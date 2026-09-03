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
| This file is loaded from routes/web.php inside:
|
| auth
| verified
| business.context
|
| Therefore we do not repeat those middleware here.
|
| Individual controllers continue enforcing their own module permissions.
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
| IMPORTANT:
|
| These routes MUST be declared before:
|
| /suppliers/{supplier}
|
| Otherwise Laravel may treat:
|
| duplicate-lookup
| gst-lookup
|
| as the {supplier} route parameter.
|
*/


/*
|--------------------------------------------------------------------------
| Duplicate Supplier Lookup
|--------------------------------------------------------------------------
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
| Supplier already exists in the company but is not linked to the active
| branch.
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
| This does NOT delete the supplier master.
|
| Only the relationship between:
|
| supplier
| active branch
|
| is removed.
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
| Supplier Activate / Deactivate
|--------------------------------------------------------------------------
*/

Route::patch(
    '/suppliers/{supplier}/toggle-status',
    [
        SupplierController::class,
        'toggleStatus',
    ]
)->name(
    'suppliers.toggle-status'
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
| IMPORTANT:
|
| These routes MUST be declared before:
|
| /customers/{customer}
|
*/


/*
|--------------------------------------------------------------------------
| Duplicate Customer Lookup
|--------------------------------------------------------------------------
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
| Customer already exists in the company but is not assigned to the
| active branch.
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
| Does NOT permanently delete the customer master.
|
| Only removes the customer_stores relationship for the active branch.
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
| Customer Activate / Deactivate
|--------------------------------------------------------------------------
*/

Route::patch(
    '/customers/{customer}/toggle-status',
    [
        CustomerController::class,
        'toggleStatus',
    ]
)->name(
    'customers.toggle-status'
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
| Category Related Products
|--------------------------------------------------------------------------
|
| Category belongs to:
|
| active company
| active branch
|
| Product list is also restricted to the same company + branch.
|
*/


/*
|--------------------------------------------------------------------------
| Category Products
|--------------------------------------------------------------------------
*/

Route::get(
    '/categories/{category}/products',
    [
        CategoryController::class,
        'products',
    ]
)->name(
    'categories.products'
);


/*
|--------------------------------------------------------------------------
| Category Products CSV Export
|--------------------------------------------------------------------------
*/

Route::get(
    '/categories/{category}/products/export/csv',
    [
        CategoryController::class,
        'exportProductsCsv',
    ]
)->name(
    'categories.products.export.csv'
);

Route::get(
    '/categories/{category}/products/print',
    [
        CategoryController::class,
        'printProducts',
    ]
)->name(
    'categories.products.print'
);


/*
|--------------------------------------------------------------------------
| Category Products PDF Export
|--------------------------------------------------------------------------
*/

Route::get(
    '/categories/{category}/products/export/pdf',
    [
        CategoryController::class,
        'exportProductsPdf',
    ]
)->name(
    'categories.products.export.pdf'
);


/*
|--------------------------------------------------------------------------
| Category Activate / Deactivate
|--------------------------------------------------------------------------
*/

Route::patch(
    '/categories/{category}/toggle-status',
    [
        CategoryController::class,
        'toggleStatus',
    ]
)->name(
    'categories.toggle-status'
);


/*
|--------------------------------------------------------------------------
| Category Resource
|--------------------------------------------------------------------------
*/

Route::resource(
    'categories',
    CategoryController::class
);


Route::patch(
    '/brands/{brand}/toggle-status',
    [
        BrandController::class,
        'toggleStatus',
    ]
)->name(
    'brands.toggle-status'
);


Route::get(
    '/brands/{brand}/products',
    [
        BrandController::class,
        'products',
    ]
)->name(
    'brands.products'
);


Route::get(
    '/brands/{brand}/products/export/csv',
    [
        BrandController::class,
        'exportProductsCsv',
    ]
)->name(
    'brands.products.export.csv'
);


Route::get(
    '/brands/{brand}/products/export/pdf',
    [
        BrandController::class,
        'exportProductsPdf',
    ]
)->name(
    'brands.products.export.pdf'
);


Route::get(
    '/brands/{brand}/products/print',
    [
        BrandController::class,
        'printProducts',
    ]
)->name(
    'brands.products.print'
);


Route::resource(
    'brands',
    BrandController::class
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

Route::patch(
    '/units/{unit}/toggle-status',
    [
        UnitController::class,
        'toggleStatus',
    ]
)->name(
    'units.toggle-status'
);


Route::resource(
    'units',
    UnitController::class
);

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
|
| ProductController must enforce:
|
| active_company_id
| active_store_id
|
| Do not trust company_id or store_id supplied from the browser.
|
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
| This helper is still available for modules that genuinely need to load
| branches for a company.
|
| IMPORTANT:
|
| Business write operations must never trust this route or an arbitrary
| company ID sent by React.
|
| Controllers must use:
|
| session('active_company_id')
| session('active_store_id')
|
| for trusted business context.
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
