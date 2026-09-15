<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)
                ->nullable()
                ->after('sale_price');
            $table->decimal('cost_total', 15, 2)
                ->nullable()
                ->after('line_total');
            $table->decimal('net_sale_amount', 15, 2)
                ->nullable()
                ->after('cost_total');
            $table->decimal('gross_profit', 15, 2)
                ->nullable()
                ->after('net_sale_amount');
            $table->string('cost_source', 30)
                ->nullable()
                ->after('gross_profit');

            $table->index(
                ['sale_id', 'product_id'],
                'sale_items_sale_product_profit_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex('sale_items_sale_product_profit_index');
            $table->dropColumn([
                'unit_cost',
                'cost_total',
                'net_sale_amount',
                'gross_profit',
                'cost_source',
            ]);
        });
    }
};
