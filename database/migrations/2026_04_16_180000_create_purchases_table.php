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
        Schema::create('purchases', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        
            $table->string('purchase_no',20)->unique();
        
            $table->date('purchase_date');
        
            $table->string('invoice_no')->nullable();
        
            $table->date('invoice_date')->nullable();
        
            $table->decimal('subtotal',15,2)->default(0);
        
            $table->decimal('discount',15,2)->default(0);
        
            $table->decimal('tax_amount',15,2)->default(0);
        
            $table->decimal('other_charges',15,2)->default(0);
        
            $table->decimal('grand_total',15,2)->default(0);
        
            $table->decimal('paid_amount',15,2)->default(0);
        
            $table->decimal('balance_amount',15,2)->default(0);
        
            $table->text('remarks')->nullable();
        
            $table->boolean('status')->default(true);
        
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
