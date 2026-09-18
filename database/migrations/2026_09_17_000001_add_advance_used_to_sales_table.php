<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('advance_used', 15, 2)
                ->default(0)
                ->after('grand_total');

            $table->index(
                ['company_id', 'store_id', 'advance_used'],
                'sales_context_advance_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_context_advance_index');
            $table->dropColumn('advance_used');
        });
    }
};
