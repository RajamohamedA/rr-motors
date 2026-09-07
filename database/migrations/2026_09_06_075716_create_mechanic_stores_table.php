<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanic_stores', function (Blueprint $table) {

            $table->id();

            $table->foreignId('mechanic_id')
                ->constrained('mechanics')
                ->cascadeOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Branch Assignment
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'mechanic_id',
                    'store_id',
                ],
                'mechanic_stores_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Useful Index
            |--------------------------------------------------------------------------
            */

            $table->index(
                'store_id',
                'mechanic_stores_store_index'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'mechanic_stores'
        );
    }
};
