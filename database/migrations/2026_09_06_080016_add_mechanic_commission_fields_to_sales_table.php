<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Sale Source
            |--------------------------------------------------------------------------
            |
            | customer = normal customer sale
            | mechanic = mechanic referred sale
            |
            */

            $table->enum(
                'sale_source',
                [
                    'customer',
                    'mechanic',
                ]
            )
                ->default('customer')
                ->after('customer_id');


            /*
            |--------------------------------------------------------------------------
            | Mechanic
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'mechanic_id'
            )
                ->nullable()
                ->after('sale_source')
                ->constrained('mechanics')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Commission Type
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'commission_type',
                [
                    'percentage',
                    'fixed',
                ]
            )
                ->nullable()
                ->after('mechanic_id');


            /*
            |--------------------------------------------------------------------------
            | Commission Percentage
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'commission_percent',
                5,
                2
            )
                ->nullable()
                ->after('commission_type');


            /*
            |--------------------------------------------------------------------------
            | Commission Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'commission_amount',
                15,
                2
            )
                ->default(0)
                ->after('commission_percent');


            /*
            |--------------------------------------------------------------------------
            | Commission Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'commission_status',
                [
                    'Pending',
                    'Partially Paid',
                    'Paid',
                ]
            )
                ->default('Pending')
                ->after('commission_amount');


            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'mechanic_id',
                ],
                'sales_company_store_mechanic_index'
            );
        });
    }


    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropIndex(
                'sales_company_store_mechanic_index'
            );


            $table->dropForeign([
                'mechanic_id',
            ]);


            $table->dropColumn([
                'sale_source',
                'mechanic_id',
                'commission_type',
                'commission_percent',
                'commission_amount',
                'commission_status',
            ]);
        });
    }
};
