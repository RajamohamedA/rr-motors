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
        Schema::create(
            'brands',
            function (
                Blueprint $table
            ) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'company_id'
                )
                    ->constrained(
                        'companies'
                    )
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Store / Branch
                |--------------------------------------------------------------------------
                */

                $storeColumn = $table->foreignId('store_id');
                if (Schema::hasTable('stores')) {
                    $storeColumn->constrained('stores')->cascadeOnDelete();
                }


                /*
                |--------------------------------------------------------------------------
                | Brand Details
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'brand_code',
                    20
                );


                $table->string(
                    'brand_name',
                    100
                );


                $table->text(
                    'description'
                )
                    ->nullable();


                $table->boolean(
                    'status'
                )
                    ->default(
                        true
                    );


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Unique Brand Name
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'brand_name',
                    ],
                    'brands_store_id_brand_name_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Unique Brand Code
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'brand_code',
                    ],
                    'brands_store_id_brand_code_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Context Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'status',
                    ],
                    'brands_company_store_status_index'
                );
            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'brands'
        );
    }
};
