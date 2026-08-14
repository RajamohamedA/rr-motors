<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {

            $table->foreignId('godown_id')
                ->nullable()
                ->after('product_id')
                ->constrained('godowns')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_return_items', function (Blueprint $table) {

            $table->dropForeign([
                'godown_id'
            ]);

            $table->dropColumn('godown_id');
        });
    }
};
