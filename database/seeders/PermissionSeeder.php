<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            'dashboard.view',

            'company.view',
            'company.create',
            'company.edit',
            'company.delete',

            'store.view',
            'store.create',
            'store.edit',
            'store.delete',

            'unit.view',
            'unit.create',
            'unit.edit',
            'unit.delete',

            'category.view',
            'category.create',
            'category.edit',
            'category.delete',

            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',

            'customer.view',
            'customer.create',
            'customer.edit',
            'customer.delete',

            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',
            'purchase.approve',

            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',

            'quotation.view',
            'quotation.create',
            'quotation.edit',
            'quotation.delete',

            'stock.view',
            'stock.adjust',

            'reports.view',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
