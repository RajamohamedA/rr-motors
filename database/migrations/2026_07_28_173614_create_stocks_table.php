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
        Schema::create('stocks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained();

            $table->foreignId('store_id')
                ->constrained();

            $table->foreignId('product_id')
                ->constrained();

            $table->date('transaction_date');

            $table->string('transaction_type');

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->string('reference_no')
                ->nullable();


            $table->decimal('qty_in', 10, 2)
                ->default(0);

            $table->decimal('qty_out', 10, 2)
                ->default(0);

            $table->decimal('balance_qty', 10, 2)
                ->default(0);


            $table->decimal('unit_cost', 10, 2)
                ->default(0);

            $table->decimal('total_cost', 12, 2)
                ->default(0);


            $table->text('remarks')
                ->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
