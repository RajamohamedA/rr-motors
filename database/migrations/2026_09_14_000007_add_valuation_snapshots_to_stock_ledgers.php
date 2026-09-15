<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ledgers', function (Blueprint $table) {
            $table->decimal('average_cost', 15, 4)
                ->nullable()
                ->after('total_cost');

            $table->decimal('balance_value', 18, 2)
                ->nullable()
                ->after('average_cost');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'average_cost',
                'balance_value',
            ]);
        });
    }
};
