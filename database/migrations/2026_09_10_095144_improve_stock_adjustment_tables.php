<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
    |--------------------------------------------------------------------------
    | Run The Migration
    |--------------------------------------------------------------------------
    */

    public function up(): void
    {
        /*
        |----------------------------------------------------------------------
        | Stock Adjustment Header Audit Fields
        |----------------------------------------------------------------------
        */

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table
                ->enum('status', [
                    'Completed',
                    'Cancelled',
                ])
                ->default('Completed')
                ->after('remarks');

            $table
                ->timestamp('cancelled_at')
                ->nullable()
                ->after('status');

            $table
                ->foreignId('cancelled_by')
                ->nullable()
                ->after('cancelled_at')
                ->constrained('users')
                ->nullOnDelete();

            $table
                ->text('cancel_reason')
                ->nullable()
                ->after('cancelled_by');

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'adjustment_date',
                ],
                'stock_adjustment_context_date_index'
            );

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'status',
                ],
                'stock_adjustment_context_status_index'
            );
        });

        /*
        |----------------------------------------------------------------------
        | Stock Adjustment Item Location And Audit Fields
        |----------------------------------------------------------------------
        |
        | Existing rows receive a NULL godown_id and are therefore treated as
        | Shop adjustments.
        |
        */

        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table
                ->foreignId('godown_id')
                ->nullable()
                ->after('product_id')
                ->constrained('godowns')
                ->restrictOnDelete();

            $table
                ->decimal('stock_before', 15, 2)
                ->default(0)
                ->after('total_cost');

            $table
                ->decimal('stock_after', 15, 2)
                ->default(0)
                ->after('stock_before');

            $table->index(
                [
                    'stock_adjustment_id',
                    'product_id',
                    'godown_id',
                ],
                'stock_adjustment_item_location_index'
            );
        });

        /*
        |----------------------------------------------------------------------
        | Match Stock Ledger Decimal Precision
        |----------------------------------------------------------------------
        */

        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table
                ->decimal('qty', 15, 2)
                ->change();

            $table
                ->decimal('unit_cost', 15, 2)
                ->change();

            $table
                ->decimal('total_cost', 15, 2)
                ->change();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reverse The Migration
    |--------------------------------------------------------------------------
    */

    public function down(): void
    {
        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table->dropIndex(
                'stock_adjustment_item_location_index'
            );

            $table->dropForeign([
                'godown_id',
            ]);

            $table->dropColumn([
                'godown_id',
                'stock_before',
                'stock_after',
            ]);
        });

        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table
                ->decimal('qty', 10, 2)
                ->change();

            $table
                ->decimal('unit_cost', 10, 2)
                ->change();

            $table
                ->decimal('total_cost', 12, 2)
                ->change();
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropIndex(
                'stock_adjustment_context_date_index'
            );

            $table->dropIndex(
                'stock_adjustment_context_status_index'
            );

            $table->dropForeign([
                'cancelled_by',
            ]);

            $table->dropColumn([
                'status',
                'cancelled_at',
                'cancelled_by',
                'cancel_reason',
            ]);
        });
    }
};
