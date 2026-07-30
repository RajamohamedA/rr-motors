<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(Permission::all());

        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        */

        $companyAdmin = Role::firstOrCreate([
            'name' => 'Company Admin',
            'guard_name' => 'web',
        ]);

        $companyAdmin->syncPermissions(Permission::all());

        /*
        |--------------------------------------------------------------------------
        | Purchase User
        |--------------------------------------------------------------------------
        */

        $purchaseUser = Role::firstOrCreate([
            'name' => 'Purchase User',
            'guard_name' => 'web',
        ]);

        $purchaseUser->syncPermissions([
            'dashboard.view',

            'supplier.view',
            'supplier.create',
            'supplier.edit',

            'purchase.view',
            'purchase.create',
            'purchase.edit',

            'product.view',
            'stock.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sales User
        |--------------------------------------------------------------------------
        */

        $salesUser = Role::firstOrCreate([
            'name' => 'Sales User',
            'guard_name' => 'web',
        ]);

        $salesUser->syncPermissions([
            'dashboard.view',

            'customer.view',
            'customer.show',
            'customer.create',
            'customer.edit',

            'sales.view',
            'sales.create',
            'sales.edit',

            'quotation.view',
            'quotation.create',

            'product.view',
            'stock.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Store Manager
        |--------------------------------------------------------------------------
        */

        $storeManager = Role::firstOrCreate([
            'name' => 'Store Manager',
            'guard_name' => 'web',
        ]);

        $storeManager->syncPermissions([
            'dashboard.view',

            'stock.view',
            'stock.adjust',

            'product.view',

            'purchase.view',

            'sales.view',
        ]);
    }
}