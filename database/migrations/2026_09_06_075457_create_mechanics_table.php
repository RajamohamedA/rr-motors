<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanics', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('mechanic_code', 30);

            $table->string('mechanic_name', 150);

            $table->string('mobile', 20)
                ->nullable();

            $table->string('email')
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('state', 100)
                ->nullable();

            $table->string('pincode', 20)
                ->nullable();

            $table->boolean('status')
                ->default(true);

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Unique Mechanic Code Per Company
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'company_id',
                    'mechanic_code',
                ],
                'mechanics_company_code_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Useful Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'status',
                ],
                'mechanics_company_status_index'
            );

            $table->index(
                [
                    'company_id',
                    'mechanic_name',
                ],
                'mechanics_company_name_index'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'mechanics'
        );
    }
};