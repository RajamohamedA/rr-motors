<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            /*
            |------------------------------------------------------------------
            | Voucher Identity
            |------------------------------------------------------------------
            |
            | receipt: money enters the selected Cash/Bank account.
            | payment: money leaves the selected Cash/Bank account.
            |
            */

            $table->string('voucher_type', 20);
            $table->string('voucher_no', 30);
            $table->date('voucher_date');

            /*
            |------------------------------------------------------------------
            | Amount and Payment Information
            |------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);
            $table->string('payment_mode', 30)->nullable();
            $table->string('reference_no', 150)->nullable();

            /*
            |------------------------------------------------------------------
            | Optional Party Information
            |------------------------------------------------------------------
            |
            | party_type may contain customer, supplier, mechanic or other.
            | party_id is intentionally not a foreign key because the record
            | may point to different master tables.
            |
            */

            $table->string('party_type', 30)->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('party_name', 150)->nullable();

            $table->text('remarks')->nullable();

            /*
            |------------------------------------------------------------------
            | Audit and Cancellation
            |------------------------------------------------------------------
            |
            | Completed vouchers are never deleted. Cancellation creates a
            | reversal entry in account_transactions.
            |
            */

            $table->string('status', 20)->default('Completed');

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
            | Constraints and Indexes
            |------------------------------------------------------------------
            */

            $table->unique(
                ['company_id', 'store_id', 'voucher_no'],
                'vouchers_company_store_no_unique'
            );

            $table->index(
                ['company_id', 'store_id', 'voucher_date'],
                'vouchers_context_date_index'
            );

            $table->index(
                ['company_id', 'store_id', 'voucher_type', 'status'],
                'vouchers_context_type_status_index'
            );

            $table->index(
                ['account_id', 'voucher_date'],
                'vouchers_account_date_index'
            );

            $table->index(
                ['party_type', 'party_id'],
                'vouchers_party_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
