<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('invoice_no',30)->unique();

            $table->date('invoice_date');

            $table->decimal('sub_total',15,2)->default(0);

            $table->decimal('discount',15,2)->default(0);

            $table->decimal('gst_total',15,2)->default(0);

            $table->decimal('grand_total',15,2)->default(0);

            $table->decimal('paid_amount',15,2)->default(0);

            $table->decimal('balance_amount',15,2)->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};