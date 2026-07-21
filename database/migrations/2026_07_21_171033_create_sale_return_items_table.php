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
        Schema::create('sale_return_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('sale_return_id')
                ->constrained('sales_returns')
                ->cascadeOnDelete();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->decimal('quantity',15,2);

            $table->decimal('sale_price',15,2);

            $table->decimal('gst_percent',5,2)->default(0);

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
        Schema::dropIfExists('sale_return_items');
    }
};
