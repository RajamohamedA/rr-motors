<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'customers',
            function (Blueprint $table) {

                $table
                    ->string(
                        'customer_type',
                        30
                    )
                    ->default(
                        'regular'
                    )
                    ->after(
                        'customer_name'
                    );

                $table
                    ->boolean(
                        'is_system_customer'
                    )
                    ->default(
                        false
                    )
                    ->after(
                        'customer_type'
                    );

                $table->index([
                    'company_id',
                    'customer_type',
                ]);

                $table->index([
                    'company_id',
                    'is_system_customer',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'customers',
            function (Blueprint $table) {

                $table->dropIndex([
                    'company_id',
                    'customer_type',
                ]);

                $table->dropIndex([
                    'company_id',
                    'is_system_customer',
                ]);

                $table->dropColumn([
                    'customer_type',
                    'is_system_customer',
                ]);
            }
        );
    }
};
