<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'godowns',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Business Context
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'company_id'
                )
                    ->constrained(
                        'companies'
                    )
                    ->cascadeOnDelete();


                $table->foreignId(
                    'store_id'
                )
                    ->constrained(
                        'stores'
                    )
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Godown Details
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'godown_name',
                    255
                );


                $table->string(
                    'godown_code',
                    255
                )->nullable();


                $table->string(
                    'address',
                    255
                )->nullable();


                $table->boolean(
                    'status'
                )->default(
                    true
                );


                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Constraints
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'godown_name',
                    ],
                    'godowns_store_id_godown_name_unique'
                );


                $table->unique(
                    [
                        'store_id',
                        'godown_code',
                    ],
                    'godowns_store_id_godown_code_unique'
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
                    'godowns_company_store_status_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'godowns'
        );
    }
};
