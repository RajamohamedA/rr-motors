<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ledgers', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Latest Stock by Location
            |--------------------------------------------------------------------------
            |
            | godown_id = NULL : Shop
            | godown_id has ID : Godown
            |
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'product_id',
                    'godown_id',
                    'id',
                ],
                'stock_current_location_index'
            );

            /*
            |--------------------------------------------------------------------------
            | Stock Ledger Date Filter
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'transaction_date',
                ],
                'stock_context_date_index'
            );

            /*
            |--------------------------------------------------------------------------
            | Transaction Type Filter
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'transaction_type',
                ],
                'stock_context_type_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('stock_ledgers', function (Blueprint $table) {
            $table->dropIndex('stock_current_location_index');
            $table->dropIndex('stock_context_date_index');
            $table->dropIndex('stock_context_type_index');
        });
    }
};
