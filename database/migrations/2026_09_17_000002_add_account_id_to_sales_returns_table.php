<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->foreignId('account_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->index(
                ['company_id', 'store_id', 'account_id'],
                'sales_returns_context_account_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropIndex('sales_returns_context_account_index');
            $table->dropConstrainedForeignId('account_id');
        });
    }
};
