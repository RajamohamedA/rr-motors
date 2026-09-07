<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Discount Type
            |--------------------------------------------------------------------------
            |
            | percentage = e.g. 10%
            | fixed      = e.g. ₹50
            |
            */

            $table->enum(
                'discount_type',
                [
                    'percentage',
                    'fixed',
                ]
            )
                ->default('fixed')
                ->after('sale_price');


            /*
            |--------------------------------------------------------------------------
            | Discount Value
            |--------------------------------------------------------------------------
            |
            | If percentage:
            | discount_value = 10
            |
            | If fixed:
            | discount_value = 50
            |
            */

            $table->decimal(
                'discount_value',
                15,
                2
            )
                ->default(0)
                ->after('discount_type');
        });
    }


    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {

            $table->dropColumn([
                'discount_type',
                'discount_value',
            ]);
        });
    }
};
