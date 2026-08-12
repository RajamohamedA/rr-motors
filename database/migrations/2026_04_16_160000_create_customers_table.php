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
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('customer_code',30)->unique();

            $table->string('customer_name');

            $table->string('contact_person')->nullable();

            $table->string('mobile',20);

            $table->string('phone')->nullable();

            $table->string('email')->nullable();

            $table->text('billing_address');

            $table->text('shipping_address')->nullable();

            $table->string('city')->nullable();

            $table->string('state')->nullable();

            $table->string('country')->default('India');

            $table->string('pincode')->nullable();

            $table->string('gst_number')->nullable();

            $table->string('pan_number')->nullable();

            $table->decimal('credit_limit',15,2)->default(0);

            $table->integer('credit_days')->default(0);

            $table->decimal('opening_balance',15,2)->default(0);

            $table->enum('balance_type',['Dr','Cr'])->default('Dr');

            $table->boolean('status')->default(true);

            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
