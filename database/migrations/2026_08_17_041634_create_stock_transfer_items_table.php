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
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_id')
                ->constrained('stock_transfers')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

    /*
    |--------------------------------------------------------------------------
    | NULL = Shop
    |--------------------------------------------------------------------------
    */

            $table->foreignId('from_godown_id')
                ->nullable()
                ->constrained('godowns')
                ->restrictOnDelete();

    /*
    |--------------------------------------------------------------------------
    | NULL = Shop
    |--------------------------------------------------------------------------
    */

            $table->foreignId('to_godown_id')
                ->nullable()
                ->constrained('godowns')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3);

            $table->decimal('unit_cost', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
