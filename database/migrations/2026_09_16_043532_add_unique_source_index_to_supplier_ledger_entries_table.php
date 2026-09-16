<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'supplier_ledger_entries',
            function (Blueprint $table): void {

                $table->unique(
                    [
                        'company_id',
                        'store_id',
                        'reference_type',
                        'reference_id',
                        'transaction_type',
                    ],
                    'supplier_ledger_unique_source'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'supplier_ledger_entries',
            function (Blueprint $table): void {

                $table->dropUnique(
                    'supplier_ledger_unique_source'
                );
            }
        );
    }
};
