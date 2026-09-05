<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Business Context
            |--------------------------------------------------------------------------
            */

            $table
                ->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();


            $table
                ->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();


            $table
                ->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Purchase Number
            |--------------------------------------------------------------------------
            |
            | Purchase number is unique inside each branch.
            |
            | Branch A -> PUR000001
            | Branch B -> PUR000001
            |
            */

            $table->string(
                'purchase_no',
                20
            );


            /*
            |--------------------------------------------------------------------------
            | Purchase Dates
            |--------------------------------------------------------------------------
            */

            $table->date(
                'purchase_date'
            );


            $table
                ->string(
                    'invoice_no',
                    255
                )
                ->nullable();


            $table
                ->date(
                    'invoice_date'
                )
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Tax Type
            |--------------------------------------------------------------------------
            |
            | gst
            | non_gst
            |
            */

            $table
                ->string(
                    'tax_type',
                    20
                )
                ->default('gst');


            /*
            |--------------------------------------------------------------------------
            | Totals
            |--------------------------------------------------------------------------
            */

            $table
                ->decimal(
                    'subtotal',
                    15,
                    2
                )
                ->default(0);


            $table
                ->decimal(
                    'discount',
                    15,
                    2
                )
                ->default(0);


            $table
                ->decimal(
                    'tax_amount',
                    15,
                    2
                )
                ->default(0);


            $table
                ->decimal(
                    'other_charges',
                    15,
                    2
                )
                ->default(0);


            $table
                ->decimal(
                    'grand_total',
                    15,
                    2
                )
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table
                ->decimal(
                    'paid_amount',
                    15,
                    2
                )
                ->default(0);


            $table
                ->decimal(
                    'balance_amount',
                    15,
                    2
                )
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            $table
                ->text('remarks')
                ->nullable();


            $table
                ->boolean('status')
                ->default(true);


            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'store_id',
                    'purchase_no',
                ],
                'purchases_store_id_purchase_no_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Business Query Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'purchase_date',
                ],
                'purchases_company_store_date_index'
            );


            $table->index(
                [
                    'company_id',
                    'store_id',
                    'supplier_id',
                ],
                'purchases_company_store_supplier_index'
            );


            $table->index(
                [
                    'company_id',
                    'store_id',
                    'status',
                ],
                'purchases_company_store_status_index'
            );
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'purchases'
        );
    }
};
