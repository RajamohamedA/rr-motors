<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->date('payment_date');
            $table->string('payment_mode', 30);
            $table->decimal('amount', 15, 2);
            $table->string('reference_no', 150)->nullable();
            $table->string('status', 20)->default('Completed');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(
                ['company_id', 'store_id', 'payment_date'],
                'sale_payments_company_store_date_index'
            );
            $table->index(
                ['sale_id', 'status'],
                'sale_payments_sale_status_index'
            );
            $table->index(
                ['account_id', 'payment_date'],
                'sale_payments_account_date_index'
            );
        });

        // Existing sales are intentionally not backfilled here. Their
        // paid_amount may already include customer receipts recorded later,
        // and copying it would count the same collection twice.
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
