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
        Schema::create('suppliers', function (Blueprint $table) {

            $table->id();
        
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->string('supplier_code',20)->unique();
        
            $table->string('supplier_name',150);
        
            $table->string('contact_person')->nullable();
        
            $table->string('phone',20)->nullable();
        
            $table->string('alternate_phone',20)->nullable();
        
            $table->string('email')->nullable();
        
            $table->string('gst_number',20)->nullable();
        
            $table->string('pan_number',20)->nullable();
        
            $table->text('address')->nullable();
        
            $table->string('city')->nullable();
        
            $table->string('state')->nullable();
        
            $table->string('country')->default('India');
        
            $table->string('pincode',10)->nullable();
        
            $table->decimal('opening_balance',15,2)->default(0);
        
            $table->enum('balance_type',['Dr','Cr'])->default('Cr');
        
            $table->boolean('status')->default(true);
        
            $table->timestamps();
        
            $table->unique(['company_id','supplier_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
