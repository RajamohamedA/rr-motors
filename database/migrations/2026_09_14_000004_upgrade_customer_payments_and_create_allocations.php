<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->after('sale_id')
                ->constrained('accounts')
                ->restrictOnDelete();
            $table->string('status', 20)
                ->default('Completed')
                ->after('reference_no');
            $table->foreignId('created_by')
                ->nullable()
                ->after('remarks')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');

            $table->index(
                ['company_id', 'store_id', 'payment_date'],
                'customer_payments_company_store_date_index'
            );
            $table->index(
                ['customer_id', 'payment_date'],
                'customer_payments_customer_date_index'
            );
            $table->index(
                ['account_id', 'payment_date'],
                'customer_payments_account_date_index'
            );
        });

        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_payment_id')->constrained('customer_payments')->restrictOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();

            $table->unique(
                ['customer_payment_id', 'sale_id'],
                'customer_payment_sale_unique'
            );
            $table->index(
                ['company_id', 'store_id', 'sale_id'],
                'customer_alloc_company_store_sale_index'
            );
        });

        // Convert every existing single-invoice payment into an allocation.
        DB::table('customer_payments')
            ->whereNotNull('sale_id')
            ->orderBy('id')
            ->chunkById(500, function ($payments) {
                $now = now();
                $rows = [];

                foreach ($payments as $payment) {
                    $rows[] = [
                        'company_id' => $payment->company_id,
                        'store_id' => $payment->store_id,
                        'customer_payment_id' => $payment->id,
                        'sale_id' => $payment->sale_id,
                        'allocated_amount' => $payment->amount,
                        'created_at' => $payment->created_at ?? $now,
                        'updated_at' => $payment->updated_at ?? $now,
                    ];
                }

                DB::table('customer_payment_allocations')->insert($rows);
            }, 'id');

        // Future receipts may be allocated across several invoices.
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->nullable(false)->change();
        });

        Schema::dropIfExists('customer_payment_allocations');

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropIndex('customer_payments_company_store_date_index');
            $table->dropIndex('customer_payments_customer_date_index');
            $table->dropIndex('customer_payments_account_date_index');
            $table->dropConstrainedForeignId('account_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['status', 'cancelled_at']);
        });
    }
};
