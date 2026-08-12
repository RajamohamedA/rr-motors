<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('quotation_no')->unique();

            $table->date('quotation_date');

            $table->date('valid_until')->nullable();

            $table->decimal('sub_total', 15, 2)->default(0);

            $table->decimal('gst_total', 15, 2)->default(0);

            $table->decimal('grand_total', 15, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->enum('status', [
                'Draft',
                'Pending',
                'Approved',
                'Rejected',
                'Converted'
            ])->default('Draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
