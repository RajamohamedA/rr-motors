<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'categories',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('company_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('store_id')
                    ->constrained('stores')
                    ->cascadeOnDelete();

                $table->string(
                    'name',
                    100
                );

                $table->string(
                    'code',
                    20
                )->nullable();

                $table->text(
                    'description'
                )->nullable();

                $table->boolean(
                    'status'
                )->default(true);

                $table->timestamps();

                $table->unique(
                    [
                        'store_id',
                        'name',
                    ],
                    'categories_store_id_name_unique'
                );

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'status',
                    ],
                    'categories_company_store_status_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'categories'
        );
    }
};
