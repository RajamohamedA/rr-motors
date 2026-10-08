<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
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

            $table->string('expense_no', 30);
            $table->date('expense_date');

            $table->string('expense_category', 100);
            $table->string('payee_name', 150)->nullable();

            $table->decimal('amount', 15, 2);
            $table->string('payment_mode', 30);
            $table->string('reference_no', 150)->nullable();

            $table->text('remarks')->nullable();

            $table->string('status', 20)
                ->default('Completed');

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

            $table->unique(
                [
                    'company_id',
                    'store_id',
                    'expense_no',
                ],
                'expenses_context_number_unique'
            );

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'expense_date',
                ],
                'expenses_context_date_index'
            );

            $table->index(
                [
                    'account_id',
                    'expense_date',
                ],
                'expenses_account_date_index'
            );

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'status',
                ],
                'expenses_context_status_index'
            );

            $table->index(
                [
                    'expense_category',
                    'expense_date',
                ],
                'expenses_category_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
