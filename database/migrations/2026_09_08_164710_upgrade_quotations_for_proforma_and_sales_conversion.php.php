<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |------------------------------------------------------------------
        | Upgrade Quotations
        |------------------------------------------------------------------
        */

        Schema::table('quotations', function (Blueprint $table) {
            $table
                ->enum('document_type', [
                    'quotation',
                    'proforma_invoice',
                ])
                ->default('quotation')
                ->after('customer_id');

            $table
                ->enum('sale_source', [
                    'customer',
                    'mechanic',
                ])
                ->default('customer')
                ->after('document_type');

            $table
                ->foreignId('mechanic_id')
                ->nullable()
                ->after('sale_source')
                ->constrained('mechanics')
                ->nullOnDelete();

            $table
                ->enum('commission_type', [
                    'percentage',
                    'fixed',
                ])
                ->nullable()
                ->after('mechanic_id');

            $table
                ->decimal('commission_percent', 5, 2)
                ->nullable()
                ->after('commission_type');

            $table
                ->decimal('commission_amount', 15, 2)
                ->default(0)
                ->after('commission_percent');

            $table
                ->enum('billing_type', [
                    'gst',
                    'non_gst',
                ])
                ->default('gst')
                ->after('valid_until');

            $table
                ->decimal('final_discount', 15, 2)
                ->default(0)
                ->after('gst_total');

            $table
                ->decimal('round_off', 15, 2)
                ->default(0)
                ->after('final_discount');

            $table
                ->text('terms_conditions')
                ->nullable()
                ->after('grand_total');

            $table
                ->timestamp('converted_at')
                ->nullable()
                ->after('status');

            $table
                ->foreignId('created_by')
                ->nullable()
                ->after('converted_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        /*
        |------------------------------------------------------------------
        | Use Branch-Wise Quotation Numbers
        |------------------------------------------------------------------
        */

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropUnique('quotations_quotation_no_unique');

            $table->unique(
                ['store_id', 'quotation_no'],
                'quotations_store_number_unique'
            );

            $table->index(
                ['company_id', 'store_id', 'quotation_date'],
                'quotations_context_date_index'
            );

            $table->index(
                ['company_id', 'store_id', 'status'],
                'quotations_context_status_index'
            );

            $table->index(
                ['company_id', 'store_id', 'document_type'],
                'quotations_context_type_index'
            );
        });

        /*
        |------------------------------------------------------------------
        | Add Final Lifecycle States
        |------------------------------------------------------------------
        */

        DB::statement(
            "ALTER TABLE quotations MODIFY status
             ENUM(
                'Draft',
                'Pending',
                'Approved',
                'Rejected',
                'Converted',
                'Expired',
                'Cancelled'
             ) NOT NULL DEFAULT 'Draft'"
        );

        /*
        |------------------------------------------------------------------
        | Upgrade Quotation Items
        |------------------------------------------------------------------
        |
        | These fields mirror Sale Items so conversion does not lose free
        | quantity, percentage/fixed discounts, or taxable values.
        |
        */

        Schema::table('quotation_items', function (Blueprint $table) {
            $table
                ->decimal('free_quantity', 15, 2)
                ->default(0)
                ->after('quantity');

            $table
                ->enum('discount_type', [
                    'percentage',
                    'fixed',
                ])
                ->default('fixed')
                ->after('sale_price');

            $table
                ->decimal('discount_value', 15, 2)
                ->default(0)
                ->after('discount_type');

            $table
                ->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('discount_value');

            $table
                ->decimal('taxable_amount', 15, 2)
                ->default(0)
                ->after('discount_amount');
        });

        /*
        |------------------------------------------------------------------
        | Link Final Sale Back To Its Quotation
        |------------------------------------------------------------------
        |
        | A quotation can create only one Sale per branch.
        | A null value means that the Sale was created directly.
        |
        */

        Schema::table('sales', function (Blueprint $table) {
            $table
                ->foreignId('quotation_id')
                ->nullable()
                ->after('store_id')
                ->constrained('quotations')
                ->nullOnDelete();

            $table->unique(
                ['store_id', 'quotation_id'],
                'sales_store_quotation_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_store_quotation_unique');
            $table->dropConstrainedForeignId('quotation_id');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('taxable_amount');
            $table->dropColumn('discount_amount');
            $table->dropColumn('discount_value');
            $table->dropColumn('discount_type');
            $table->dropColumn('free_quantity');
        });

        /*
        | Convert newer states before restoring the old enum definition.
        */

        DB::table('quotations')
            ->whereIn('status', ['Expired', 'Cancelled'])
            ->update(['status' => 'Rejected']);

        DB::statement(
            "ALTER TABLE quotations MODIFY status
             ENUM(
                'Draft',
                'Pending',
                'Approved',
                'Rejected',
                'Converted'
             ) NOT NULL DEFAULT 'Draft'"
        );

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex('quotations_context_date_index');
            $table->dropIndex('quotations_context_status_index');
            $table->dropIndex('quotations_context_type_index');

            $table->dropUnique('quotations_store_number_unique');
            $table->unique('quotation_no');

            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('converted_at');
            $table->dropColumn('terms_conditions');
            $table->dropColumn('round_off');
            $table->dropColumn('final_discount');
            $table->dropColumn('billing_type');
            $table->dropColumn('commission_amount');
            $table->dropColumn('commission_percent');
            $table->dropColumn('commission_type');
            $table->dropConstrainedForeignId('mechanic_id');
            $table->dropColumn('sale_source');
            $table->dropColumn('document_type');
        });
    }
};
