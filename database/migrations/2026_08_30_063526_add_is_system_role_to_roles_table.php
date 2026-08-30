<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'roles',
            function (
                Blueprint $table
            ) {

                $table
                    ->boolean(
                        'is_system_role'
                    )
                    ->default(
                        false
                    )
                    ->after(
                        'company_id'
                    );

                $table->index(
                    'is_system_role'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'roles',
            function (
                Blueprint $table
            ) {

                $table->dropIndex([
                    'is_system_role',
                ]);

                $table->dropColumn(
                    'is_system_role'
                );
            }
        );
    }
};
