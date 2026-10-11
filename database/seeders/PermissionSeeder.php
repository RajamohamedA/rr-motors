<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder

{

    public function run(): void

    {

        /*

        |--------------------------------------------------------------------------

        | Clear Permission Cache

        |--------------------------------------------------------------------------

        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*

        |--------------------------------------------------------------------------

        | Permissions

        |--------------------------------------------------------------------------

        */

        $permissions = [

            /*

        |--------------------------------------------------------------------------

        | Dashboard

        |--------------------------------------------------------------------------

            */

            'dashboard.view',

            /*

        |--------------------------------------------------------------------------

        | Company

        |--------------------------------------------------------------------------

            */

            'company.view',

            'company.show',

            'company.create',

            'company.edit',

            'company.delete',

            /*

        |--------------------------------------------------------------------------

        | Branch / Store

        |--------------------------------------------------------------------------

            */

            'store.view',

            'store.show',

            'store.create',

            'store.edit',

            'store.delete',

            /*

        |--------------------------------------------------------------------------

        | Godown

        |--------------------------------------------------------------------------

            */

            'godown.view',

            'godown.show',

            'godown.create',

            'godown.edit',

            'godown.delete',

            /*

        |--------------------------------------------------------------------------

        | Brand

        |--------------------------------------------------------------------------

            */

            'brand.view',

            'brand.show',

            'brand.create',

            'brand.edit',

            'brand.delete',

            /*

        |--------------------------------------------------------------------------

        | Unit

        |--------------------------------------------------------------------------

            */

            'unit.view',

            'unit.show',

            'unit.create',

            'unit.edit',

            'unit.delete',

            /*

        |--------------------------------------------------------------------------

        | Category

        |--------------------------------------------------------------------------

            */

            'category.view',

            'category.show',

            'category.create',

            'category.edit',

            'category.delete',

            /*

        |--------------------------------------------------------------------------

        | Product

        |--------------------------------------------------------------------------

            */

            'product.view',

            'product.show',

            'product.create',

            'product.edit',

            'product.delete',

            /*

        |--------------------------------------------------------------------------

        | Barcode Labels

        |--------------------------------------------------------------------------

            */

            'barcode-label.view',

            'barcode-label.create',

            'barcode-label.print',

            /*

        |--------------------------------------------------------------------------

        | Supplier

        |--------------------------------------------------------------------------

            */

            'supplier.view',

            'supplier.show',

            'supplier.create',

            'supplier.edit',

            'supplier.delete',

            /*

        |--------------------------------------------------------------------------

        | Customer

        |--------------------------------------------------------------------------

            */

            'customer.view',

            'customer.show',

            'customer.create',

            'customer.edit',

            'customer.delete',

            /*

        |--------------------------------------------------------------------------

        | Purchase

        |--------------------------------------------------------------------------

            */

            'purchase.view',

            'purchase.show',

            'purchase.create',

            'purchase.edit',

            'purchase.delete',

            'purchase.approve',

            'purchase.print',

            /*

        |--------------------------------------------------------------------------

        | Purchase Return

        |--------------------------------------------------------------------------

            */

            'purchase-return.view',

            'purchase-return.show',

            'purchase-return.create',

            'purchase-return.edit',

            'purchase-return.delete',

            'purchase-return.print',

            /*

        |--------------------------------------------------------------------------

        | Purchase Report

        |--------------------------------------------------------------------------

            */

            'purchase-report.view',

            'purchase-report.print',

            'purchase-report.pdf',

            'purchase-report.excel',

            /*

        |--------------------------------------------------------------------------

        | Sales

        |--------------------------------------------------------------------------

            */

            'sales.view',

            'sales.show',

            'sales.create',

            'sales.edit',

            'sales.delete',

            'sales.print',

            /*

        |--------------------------------------------------------------------------

        | Sales Return

        |--------------------------------------------------------------------------

            */

            'sales-return.view',

            'sales-return.show',

            'sales-return.create',

            'sales-return.edit',

            'sales-return.delete',

            'sales-return.print',

            /*

        |--------------------------------------------------------------------------

        | Sales Report

        |--------------------------------------------------------------------------

            */

            'sales-report.view',

            'sales-report.print',

            'sales-report.pdf',

            'sales-report.excel',

            /*

        |--------------------------------------------------------------------------

        | Quotation

        |--------------------------------------------------------------------------

            */

            'quotation.view',

            'quotation.show',

            'quotation.create',

            'quotation.edit',

            'quotation.delete',

            'quotation.print',

            'quotation.convert-sale',

            /*

        |--------------------------------------------------------------------------

        | Current Stock

        |--------------------------------------------------------------------------

            */

            'current-stock.view',

            'current-stock.print',

            'current-stock.pdf',

            'current-stock.excel',

            /*

        |--------------------------------------------------------------------------

        | Stock Ledger

        |--------------------------------------------------------------------------

            */

            'stock-ledger.view',

            'stock-ledger.show',

            'stock-ledger.print',

            'stock-ledger.pdf',

            'stock-ledger.excel',

            /*

        |--------------------------------------------------------------------------

        | Stock Adjustment

        |--------------------------------------------------------------------------

            */

            'stock-adjustment.view',

            'stock-adjustment.show',

            'stock-adjustment.create',

            'stock-adjustment.edit',

            'stock-adjustment.delete',

            'stock-adjustment.print',

            /*

        |--------------------------------------------------------------------------

        | Stock Transfer

        |--------------------------------------------------------------------------

            */

            'stock-transfer.view',

            'stock-transfer.show',

            'stock-transfer.create',

            'stock-transfer.edit',

            'stock-transfer.delete',

            'stock-transfer.print',

            /*

        |--------------------------------------------------------------------------

        | General Stock

        |--------------------------------------------------------------------------

            */

            'stock.view',

            'stock.adjust',

            /*

        |--------------------------------------------------------------------------

        | Accounts

        |--------------------------------------------------------------------------

            */

            'accounts.view',

            'accounts.dashboard.view',

            /*

        |--------------------------------------------------------------------------

        | Account Master

        |--------------------------------------------------------------------------

            */

            'account.view',

            'account.create',

            'account.edit',

            /*

        |--------------------------------------------------------------------------

        | Customer Payments

        |--------------------------------------------------------------------------

            */

            'customer-payment.view',

            'customer-payment.show',

            'customer-payment.create',

            'customer-payment.edit',

            'customer-payment.delete',

            'customer-payment.print',

            /*

        |--------------------------------------------------------------------------

        | Supplier Payments

        |--------------------------------------------------------------------------

            */

            'supplier-payment.view',

            'supplier-payment.show',

            'supplier-payment.create',

            'supplier-payment.delete',

            /*

        |--------------------------------------------------------------------------

        | Supplier Ledger

        |--------------------------------------------------------------------------

            */

            'supplier-ledger.view',

            'supplier-ledger.print',

            'supplier-ledger.pdf',

            'supplier-ledger.excel',

            /*

        |--------------------------------------------------------------------------

        | Customer Ledger

        |--------------------------------------------------------------------------

            */

            'customer-ledger.view',

            'customer-ledger.print',

            'customer-ledger.pdf',

            'customer-ledger.excel',

            /*

        |--------------------------------------------------------------------------

        | Mechanic

        |--------------------------------------------------------------------------

            */

            'mechanic.view',

            'mechanic.show',

            'mechanic.create',

            'mechanic.edit',

            'mechanic.delete',

            /*

        |--------------------------------------------------------------------------

        | Mechanic Payments

        |--------------------------------------------------------------------------

            */

            'mechanic-payment.view',

            'mechanic-payment.show',

            'mechanic-payment.create',

            'mechanic-payment.delete',

            'mechanic-payment.print',

            /*

        |--------------------------------------------------------------------------

        | Mechanic Ledger

        |--------------------------------------------------------------------------

            */

            'mechanic-account.view',

            'mechanic-ledger.view',

            'mechanic-ledger.print',

            'mechanic-ledger.pdf',

            'mechanic-ledger.excel',

            /*

        |--------------------------------------------------------------------------

        | Vouchers

        |--------------------------------------------------------------------------

            */

            'voucher.view',

            'voucher.show',

            'voucher.create',

            'voucher.delete',

            /*

        |--------------------------------------------------------------------------

        | Expenses

        |--------------------------------------------------------------------------

            */

            'expense.view',

            'expense.show',

            'expense.create',

            'expense.delete',

            'expense.print',

            /*

        |--------------------------------------------------------------------------

        | Accounts Reports

        |--------------------------------------------------------------------------

            */

            'accounts-report.view',

            /*

        |--------------------------------------------------------------------------

        | Reports

        |--------------------------------------------------------------------------

            */

            'reports.view',

            /*

        |--------------------------------------------------------------------------

        | Users & Access

        |--------------------------------------------------------------------------

            */

            'users.view',

            'users.show',

            'users.create',

            'users.edit',

            'users.delete',

            'users.status',

            /*

        |--------------------------------------------------------------------------

        | Roles & Permissions

        |--------------------------------------------------------------------------

            */

            'roles.view',

            'roles.show',

            'roles.create',

            'roles.edit',

            'roles.delete',

            'roles.status',

        ];

        /*

        |--------------------------------------------------------------------------

        | Create / Update Permissions

        |--------------------------------------------------------------------------

        */

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([

                'name' => $permission,

                'guard_name' => 'web',

            ]);
        }

        /*

        |--------------------------------------------------------------------------

        | Clear Permission Cache Again

        |--------------------------------------------------------------------------

        */

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
