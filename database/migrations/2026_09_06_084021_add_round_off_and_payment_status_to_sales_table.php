<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Final Discount
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {

            if (!Schema::hasColumn('sales', 'final_discount')) {

                $table->decimal(
                    'final_discount',
                    15,
                    2
                )
                    ->default(0)
                    ->after('gst_total');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Step 2: Round Off
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {

            if (!Schema::hasColumn('sales', 'round_off')) {

                $table->decimal(
                    'round_off',
                    15,
                    2
                )
                    ->default(0)
                    ->after('final_discount');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Step 3: Payment Status
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {

            if (!Schema::hasColumn('sales', 'payment_status')) {

                $table->enum(
                    'payment_status',
                    [
                        'Unpaid',
                        'Partially Paid',
                        'Paid',
                    ]
                )
                    ->default('Unpaid')
                    ->after('balance_amount');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Step 4: Payment Status Index
        |--------------------------------------------------------------------------
        */

        Schema::table('sales', function (Blueprint $table) {

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'payment_status',
                ],
                'sales_payment_status_index'
            );
        });
    }


    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropIndex(
                'sales_payment_status_index'
            );
        });


        Schema::table('sales', function (Blueprint $table) {

            if (Schema::hasColumn('sales', 'payment_status')) {

                $table->dropColumn(
                    'payment_status'
                );
            }


            if (Schema::hasColumn('sales', 'round_off')) {

                $table->dropColumn(
                    'round_off'
                );
            }


            if (Schema::hasColumn('sales', 'final_discount')) {

                $table->dropColumn(
                    'final_discount'
                );
            }
        });
    }
};
