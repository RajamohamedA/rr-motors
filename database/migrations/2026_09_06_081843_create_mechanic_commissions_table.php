<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanic_commissions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Business Context
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained()
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Mechanic
            |--------------------------------------------------------------------------
            */

            $table->foreignId('mechanic_id')
                ->constrained('mechanics')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Sale
            |--------------------------------------------------------------------------
            */

            $table->foreignId('sale_id')
                ->constrained('sales')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Commission
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'commission_type',
                [
                    'percentage',
                    'fixed',
                ]
            );

            $table->decimal(
                'commission_percent',
                5,
                2
            )
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Commission Base
            |--------------------------------------------------------------------------
            |
            | Normally:
            |
            | taxable sales value after discount
            | excluding GST
            |
            */

            $table->decimal(
                'commission_base',
                15,
                2
            )
                ->default(0);


            $table->decimal(
                'commission_amount',
                15,
                2
            )
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Payment Tracking
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'paid_amount',
                15,
                2
            )
                ->default(0);

            $table->decimal(
                'balance_amount',
                15,
                2
            )
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'Pending',
                    'Partially Paid',
                    'Paid',
                    'Cancelled',
                ]
            )
                ->default('Pending');


            /*
            |--------------------------------------------------------------------------
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | One Commission Entry Per Sale
            |--------------------------------------------------------------------------
            */

            $table->unique(
                'sale_id',
                'mechanic_commissions_sale_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Reporting Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'mechanic_id',
                ],
                'mechanic_commissions_context_index'
            );


            $table->index(
                [
                    'mechanic_id',
                    'status',
                ],
                'mechanic_commissions_status_index'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'mechanic_commissions'
        );
    }
};
