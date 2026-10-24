<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The current base products migration already creates this column.
        if (!Schema::hasColumn('products', 'current_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('current_stock', 15, 3)
                    ->default(0)->after('opening_stock');
            });
        }
    }

    public function down(): void
    {
        // Preserve a compatibility column that may belong to the base schema.
        // Rolling back the base products migration drops the table itself.
    }
};
