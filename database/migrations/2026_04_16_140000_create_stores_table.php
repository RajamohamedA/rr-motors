<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('store_code',20)->unique();

            $table->string('store_name');

            $table->string('contact_person')->nullable();

            $table->string('phone',20)->nullable();

            $table->string('email')->nullable();

            $table->text('address')->nullable();

            $table->string('city')->nullable();

            $table->string('state')->nullable();

            $table->string('country')->default('India');

            $table->string('pincode',10)->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};