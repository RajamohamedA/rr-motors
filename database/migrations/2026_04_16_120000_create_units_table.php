<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'units',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Company
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'company_id'
                )
                    ->constrained('companies')
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
                | Unit Details
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'unit_name',
                    100
                );


                $table->string(
                    'short_name',
                    20
                );


                $table->text(
                    'description'
                )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | System Unit
                |--------------------------------------------------------------------------
                |
                | Default units such as Nos, Pcs, Kg, Ltr, Mtr etc.
                | will have this flag enabled.
                |
                */

                $table->boolean(
                    'is_system_unit'
                )
                    ->default(false);


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $table->boolean(
                    'status'
                )
                    ->default(true);


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Unit Name
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'unit_name',
                    ],
                    'units_store_id_unit_name_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Short Name
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'short_name',
                    ],
                    'units_store_id_short_name_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Business Context Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'status',
                    ],
                    'units_company_store_status_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'units'
        );
    }
};
