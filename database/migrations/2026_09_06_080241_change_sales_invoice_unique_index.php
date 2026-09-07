<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Remove Global Invoice Unique
            |--------------------------------------------------------------------------
            */

            $table->dropUnique(
                'sales_invoice_no_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Branch-wise Invoice Unique
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'store_id',
                    'invoice_no',
                ],
                'sales_store_invoice_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Useful Sales Index
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'store_id',
                    'invoice_date',
                ],
                'sales_company_store_date_index'
            );


            $table->index(
                [
                    'company_id',
                    'store_id',
                    'customer_id',
                ],
                'sales_company_store_customer_index'
            );


            $table->index(
                [
                    'company_id',
                    'store_id',
                    'status',
                ],
                'sales_company_store_status_index'
            );
        });
    }


    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {

            $table->dropIndex(
                'sales_company_store_date_index'
            );


            $table->dropIndex(
                'sales_company_store_customer_index'
            );


            $table->dropIndex(
                'sales_company_store_status_index'
            );


            $table->dropUnique(
                'sales_store_invoice_unique'
            );


            $table->unique(
                'invoice_no',
                'sales_invoice_no_unique'
            );
        });
    }
};
