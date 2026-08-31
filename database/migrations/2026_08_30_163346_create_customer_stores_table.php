<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'customer_stores',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->foreignId(
                        'customer_id'
                    )
                    ->constrained(
                        'customers'
                    )
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table
                    ->foreignId(
                        'store_id'
                    )
                    ->constrained(
                        'stores'
                    )
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique([
                    'customer_id',
                    'store_id',
                ]);

                $table->index([
                    'store_id',
                    'customer_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'customer_stores'
        );
    }
};
