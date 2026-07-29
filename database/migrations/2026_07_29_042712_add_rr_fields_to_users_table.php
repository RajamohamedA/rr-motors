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
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->boolean('is_super_user')
                ->default(false);

            $table->string('mobile', 20)
                ->nullable();

            $table->boolean('status')
                ->default(true);

            $table->timestamp('last_login_at')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropConstrainedForeignId('company_id');

            $table->dropColumn([
                'is_super_user',
                'mobile',
                'status',
                'last_login_at',
            ]);
        });
    }
};
