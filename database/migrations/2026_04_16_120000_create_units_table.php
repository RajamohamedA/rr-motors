<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('unit_name',100);

            $table->string('short_name',20);

            $table->text('description')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['company_id','unit_name']);
            $table->unique(['company_id','short_name']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
