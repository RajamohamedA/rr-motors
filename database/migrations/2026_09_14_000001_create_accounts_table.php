<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->string('account_code', 30);
            $table->string('account_name', 150);
            $table->string('account_type', 30);
            $table->string('payment_mode', 30)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('upi_id', 100)->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['company_id', 'store_id', 'account_code'],
                'accounts_company_store_code_unique'
            );
            $table->index(
                ['company_id', 'store_id', 'status'],
                'accounts_company_store_status_index'
            );
            $table->index(
                ['company_id', 'store_id', 'account_type'],
                'accounts_company_store_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
