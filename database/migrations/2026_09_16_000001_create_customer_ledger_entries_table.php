<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'customer_ledger_entries',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->restrictOnDelete();

                $table->foreignId('store_id')
                    ->constrained('stores')
                    ->restrictOnDelete();

                $table->foreignId('customer_id')
                    ->constrained('customers')
                    ->restrictOnDelete();

                $table->date('transaction_date');

                $table->string(
                    'transaction_type',
                    40
                );

                $table->string(
                    'reference_type',
                    80
                )->nullable();

                $table->unsignedBigInteger(
                    'reference_id'
                )->nullable();

                $table->string(
                    'reference_no',
                    100
                )->nullable();

                /*
                |--------------------------------------------------------------
                | Customer Accounting Direction
                |--------------------------------------------------------------
                |
                | Sale / opening Dr balance       => Debit
                | Customer payment / sales return => Credit
                |
                */

                $table->decimal(
                    'debit',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'credit',
                    15,
                    2
                )->default(0);

                $table->boolean(
                    'is_reversal'
                )->default(false);

                $table->foreignId(
                    'reversal_of_id'
                )
                    ->nullable()
                    ->constrained(
                        'customer_ledger_entries'
                    )
                    ->restrictOnDelete();

                $table->text(
                    'remarks'
                )->nullable();

                $table->foreignId(
                    'created_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'transaction_date',
                    ],
                    'customer_ledger_context_date_index'
                );

                $table->index(
                    [
                        'customer_id',
                        'transaction_date',
                        'id',
                    ],
                    'customer_ledger_customer_date_index'
                );

                $table->index(
                    [
                        'reference_type',
                        'reference_id',
                    ],
                    'customer_ledger_reference_index'
                );

                $table->index(
                    [
                        'transaction_type',
                        'transaction_date',
                    ],
                    'customer_ledger_type_date_index'
                );

                /*
                |--------------------------------------------------------------
                | Prevent Duplicate Source Posting
                |--------------------------------------------------------------
                */

                $table->unique(
                    [
                        'company_id',
                        'store_id',
                        'reference_type',
                        'reference_id',
                        'transaction_type',
                    ],
                    'customer_ledger_source_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'customer_ledger_entries'
        );
    }
};
