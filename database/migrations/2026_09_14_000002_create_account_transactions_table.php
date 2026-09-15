<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->date('transaction_date');
            $table->string('transaction_type', 40);
            $table->string('reference_type', 80);
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_no', 100)->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('payment_mode', 30)->nullable();
            $table->string('external_reference', 150)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversal_of_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['company_id', 'store_id', 'transaction_date'],
                'account_tx_company_store_date_index'
            );
            $table->index(
                ['account_id', 'transaction_date', 'id'],
                'account_tx_account_date_index'
            );
            $table->index(
                ['reference_type', 'reference_id'],
                'account_tx_reference_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
