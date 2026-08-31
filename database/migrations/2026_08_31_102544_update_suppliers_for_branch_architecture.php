<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'suppliers',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Supplier Code - Unique Per Company
                |--------------------------------------------------------------------------
                */

                $table->dropUnique([
                    'supplier_code',
                ]);


                $table->unique(
                    [
                        'company_id',
                        'supplier_code',
                    ],
                    'suppliers_company_code_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Credit Settings
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal(
                        'credit_limit',
                        15,
                        2
                    )
                    ->default(
                        0
                    )
                    ->after(
                        'opening_balance'
                    );


                $table
                    ->integer(
                        'credit_days'
                    )
                    ->default(
                        0
                    )
                    ->after(
                        'credit_limit'
                    );


                /*
                |--------------------------------------------------------------------------
                | Remarks
                |--------------------------------------------------------------------------
                */

                $table
                    ->text(
                        'remarks'
                    )
                    ->nullable()
                    ->after(
                        'status'
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Supplier Branch Assignment
        |--------------------------------------------------------------------------
        */

        Schema::create(
            'supplier_stores',
            function (Blueprint $table) {

                $table->id();


                $table
                    ->foreignId(
                        'supplier_id'
                    )
                    ->constrained(
                        'suppliers'
                    )
                    ->cascadeOnDelete();


                $table
                    ->foreignId(
                        'store_id'
                    )
                    ->constrained(
                        'stores'
                    )
                    ->cascadeOnDelete();


                $table->timestamps();


                $table->unique(
                    [
                        'supplier_id',
                        'store_id',
                    ],
                    'supplier_store_unique'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'supplier_stores'
        );


        Schema::table(
            'suppliers',
            function (Blueprint $table) {

                $table->dropUnique(
                    'suppliers_company_code_unique'
                );


                $table->unique(
                    'supplier_code'
                );


                $table->dropColumn([
                    'credit_limit',
                    'credit_days',
                    'remarks',
                ]);
            }
        );
    }
};
