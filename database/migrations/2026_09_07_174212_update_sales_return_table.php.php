<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table
                ->foreignId('store_id')
                ->nullable()
                ->after('company_id')
                ->constrained('stores')
                ->restrictOnDelete();

            $table
                ->string('billing_type', 20)
                ->default('gst')
                ->after('customer_id');

            $table
                ->decimal('sub_total', 15, 2)
                ->default(0)
                ->after('billing_type');

            $table
                ->decimal('discount_total', 15, 2)
                ->default(0)
                ->after('sub_total');

            $table
                ->decimal('gst_total', 15, 2)
                ->default(0)
                ->after('discount_total');

            $table
                ->decimal('round_off', 15, 2)
                ->default(0)
                ->after('gst_total');

            $table
                ->decimal('refund_amount', 15, 2)
                ->default(0)
                ->after('total_amount');

            $table
                ->string('refund_method', 30)
                ->default('none')
                ->after('refund_amount');

            $table
                ->string('refund_status', 30)
                ->default('pending')
                ->after('refund_method');

            $table
                ->string('status', 20)
                ->default('completed')
                ->after('refund_status');

            $table
                ->index(
                    ['company_id', 'store_id', 'return_date'],
                    'sales_returns_context_date_index'
                );
        });

        /*
        |------------------------------------------------------------------
        | Attach existing returns to the branch of their original sale.
        |------------------------------------------------------------------
        */

        DB::table('sales_returns')->whereNull('store_id')->update([
            'store_id' => DB::raw('(SELECT sales.store_id FROM sales WHERE sales.id = sales_returns.sale_id)'),
        ]);

        /*
        |------------------------------------------------------------------
        | Preserve existing totals in the new amount columns.
        |------------------------------------------------------------------
        */

        DB::table('sales_returns')->update([
            'sub_total' => DB::raw('total_amount - COALESCE(gst_total, 0)'),
            'refund_amount' => DB::raw('total_amount'),
        ]);

        Schema::table('sale_return_items', function (Blueprint $table) {
            $table
                ->foreignId('sale_item_id')
                ->nullable()
                ->after('sale_return_id')
                ->constrained('sale_items')
                ->restrictOnDelete();

            $table
                ->decimal('free_quantity', 15, 2)
                ->default(0)
                ->after('quantity');

            $table
                ->string('discount_type', 20)
                ->default('fixed')
                ->after('sale_price');

            $table
                ->decimal('discount_value', 15, 2)
                ->default(0)
                ->after('discount_type');

            $table
                ->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('discount_value');

            $table
                ->decimal('taxable_amount', 15, 2)
                ->default(0)
                ->after('discount_amount');

            $table
                ->index(
                    ['sale_item_id', 'product_id'],
                    'sale_return_items_source_index'
                );
        });

        /*
        |------------------------------------------------------------------
        | Link legacy return rows to a matching item from the original sale.
        | This is safe when an invoice contains a product only once.
        |------------------------------------------------------------------
        */

        DB::table('sale_return_items')->whereNull('sale_item_id')->update([
            // Repeated products on an old invoice cannot be linked unambiguously.
            'sale_item_id' => DB::raw('(SELECT CASE WHEN COUNT(*) = 1 THEN MIN(si.id) ELSE NULL END
                FROM sale_items AS si
                INNER JOIN sales_returns AS sr ON sr.sale_id = si.sale_id
                WHERE sr.id = sale_return_items.sale_return_id
                  AND si.product_id = sale_return_items.product_id)'),
        ]);

        DB::table('sale_return_items')->update([
            'taxable_amount' => DB::raw(
                'CASE WHEN (quantity * sale_price) - discount_amount > 0
                    THEN (quantity * sale_price) - discount_amount ELSE 0 END'
            ),
        ]);
    }

    public function down(): void
    {
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->dropIndex('sale_return_items_source_index');
            $table->dropConstrainedForeignId('sale_item_id');
            $table->dropColumn([
                'free_quantity',
                'discount_type',
                'discount_value',
                'discount_amount',
                'taxable_amount',
            ]);
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropIndex('sales_returns_context_date_index');
            $table->dropConstrainedForeignId('store_id');
            $table->dropColumn([
                'billing_type',
                'sub_total',
                'discount_total',
                'gst_total',
                'round_off',
                'refund_amount',
                'refund_method',
                'refund_status',
                'status',
            ]);
        });
    }
};
