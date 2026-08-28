<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Business Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Authenticated User
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | User Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $user->status,
            403,
            'Your account is inactive.'
        );


        /*
        |--------------------------------------------------------------------------
        | Active Business Context
        |--------------------------------------------------------------------------
        */

        $companyId =
            (int) $request
                ->session()
                ->get(
                    'active_company_id'
                );


        $storeId =
            (int) $request
                ->session()
                ->get(
                    'active_store_id'
                );


        /*
        |--------------------------------------------------------------------------
        | Context Required
        |--------------------------------------------------------------------------
        */

        abort_if(
            !$companyId ||
                !$storeId,
            403,
            'Please select a company and branch before entering the ERP.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->whereKey(
                $companyId
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Company Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $company->status,
            403,
            'The company is inactive.'
        );


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()
            ->whereKey(
                $storeId
            )
            ->where(
                'company_id',
                $company->id
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Branch Must Be Active
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (bool) $store->status,
            403,
            'The branch is inactive.'
        );


        /*
        |--------------------------------------------------------------------------
        | Validate User Company
        |--------------------------------------------------------------------------
        |
        | Super Admin is allowed to enter any active company/branch.
        |
        */

        if (
            !(bool) $user->is_super_user
        ) {

            abort_unless(
                (int) $user->company_id ===
                    (int) $company->id,
                403,
                'You do not have access to this company.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Branch Access
        |--------------------------------------------------------------------------
        |
        | Company Admin:
        | Can access all ACTIVE branches of their own company.
        |
        | Other business users:
        | Must have explicit user_stores assignment.
        |
        */

        if (
            !(bool) $user->is_super_user &&
            !$user->hasRole(
                'Company Admin'
            )
        ) {

            $hasBranchAccess =
                $user
                ->stores()
                ->where(
                    'stores.id',
                    $store->id
                )
                ->where(
                    'stores.company_id',
                    $company->id
                )
                ->where(
                    'stores.status',
                    true
                )
                ->exists();


            abort_unless(
                $hasBranchAccess,
                403,
                'You do not have access to this branch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Today
        |--------------------------------------------------------------------------
        */

        $today =
            now()->toDateString();


        /*
        |--------------------------------------------------------------------------
        | Today's Sales
        |--------------------------------------------------------------------------
        */

        $todaySales =
            (float) Sales::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'store_id',
                    $store->id
                )
                ->whereDate(
                    'invoice_date',
                    $today
                )
                ->sum(
                    'grand_total'
                );


        /*
        |--------------------------------------------------------------------------
        | Today's Purchases
        |--------------------------------------------------------------------------
        */

        $todayPurchases =
            (float) Purchase::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'store_id',
                    $store->id
                )
                ->whereDate(
                    'purchase_date',
                    $today
                )
                ->sum(
                    'grand_total'
                );


        /*
        |--------------------------------------------------------------------------
        | Receivables
        |--------------------------------------------------------------------------
        */

        $receivables =
            (float) Sales::query()
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'store_id',
                    $store->id
                )
                ->where(
                    'balance_amount',
                    '>',
                    0
                )
                ->sum(
                    'balance_amount'
                );


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $customersCount =
            Customer::query()
            ->where(
                'company_id',
                $company->id
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        $suppliersCount =
            Supplier::query()
            ->where(
                'company_id',
                $company->id
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $productsCount =
            Product::query()
            ->where(
                'company_id',
                $company->id
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Current Branch Stock
        |--------------------------------------------------------------------------
        |
        | Latest ledger balance per product/location is the source of truth.
        |
        */

        $latestLedgerIds =
            StockLedger::query()
            ->selectRaw(
                'MAX(id) as id'
            )
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'store_id',
                $store->id
            )
            ->groupBy(
                'product_id',
                'godown_id'
            )
            ->pluck(
                'id'
            );


        $stockRows =
            StockLedger::query()
            ->whereIn(
                'id',
                $latestLedgerIds
            )
            ->get([
                'product_id',
                'godown_id',
                'balance_qty',
                'unit_cost',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Stock Quantity
        |--------------------------------------------------------------------------
        */

        $totalStockQty =
            (float) $stockRows
                ->sum(
                    'balance_qty'
                );


        /*
        |--------------------------------------------------------------------------
        | Stock Value
        |--------------------------------------------------------------------------
        */

        $stockValue =
            (float) $stockRows
                ->sum(
                    function (
                        $row
                    ) {

                        return
                            (float) $row->balance_qty
                            *
                            (float) $row->unit_cost;
                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Low Stock
        |--------------------------------------------------------------------------
        |
        | For now we use balance <= 5.
        |
        | Later we should move this to product.reorder_level.
        |
        */

        $lowStockCount =
            $stockRows
            ->filter(
                fn($row) =>
                (float) $row->balance_qty <= 5
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Company Branch Summary
        |--------------------------------------------------------------------------
        */

        $branchQuery =
            Store::query()
            ->where(
                'company_id',
                $company->id
            );


        $totalBranches =
            (clone $branchQuery)
            ->count();


        $activeBranches =
            (clone $branchQuery)
            ->where(
                'status',
                true
            )
            ->count();


        $pendingBranches =
            (clone $branchQuery)
            ->where(
                'status',
                false
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Company Users
        |--------------------------------------------------------------------------
        */

        $totalUsers =
            User::query()
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'is_super_user',
                false
            )
            ->count();


        $activeUsers =
            User::query()
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'is_super_user',
                false
            )
            ->where(
                'status',
                true
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Recent Sales
        |--------------------------------------------------------------------------
        */

        $recentSales =
            Sales::query()
            ->with([
                'customer:id,customer_name',
            ])
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'store_id',
                $store->id
            )
            ->latest(
                'id'
            )
            ->limit(
                5
            )
            ->get()
            ->map(
                fn($sale) => [

                    'id' =>
                    $sale->id,

                    'invoice_no' =>
                    $sale->invoice_no,

                    'invoice_date' =>
                    $sale->invoice_date,

                    'customer' =>
                    $sale->customer
                        ? $sale->customer->customer_name
                        : 'Walk-in Customer',

                    'grand_total' =>
                    (float) $sale->grand_total,

                    'balance_amount' =>
                    (float) $sale->balance_amount,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Recent Purchases
        |--------------------------------------------------------------------------
        */

        $recentPurchases =
            Purchase::query()
            ->with([
                'supplier:id,supplier_name',
            ])
            ->where(
                'company_id',
                $company->id
            )
            ->where(
                'store_id',
                $store->id
            )
            ->latest(
                'id'
            )
            ->limit(
                5
            )
            ->get()
            ->map(
                fn($purchase) => [

                    'id' =>
                    $purchase->id,

                    'purchase_no' =>
                    $purchase->purchase_no,

                    'purchase_date' =>
                    $purchase->purchase_date,

                    'supplier' =>
                    $purchase->supplier
                        ? $purchase->supplier->supplier_name
                        : 'Supplier',

                    'grand_total' =>
                    (float) $purchase->grand_total,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Store::query()
            ->where(
                'company_id',
                $company->id
            )
            ->orderBy(
                'branch_number'
            )
            ->orderBy(
                'store_name'
            )
            ->get([
                'id',
                'branch_number',
                'store_code',
                'store_name',
                'city',
                'state',
                'status',
            ])
            ->map(
                fn($branch) => [

                    'id' =>
                    $branch->id,

                    'branch_number' =>
                    $branch->branch_number,

                    'code' =>
                    $branch->store_code,

                    'name' =>
                    $branch->store_name,

                    'city' =>
                    $branch->city,

                    'state' =>
                    $branch->state,

                    'status' =>
                    (bool) $branch->status,

                    'is_current' =>
                    (int) $branch->id ===
                        (int) $store->id,
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Render Business Dashboard
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Dashboard',
            [

                /*
                |--------------------------------------------------------------------------
                | Context
                |--------------------------------------------------------------------------
                */

                'context' => [

                    'company' => [

                        'id' =>
                        $company->id,

                        'name' =>
                        $company->company_name,

                        'code' =>
                        $company->company_code,

                        'owner_name' =>
                        $company->owner_name,

                        'gst_number' =>
                        $company->gst_number,

                        'pan_number' =>
                        $company->pan_number,

                        'email' =>
                        $company->email,

                        'phone' =>
                        $company->phone,

                        'website' =>
                        $company->website,

                        'address' =>
                        $company->address,

                        'city' =>
                        $company->city,

                        'state' =>
                        $company->state,

                        'country' =>
                        $company->country,

                        'pincode' =>
                        $company->pincode,

                        'status' =>
                        (bool) $company->status,
                    ],


                    'branch' => [

                        'id' =>
                        $store->id,

                        'name' =>
                        $store->store_name,

                        'code' =>
                        $store->store_code,

                        'city' =>
                        $store->city,

                        'state' =>
                        $store->state,

                        'status' =>
                        (bool) $store->status,
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Current User
                    |--------------------------------------------------------------------------
                    */

                    'user' => [

                        'id' =>
                        $user->id,

                        'name' =>
                        $user->name,

                        'role' =>
                        (bool) $user->is_super_user
                            ? 'Super Administrator'
                            : (
                                $user
                                ->getRoleNames()
                                ->first()
                                ?? 'User'
                            ),
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | Super Admin Business Context
                    |--------------------------------------------------------------------------
                    */

                    'is_super_admin_context' =>
                    (bool) $request
                        ->session()
                        ->get(
                            'super_admin_business_context',
                            false
                        ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Performance
                |--------------------------------------------------------------------------
                */

                'performance' => [

                    'today_sales' =>
                    $todaySales,

                    'today_purchases' =>
                    $todayPurchases,

                    'receivables' =>
                    $receivables,

                    'stock_value' =>
                    $stockValue,

                    'stock_quantity' =>
                    $totalStockQty,

                    'low_stock_count' =>
                    $lowStockCount,

                    'customers_count' =>
                    $customersCount,

                    'suppliers_count' =>
                    $suppliersCount,

                    'products_count' =>
                    $productsCount,
                ],


                /*
                |--------------------------------------------------------------------------
                | Branch Summary
                |--------------------------------------------------------------------------
                */

                'branchSummary' => [

                    'total' =>
                    $totalBranches,

                    'active' =>
                    $activeBranches,

                    'pending' =>
                    $pendingBranches,
                ],


                /*
                |--------------------------------------------------------------------------
                | User Summary
                |--------------------------------------------------------------------------
                */

                'userSummary' => [

                    'total' =>
                    $totalUsers,

                    'active' =>
                    $activeUsers,

                    'inactive' =>
                    $totalUsers -
                        $activeUsers,
                ],


                /*
                |--------------------------------------------------------------------------
                | Branches
                |--------------------------------------------------------------------------
                */

                'branches' =>
                $branches,


                /*
                |--------------------------------------------------------------------------
                | Recent Transactions
                |--------------------------------------------------------------------------
                */

                'recentSales' =>
                $recentSales,

                'recentPurchases' =>
                $recentPurchases,
            ]
        );
    }
}
