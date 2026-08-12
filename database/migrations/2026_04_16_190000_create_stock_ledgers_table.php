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
        Schema::create('stock_ledgers', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
        
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        
            $table->date('transaction_date');
        
            $table->string('transaction_type',30);
            // Opening
            // Purchase
            // Sale
            // Purchase Return
            // Sales Return
            // Adjustment
            // Transfer In
            // Transfer Out
        
            $table->unsignedBigInteger('reference_id')->nullable();
        
            $table->string('reference_no')->nullable();
        
            $table->decimal('qty_in',15,2)->default(0);
        
            $table->decimal('qty_out',15,2)->default(0);
        
            $table->decimal('balance_qty',15,2)->default(0);
        
            $table->decimal('unit_cost',15,2)->default(0);
        
            $table->decimal('total_cost',15,2)->default(0);
        
            $table->text('remarks')->nullable();
        
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
