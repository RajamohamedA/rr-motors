<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'mechanic_payments',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('store_id')
                    ->constrained('stores')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('mechanic_id')
                    ->constrained('mechanics')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('account_id')
                    ->constrained('accounts')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->string('payment_no', 50);

                $table->date('payment_date');

                $table->decimal(
                    'amount',
                    15,
                    2
                );

                $table->string(
                    'payment_mode',
                    30
                );

                $table->string(
                    'reference_no',
                    150
                )->nullable();

                $table->string(
                    'status',
                    20
                )->default('Completed');

                $table->text('remarks')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('cancelled_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp(
                    'cancelled_at'
                )->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'company_id',
                        'store_id',
                        'payment_no',
                    ],
                    'mechanic_payments_context_number_unique'
                );

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'payment_date',
                    ],
                    'mechanic_payments_context_date_index'
                );

                $table->index(
                    [
                        'mechanic_id',
                        'payment_date',
                    ],
                    'mechanic_payments_mechanic_date_index'
                );

                $table->index(
                    [
                        'account_id',
                        'payment_date',
                    ],
                    'mechanic_payments_account_date_index'
                );

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'status',
                    ],
                    'mechanic_payments_context_status_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('mechanic_payments');
    }
};
