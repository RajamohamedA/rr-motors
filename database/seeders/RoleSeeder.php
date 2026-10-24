<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Protected global system role.
        |
        | Company Admin can NEVER assign this role.
        |
        */
        $superAdmin =
            Role::updateOrCreate(
                [
                    'name' => 'Super Admin',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Full platform administrator with access to all companies and system settings.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $superAdmin->syncPermissions(
            Permission::query()
                ->where(
                    'guard_name',
                    'web'
                )
                ->get()
        );
        /*
        |--------------------------------------------------------------------------
        | Company Admin
        |--------------------------------------------------------------------------
        |
        | Protected global system role.
        |
        | Assigned by Super Admin only.
        |
        */
        $companyAdmin =
            Role::updateOrCreate(
                [
                    'name' => 'Company Admin',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Company administrator with full access to company operations, branches, users and roles.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        /*
        |--------------------------------------------------------------------------
        | Normalize Legacy Company Admin Role
        |--------------------------------------------------------------------------
        |
        | A case-insensitive database can match an older "company admin" row
        | without changing its stored letter case. Spatie role checks use the
        | stored name, so enforce one canonical value on the same role record.
        |
        */
        if ($companyAdmin->name !== 'Company Admin') {
            $companyAdmin->forceFill([
                'name' => 'Company Admin',
            ])->save();
        }
        $companyAdmin->syncPermissions(
            Permission::query()
                ->where(
                    'guard_name',
                    'web'
                )
                ->get()
        );
        $branchModules = [
            'dashboard',
            'godown',
            'customer',
            'supplier',
            'product',
            'category',
            'brand',
            'unit',
            'mechanic',
            'barcode-label',
            'purchase',
            'purchase-return',
            'quotation',
            'sales',
            'sales-return',
            'stock',
            'current-stock',
            'stock-ledger',
            'stock-adjustment',
            'stock-transfer',
            'accounts',
            'account',
            'voucher',
            'expense',
            'customer-payment',
            'customer-ledger',
            'supplier-payment',
            'supplier-ledger',
            'mechanic-account',
            'mechanic-payment',
            'mechanic-ledger',
            'reports',
            'purchase-report',
            'sales-report',
            'accounts-report',
        ];

        /*
        |--------------------------------------------------------------------------
        | Store Manager
        |--------------------------------------------------------------------------
        |
        | Default assignable system role.
        |
        | Company Admin can assign this role.
        |
        */
        $storeManager =
            Role::updateOrCreate(
                [
                    'name' => 'Store Manager',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Full operational access to masters, sales, purchases, inventory, accounts and reports within assigned active branches.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $storeManager->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->get()
                ->filter(fn(Permission $permission) => in_array(
                    explode('.', $permission->name, 2)[0],
                    $branchModules,
                    true
                ))
                ->values()
        );

        /*
        |--------------------------------------------------------------------------
        | Purchase User
        |--------------------------------------------------------------------------
        */
        $purchaseUser =
            Role::updateOrCreate(
                [
                    'name' => 'Purchase User',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Handles suppliers, purchases, purchase returns and purchase-related stock operations.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $purchaseUser->syncPermissions(
            $this->permissions([
                'dashboard.view',
                /*
                |--------------------------------------------------------------------------
                | Supplier
                |--------------------------------------------------------------------------
                */
                'supplier.view',
                'supplier.show',
                'supplier.create',
                'supplier.edit',
                /*
                |--------------------------------------------------------------------------
                | Products
                |--------------------------------------------------------------------------
                */
                'product.view',
                'product.show',
                'category.view',
                'brand.view',
                'unit.view',
                'godown.view',
                /*
                |--------------------------------------------------------------------------
                | Purchase
                |--------------------------------------------------------------------------
                */
                'purchase.view',
                'purchase.show',
                'purchase.create',
                'purchase.edit',
                'purchase.print',
                /*
                |--------------------------------------------------------------------------
                | Purchase Returns
                |--------------------------------------------------------------------------
                */
                'purchase-return.view',
                'purchase-return.show',
                'purchase-return.create',
                'purchase-return.edit',
                'purchase-return.print',
                /*
                |--------------------------------------------------------------------------
                | Stock
                |--------------------------------------------------------------------------
                */
                'current-stock.view',
                'stock-ledger.view',
                'stock-ledger.show',
                /*
                |--------------------------------------------------------------------------
                | Purchase Reports
                |--------------------------------------------------------------------------
                */
                'purchase-report.view',
                'purchase-report.print',
            ])
        );
        /*
        |--------------------------------------------------------------------------
        | Sales User
        |--------------------------------------------------------------------------
        */
        $salesUser =
            Role::updateOrCreate(
                [
                    'name' => 'Sales User',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Handles customers, sales, sales returns and quotations.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $salesUser->syncPermissions(
            $this->permissions([
                'dashboard.view',
                /*
                |--------------------------------------------------------------------------
                | Customers
                |--------------------------------------------------------------------------
                */
                'customer.view',
                'customer.show',
                'customer.create',
                'customer.edit',
                /*
                |--------------------------------------------------------------------------
                | Product Lookup
                |--------------------------------------------------------------------------
                */
                'product.view',
                'product.show',
                'category.view',
                'brand.view',
                'unit.view',
                /*
                |--------------------------------------------------------------------------
                | Sales
                |--------------------------------------------------------------------------
                */
                'sales.view',
                'sales.show',
                'sales.create',
                'sales.edit',
                'sales.print',
                /*
                |--------------------------------------------------------------------------
                | Sales Returns
                |--------------------------------------------------------------------------
                */
                'sales-return.view',
                'sales-return.show',
                'sales-return.create',
                'sales-return.edit',
                'sales-return.print',
                /*
                |--------------------------------------------------------------------------
                | Quotations
                |--------------------------------------------------------------------------
                */
                'quotation.view',
                'quotation.show',
                'quotation.create',
                'quotation.edit',
                'quotation.print',
                'quotation.convert-sale',
                /*
                |--------------------------------------------------------------------------
                | Stock Lookup
                |--------------------------------------------------------------------------
                */
                'current-stock.view',
                'stock-ledger.view',
                /*
                |--------------------------------------------------------------------------
                | Reports
                |--------------------------------------------------------------------------
                */
                'sales-report.view',
            ])
        );
        /*
        |--------------------------------------------------------------------------
        | Inventory User
        |--------------------------------------------------------------------------
        */
        $inventoryUser =
            Role::updateOrCreate(
                [
                    'name' => 'Inventory User',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Manages stock visibility, stock adjustments and stock transfers.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $inventoryUser->syncPermissions(
            $this->permissions([
                'dashboard.view',
                /*
                |--------------------------------------------------------------------------
                | Product Masters
                |--------------------------------------------------------------------------
                */
                'product.view',
                'product.show',
                'category.view',
                'brand.view',
                'unit.view',
                'godown.view',
                'godown.show',
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
                /*
                |--------------------------------------------------------------------------
                | Stock Adjustment
                |--------------------------------------------------------------------------
                */
                'stock-adjustment.view',
                'stock-adjustment.show',
                'stock-adjustment.create',
                'stock-adjustment.edit',
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
                'stock-transfer.print',
            ])
        );
        /*
        |--------------------------------------------------------------------------
        | Accounts User
        |--------------------------------------------------------------------------
        */
        $accountsUser =
            Role::updateOrCreate(
                [
                    'name' => 'Accounts User',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Handles customer payments, ledgers and financial reports.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $accountsUser->syncPermissions(
            $this->permissions([
                'dashboard.view',
                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */
                'customer.view',
                'customer.show',
                /*
                |--------------------------------------------------------------------------
                | Sales Lookup
                |--------------------------------------------------------------------------
                */
                'sales.view',
                'sales.show',
                /*
                |--------------------------------------------------------------------------
                | Accounts
                |--------------------------------------------------------------------------
                */
                'accounts.view',
                'customer-payment.view',
                'customer-payment.show',
                'customer-payment.create',
                'customer-payment.print',
                'customer-ledger.view',
                'customer-ledger.print',
                /*
                |--------------------------------------------------------------------------
                | Reports
                |--------------------------------------------------------------------------
                */
                'reports.view',
                'sales-report.view',
                'sales-report.print',
                'sales-report.pdf',
                'sales-report.excel',
                'purchase-report.view',
                'purchase-report.print',
            ])
        );
        /*
        |--------------------------------------------------------------------------
        | Read Only
        |--------------------------------------------------------------------------
        |
        | Useful for owners / auditors / staff who must not change data.
        |
        */
        $readOnly =
            Role::updateOrCreate(
                [
                    'name' => 'Read Only',
                    'guard_name' => 'web',
                    'company_id' => null,
                ],
                [
                    'description' => 'Read-only access to business records and reports without create, edit or delete access.',
                    'status' => true,
                    'is_system_role' => true,
                ]
            );
        $readOnly->syncPermissions(
            $this->permissions([
                'dashboard.view',
                /*
                |--------------------------------------------------------------------------
                | Masters
                |--------------------------------------------------------------------------
                */
                'customer.view',
                'customer.show',
                'supplier.view',
                'supplier.show',
                'product.view',
                'product.show',
                'category.view',
                'category.show',
                'brand.view',
                'brand.show',
                'unit.view',
                'unit.show',
                'godown.view',
                'godown.show',
                /*
                |--------------------------------------------------------------------------
                | Purchase
                |--------------------------------------------------------------------------
                */
                'purchase.view',
                'purchase.show',
                'purchase-return.view',
                'purchase-return.show',
                /*
                |--------------------------------------------------------------------------
                | Sales
                |--------------------------------------------------------------------------
                */
                'sales.view',
                'sales.show',
                'sales-return.view',
                'sales-return.show',
                'quotation.view',
                'quotation.show',
                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */
                'current-stock.view',
                'stock-ledger.view',
                'stock-ledger.show',
                'stock-adjustment.view',
                'stock-adjustment.show',
                'stock-transfer.view',
                'stock-transfer.show',
                /*
                |--------------------------------------------------------------------------
                | Accounts
                |--------------------------------------------------------------------------
                */
                'accounts.view',
                'customer-payment.view',
                'customer-payment.show',
                'customer-ledger.view',
                /*
                |--------------------------------------------------------------------------
                | Reports
                |--------------------------------------------------------------------------
                */
                'reports.view',
                'purchase-report.view',
                'sales-report.view',
            ])
        );
        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache Again
        |--------------------------------------------------------------------------
        */
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Existing Permissions
    |--------------------------------------------------------------------------
    |
    | Important:
    |
    | Using Permission models instead of passing raw permission names makes
    | this seeder tolerant while the permission set evolves.
    |
    | Only permissions that actually exist in the permissions table are
    | returned.
    |
    */
    private function permissions(
        array $permissions
    ) {
        return Permission::query()
            ->where(
                'guard_name',
                'web'
            )
            ->whereIn(
                'name',
                $permissions
            )
            ->get();
    }
}
