<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {

            $table->string('company_code', 20)
                ->nullable()
                ->unique()
                ->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['company_code']);
            $table->dropColumn('company_code');
        });
    }
};
