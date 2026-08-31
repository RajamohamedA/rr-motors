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
| Supplier
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
| Otherwise Laravel may treat "duplicate-lookup" or "gst-lookup"
| as a customer route parameter.
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
| Used by React Create Customer before saving.
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
| GST Lookup
|--------------------------------------------------------------------------
|
| React
|    ↓
| CustomerController
|    ↓
| GstLookupService
|    ↓
| GST Provider
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
| This adds only the customer_stores relationship.
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
| NOTE:
|
| This route should eventually be moved behind proper company isolation.
| Do not trust arbitrary company IDs from normal employees.
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
