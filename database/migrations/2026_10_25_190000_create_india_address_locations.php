<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_states', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('name', 100)->unique();
        });
        Schema::create('geo_districts', function (Blueprint $table) {
            $table->string('code', 10)->primary();
            $table->string('state_code', 10);
            $table->string('name', 100);
            $table->foreign('state_code')->references('code')->on('geo_states');
            $table->unique(['state_code', 'name']);
        });
        Schema::create('geo_cities', function (Blueprint $table) {
            $table->id();
            $table->string('district_code', 10);
            $table->string('name', 100);
            $table->foreign('district_code')->references('code')->on('geo_districts');
            $table->unique(['district_code', 'name']);
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->string('district', 100)->nullable();
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->string('district', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('stores', fn(Blueprint $table) => $table->dropColumn('district'));
        Schema::table('companies', fn(Blueprint $table) => $table->dropColumn('district'));
        Schema::dropIfExists('geo_cities');
        Schema::dropIfExists('geo_districts');
        Schema::dropIfExists('geo_states');
    }
};
