<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            // Company
            ['module' => 'Company', 'permission' => 'View'],
            ['module' => 'Company', 'permission' => 'Create'],
            ['module' => 'Company', 'permission' => 'Edit'],
            ['module' => 'Company', 'permission' => 'Delete'],

            // Store
            ['module' => 'Store', 'permission' => 'View'],
            ['module' => 'Store', 'permission' => 'Create'],
            ['module' => 'Store', 'permission' => 'Edit'],
            ['module' => 'Store', 'permission' => 'Delete'],

            // Product
            ['module' => 'Product', 'permission' => 'View'],
            ['module' => 'Product', 'permission' => 'Create'],
            ['module' => 'Product', 'permission' => 'Edit'],
            ['module' => 'Product', 'permission' => 'Delete'],

            // Purchase
            ['module' => 'Purchase', 'permission' => 'View'],
            ['module' => 'Purchase', 'permission' => 'Create'],
            ['module' => 'Purchase', 'permission' => 'Approve'],

            // Sales
            ['module' => 'Sales', 'permission' => 'View'],
            ['module' => 'Sales', 'permission' => 'Create'],

            // Stock
            ['module' => 'Stock', 'permission' => 'View'],
            ['module' => 'Stock', 'permission' => 'Adjust'],

            // Reports
            ['module' => 'Reports', 'permission' => 'View'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
