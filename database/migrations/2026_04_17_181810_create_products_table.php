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
        Schema::create('products', function (Blueprint $table) {

            $table->id();
        
            // Company & Store
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        
            // Master Tables
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
        
            // Product Details
            $table->string('product_code',50);
            $table->string('barcode',100)->nullable()->unique();
        
            $table->string('product_name');
            $table->string('part_number')->nullable();
        
            $table->string('manufacturer')->nullable();
            $table->string('rack_location')->nullable();
        
            // Tax
            $table->string('hsn_code',20)->nullable();
            $table->decimal('gst_percent',5,2)->default(0);
        
            // Pricing
            $table->decimal('purchase_price',15,2)->default(0);
            $table->decimal('selling_price',15,2)->default(0);
            $table->decimal('mrp',15,2)->default(0);
        
            // Stock
            $table->decimal('opening_stock',15,2)->default(0);
            $table->decimal('minimum_stock',15,2)->default(0);
            $table->decimal('maximum_stock',15,2)->nullable();
        
            // Other
            $table->string('image')->nullable();
            $table->text('description')->nullable();
        
            $table->boolean('status')->default(true);
        
            $table->timestamps();
        
            $table->unique(['company_id','product_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
