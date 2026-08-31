<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\GodownController;

use App\Models\Store;


/*
|--------------------------------------------------------------------------
| Master Modules
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| This file is already loaded from routes/web.php inside:
|
| auth
| verified
| business.context
|
| So we do not repeat those middleware here.
|
| Individual controllers continue to enforce their own permissions.
|
*/


/*
|--------------------------------------------------------------------------
| Company
|--------------------------------------------------------------------------
*/

Route::resource(
    'companies',
    CompanyController::class
);


/*
|--------------------------------------------------------------------------
| Store / Branch
|--------------------------------------------------------------------------
*/

Route::resource(
    'stores',
    StoreController::class
);


/*
|--------------------------------------------------------------------------
| Supplier - Lookup Routes
|--------------------------------------------------------------------------
|
| These routes MUST be defined before:
|
| /suppliers/{supplier}
|
| Otherwise Laravel may treat:
|
| duplicate-lookup
| gst-lookup
|
| as the {supplier} parameter.
|
*/


/*
|--------------------------------------------------------------------------
| Duplicate Supplier Lookup
|--------------------------------------------------------------------------
|
| Checks the current company for an existing supplier using:
|
| GST Number
| Phone
| Email
|
*/

Route::post(
    '/suppliers/duplicate-lookup',
    [
        SupplierController::class,
        'duplicateLookup',
    ]
)->name(
    'suppliers.duplicate-lookup'
);


/*
|--------------------------------------------------------------------------
| Supplier GST Lookup
|--------------------------------------------------------------------------
|
| React
|    ↓
| SupplierController
|    ↓
| GstLookupService
|    ↓
| GstProviderInterface
|
*/

Route::post(
    '/suppliers/gst-lookup',
    [
        SupplierController::class,
        'gstLookup',
    ]
)->name(
    'suppliers.gst-lookup'
);


/*
|--------------------------------------------------------------------------
| Attach Existing Supplier To Current Branch
|--------------------------------------------------------------------------
|
| Supplier already exists inside the same company, but is not yet linked
| to the active branch.
|
| Only supplier_stores is updated.
|
*/

Route::post(
    '/suppliers/{supplier}/attach-existing',
    [
        SupplierController::class,
        'attachExisting',
    ]
)->name(
    'suppliers.attach-existing'
);


/*
|--------------------------------------------------------------------------
| Remove Supplier From Current Branch
|--------------------------------------------------------------------------
|
| This DOES NOT delete the supplier master.
|
| Only the supplier_stores relationship for the current branch is removed.
|
*/

Route::delete(
    '/suppliers/{supplier}/remove-from-branch',
    [
        SupplierController::class,
        'removeFromBranch',
    ]
)->name(
    'suppliers.remove-from-branch'
);


/*
|--------------------------------------------------------------------------
| Supplier Resource
|--------------------------------------------------------------------------
*/

Route::resource(
    'suppliers',
    SupplierController::class
);


/*
|--------------------------------------------------------------------------
| Customer - Lookup Routes
|--------------------------------------------------------------------------
|
| These routes MUST be defined before:
|
| /customers/{customer}
|
| Otherwise Laravel may treat:
|
| duplicate-lookup
| gst-lookup
|
| as the {customer} parameter.
|
*/


/*
|--------------------------------------------------------------------------
| Duplicate Customer Lookup
|--------------------------------------------------------------------------
|
| Checks current company for matching:
|
| GST
| Mobile
| Email
|
*/

Route::post(
    '/customers/duplicate-lookup',
    [
        CustomerController::class,
        'duplicateLookup',
    ]
)->name(
    'customers.duplicate-lookup'
);


/*
|--------------------------------------------------------------------------
| Customer GST Lookup
|--------------------------------------------------------------------------
|
| React
|    ↓
| CustomerController
|    ↓
| GstLookupService
|    ↓
| GstProviderInterface
|
*/

Route::post(
    '/customers/gst-lookup',
    [
        CustomerController::class,
        'gstLookup',
    ]
)->name(
    'customers.gst-lookup'
);


/*
|--------------------------------------------------------------------------
| Attach Existing Customer To Current Branch
|--------------------------------------------------------------------------
|
| The customer already exists in the same company but belongs to another
| branch.
|
| Only customer_stores is updated.
|
*/

Route::post(
    '/customers/{customer}/attach-existing',
    [
        CustomerController::class,
        'attachExisting',
    ]
)->name(
    'customers.attach-existing'
);


/*
|--------------------------------------------------------------------------
| Remove Customer From Current Branch
|--------------------------------------------------------------------------
|
| Does NOT permanently delete the customer.
|
| Only removes:
|
| customer_stores
|
| current customer + current branch
|
*/

Route::delete(
    '/customers/{customer}/remove-from-branch',
    [
        CustomerController::class,
        'removeFromBranch',
    ]
)->name(
    'customers.remove-from-branch'
);


/*
|--------------------------------------------------------------------------
| Customer Resource
|--------------------------------------------------------------------------
*/

Route::resource(
    'customers',
    CustomerController::class
);


/*
|--------------------------------------------------------------------------
| Category
|--------------------------------------------------------------------------
*/

Route::resource(
    'categories',
    CategoryController::class
);


/*
|--------------------------------------------------------------------------
| Brand
|--------------------------------------------------------------------------
*/

Route::resource(
    'brands',
    BrandController::class
);


/*
|--------------------------------------------------------------------------
| Unit
|--------------------------------------------------------------------------
*/

Route::resource(
    'units',
    UnitController::class
);


/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

Route::resource(
    'products',
    ProductController::class
);


/*
|--------------------------------------------------------------------------
| Godown
|--------------------------------------------------------------------------
*/

Route::resource(
    'godowns',
    GodownController::class
);


/*
|--------------------------------------------------------------------------
| AJAX / API Helpers
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Product Search
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/products/search',
    [
        ProductController::class,
        'search',
    ]
)->name(
    'products.search'
);


/*
|--------------------------------------------------------------------------
| Get Stores For Company
|--------------------------------------------------------------------------
|
| This helper is still available for modules that need branch loading.
|
| IMPORTANT:
|
| Do not trust arbitrary company IDs in business write operations.
| Controllers must continue using active_company_id and active_store_id
| from the trusted business session.
|
*/

Route::get(
    '/get-stores/{company}',
    function (
        $company
    ) {

        return Store::query()

            ->where(
                'company_id',
                $company
            )

            ->where(
                'status',
                true
            )

            ->orderBy(
                'store_name'
            )

            ->get([
                'id',
                'store_name',
                'store_code',
            ]);
    }
);
