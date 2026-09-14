<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->decimal('from_stock_before', 15, 3)
                ->default(0)
                ->after('quantity');

            $table->decimal('from_stock_after', 15, 3)
                ->default(0)
                ->after('from_stock_before');

            $table->decimal('to_stock_before', 15, 3)
                ->default(0)
                ->after('from_stock_after');

            $table->decimal('to_stock_after', 15, 3)
                ->default(0)
                ->after('to_stock_before');

            $table->decimal('total_cost', 15, 2)
                ->default(0)
                ->after('unit_cost');

            $table->index(
                ['stock_transfer_id', 'product_id'],
                'stock_transfer_product_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropIndex('stock_transfer_product_index');

            $table->dropColumn([
                'from_stock_before',
                'from_stock_after',
                'to_stock_before',
                'to_stock_after',
                'total_cost',
            ]);
        });
    }
};
