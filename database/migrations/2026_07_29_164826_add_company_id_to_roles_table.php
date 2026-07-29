<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->boolean('status')
                ->default(true)
                ->after('guard_name');

            $table->text('description')
                ->nullable()
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {

            $table->dropConstrainedForeignId('company_id');

            $table->dropColumn([
                'status',
                'description'
            ]);
        });
    }
};