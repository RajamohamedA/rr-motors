<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'units', 'brands'] as $name) {
            $exists = collect(Schema::getForeignKeys($name))->contains(
                fn (array $key) => $key['columns'] === ['store_id']
            );
            if (!$exists) {
                Schema::table($name, function (Blueprint $table) use ($name) {
                    $table->foreign('store_id', "{$name}_store_context_fk")
                        ->references('id')->on('stores')->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['categories', 'units', 'brands'] as $name) {
            $keyName = "{$name}_store_context_fk";
            if (collect(Schema::getForeignKeys($name))->contains(fn (array $key) => $key['name'] === $keyName)) {
                Schema::table($name, fn (Blueprint $table) => $table->dropForeign($keyName));
            }
        }
    }
};
