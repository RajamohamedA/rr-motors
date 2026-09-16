<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();

            /*
            |------------------------------------------------------------------
            | Company and Branch Isolation
            |------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            /*
            | A payment can be an advance and therefore need not be attached
            | to a purchase immediately.
            */
            $table->foreignId('purchase_id')
                ->nullable()
                ->constrained('purchases')
                ->restrictOnDelete();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            /*
            |------------------------------------------------------------------
            | Payment Details
            |------------------------------------------------------------------
            */

            $table->string('payment_no', 30);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_mode', 30)->default('Cash');
            $table->string('reference_no', 150)->nullable();
            $table->string('status', 20)->default('Completed');
            $table->text('remarks')->nullable();

            /*
            |------------------------------------------------------------------
            | Audit and Cancellation
            |------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            /*
            |------------------------------------------------------------------
            | Constraints and Reporting Indexes
            |------------------------------------------------------------------
            */

            $table->unique(
                [
                    'company_id',
                    'store_id',
                    'payment_no',
                ],
                'supplier_payments_context_no_unique'
            );

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'payment_date',
                ],
                'supplier_payments_context_date_index'
            );

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'status',
                    'payment_mode',
                    'payment_date',
                ],
                'supplier_payments_dashboard_index'
            );

            $table->index(
                [
                    'supplier_id',
                    'payment_date',
                    'id',
                ],
                'supplier_payments_supplier_date_index'
            );

            $table->index(
                [
                    'purchase_id',
                    'status',
                ],
                'supplier_payments_purchase_status_index'
            );

            $table->index(
                [
                    'account_id',
                    'payment_date',
                ],
                'supplier_payments_account_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
