<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'supplier_ledger_entries',
            function (Blueprint $table): void {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Company and Branch Isolation
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('company_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table
                    ->foreignId('store_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table
                    ->foreignId('supplier_id')
                    ->constrained()
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Transaction Details
                |--------------------------------------------------------------------------
                */

                $table->date('transaction_date');

                $table->string(
                    'transaction_type',
                    40
                );

                /*
                | Examples:
                |
                | opening_balance
                | purchase
                | purchase_return
                | supplier_payment
                | payment_reversal
                | adjustment
                */


                /*
                |--------------------------------------------------------------------------
                | Source Reference
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'reference_type',
                        80
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'reference_id'
                    )
                    ->nullable();

                $table
                    ->string(
                        'reference_no',
                        100
                    )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Ledger Amounts
                |--------------------------------------------------------------------------
                |
                | Credit increases the supplier payable.
                | Debit reduces the supplier payable.
                |
                */

                $table
                    ->decimal(
                        'debit',
                        15,
                        2
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'credit',
                        15,
                        2
                    )
                    ->default(0);


                /*
                |--------------------------------------------------------------------------
                | Reversal Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->boolean('is_reversal')
                    ->default(false);

                $table
                    ->foreignId('reversal_of_id')
                    ->nullable()
                    ->constrained(
                        'supplier_ledger_entries'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Additional Information
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('remarks')
                    ->nullable();

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'transaction_date',
                    ],
                    'supplier_ledger_context_date_index'
                );

                $table->index(
                    [
                        'supplier_id',
                        'transaction_date',
                        'id',
                    ],
                    'supplier_ledger_supplier_date_index'
                );

                $table->index(
                    [
                        'reference_type',
                        'reference_id',
                    ],
                    'supplier_ledger_reference_index'
                );

                $table->index(
                    [
                        'transaction_type',
                        'transaction_date',
                    ],
                    'supplier_ledger_type_date_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'supplier_ledger_entries'
        );
    }
};
