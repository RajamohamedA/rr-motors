<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'customers',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Optional Customer Contact
                |--------------------------------------------------------------------------
                */

                $table
                    ->string(
                        'mobile',
                        20
                    )
                    ->nullable()
                    ->change();


                /*
                |--------------------------------------------------------------------------
                | Optional Billing Address
                |--------------------------------------------------------------------------
                */

                $table
                    ->text(
                        'billing_address'
                    )
                    ->nullable()
                    ->change();


                /*
                |--------------------------------------------------------------------------
                | Customer Code Must Be Unique Per Company
                |--------------------------------------------------------------------------
                */

                $table->dropUnique([
                    'customer_code',
                ]);


                $table->unique(
                    [
                        'company_id',
                        'customer_code',
                    ],
                    'customers_company_code_unique'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'customers',
            function (Blueprint $table) {

                $table->dropUnique(
                    'customers_company_code_unique'
                );


                $table->unique(
                    'customer_code'
                );


                $table
                    ->string(
                        'mobile',
                        20
                    )
                    ->nullable(false)
                    ->change();


                $table
                    ->text(
                        'billing_address'
                    )
                    ->nullable(false)
                    ->change();
            }
        );
    }
};
