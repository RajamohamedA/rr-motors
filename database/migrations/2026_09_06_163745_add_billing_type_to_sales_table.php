<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->enum(
                'billing_type',
                [
                    'gst',
                    'non_gst',
                ]
            )
                ->default('gst')
                ->after('invoice_date');

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'billing_type',
                ],
                'sales_billing_type_index'
            );
        });
    }


    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropIndex(
                'sales_billing_type_index'
            );

            $table->dropColumn(
                'billing_type'
            );
        });
    }
};
