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
        Schema::create('purchase_return_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('purchase_return_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')->constrained();

            $table->decimal('quantity',15,2);

            $table->decimal('purchase_price',15,2);

            $table->decimal('gst_percent',8,2)->default(0);

            $table->decimal('gst_amount',15,2)->default(0);

            $table->decimal('line_total',15,2);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
