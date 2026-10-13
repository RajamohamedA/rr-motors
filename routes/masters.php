<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CompanyController;

use App\Http\Controllers\StoreController;

use App\Http\Controllers\SupplierController;

use App\Http\Controllers\SupplierImportController;

use App\Http\Controllers\CustomerController;

use App\Http\Controllers\CustomerImportController;

use App\Http\Controllers\CategoryController;

use App\Http\Controllers\BrandController;

use App\Http\Controllers\UnitController;

use App\Http\Controllers\ProductController;

use App\Http\Controllers\ProductImportController;

use App\Http\Controllers\GodownController;

use App\Http\Controllers\MechanicController;

use App\Http\Controllers\MechanicImportController;

use App\Models\Store;

/*

|--------------------------------------------------------------------------

| Master Modules

|--------------------------------------------------------------------------

|

| This file is loaded from routes/web.php inside:

|

| auth

| verified

| business.context

|

| Therefore these middleware do not need to be repeated here.

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

|

| IMPORTANT:

|

| Custom routes must stay before:

|

| /suppliers/{supplier}

|

*/

/*

|--------------------------------------------------------------------------

| Supplier Duplicate Lookup

|--------------------------------------------------------------------------

*/

/*

|--------------------------------------------------------------------------

| Supplier Import

|--------------------------------------------------------------------------

|

| Fixed import routes must stay before /suppliers/{supplier}.

|

*/

Route::get(

    '/suppliers/import',

    [SupplierImportController::class, 'create']

)->name(

    'suppliers.import.create'

);

Route::get(

    '/suppliers/import/template',

    [SupplierImportController::class, 'downloadTemplate']

)->name(

    'suppliers.import.template'

);

Route::post(

    '/suppliers/import/preview',

    [SupplierImportController::class, 'preview']

)->name(

    'suppliers.import.preview'

);

Route::post(

    '/suppliers/import',

    [SupplierImportController::class, 'store']

)->name(

    'suppliers.import.store'

);

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

| Attach Existing Supplier

|--------------------------------------------------------------------------

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

| Remove Supplier From Branch

|--------------------------------------------------------------------------

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

| Supplier Status

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

| Customer

|--------------------------------------------------------------------------

|

| Custom routes must stay before:

|

| /customers/{customer}

|

*/

/*

|--------------------------------------------------------------------------

| Customer Duplicate Lookup

|--------------------------------------------------------------------------

*/

/*

|--------------------------------------------------------------------------

| Customer Import

|--------------------------------------------------------------------------

|

| Fixed import routes must stay before /customers/{customer}.

|

*/

Route::get(

    '/customers/import',

    [CustomerImportController::class, 'create']

)->name(

    'customers.import.create'

);

Route::get(

    '/customers/import/template',

    [CustomerImportController::class, 'downloadTemplate']

)->name(

    'customers.import.template'

);

Route::post(

    '/customers/import/preview',

    [CustomerImportController::class, 'preview']

)->name(

    'customers.import.preview'

);

Route::post(

    '/customers/import',

    [CustomerImportController::class, 'store']

)->name(

    'customers.import.store'

);

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

| Attach Existing Customer

|--------------------------------------------------------------------------

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

| Remove Customer From Branch

|--------------------------------------------------------------------------

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

| Customer Status

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

| Category

|--------------------------------------------------------------------------

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

| Category Products CSV

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

/*

|--------------------------------------------------------------------------

| Category Products Print

|--------------------------------------------------------------------------

*/

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

| Category Products PDF

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

| Category Status

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

/*

|--------------------------------------------------------------------------

| Brand

|--------------------------------------------------------------------------

*/

/*

|--------------------------------------------------------------------------

| Brand Status

|--------------------------------------------------------------------------

*/

Route::patch(

    '/brands/{brand}/toggle-status',

    [

        BrandController::class,

        'toggleStatus',

    ]

)->name(

    'brands.toggle-status'

);

/*

|--------------------------------------------------------------------------

| Brand Products

|--------------------------------------------------------------------------

*/

Route::get(

    '/brands/{brand}/products',

    [

        BrandController::class,

        'products',

    ]

)->name(

    'brands.products'

);

/*

|--------------------------------------------------------------------------

| Brand Products CSV

|--------------------------------------------------------------------------

*/

Route::get(

    '/brands/{brand}/products/export/csv',

    [

        BrandController::class,

        'exportProductsCsv',

    ]

)->name(

    'brands.products.export.csv'

);

/*

|--------------------------------------------------------------------------

| Brand Products PDF

|--------------------------------------------------------------------------

*/

Route::get(

    '/brands/{brand}/products/export/pdf',

    [

        BrandController::class,

        'exportProductsPdf',

    ]

)->name(

    'brands.products.export.pdf'

);

/*

|--------------------------------------------------------------------------

| Brand Products Print

|--------------------------------------------------------------------------

*/

Route::get(

    '/brands/{brand}/products/print',

    [

        BrandController::class,

        'printProducts',

    ]

)->name(

    'brands.products.print'

);

/*

|--------------------------------------------------------------------------

| Brand Resource

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

/*

|--------------------------------------------------------------------------

| Unit Status

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

/*

|--------------------------------------------------------------------------

| Unit Resource

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

/*

|--------------------------------------------------------------------------

| Product Import

|--------------------------------------------------------------------------

|

| Fixed import routes must stay before products/{product} routes.

|

*/

Route::get(

    '/products/import',

    [ProductImportController::class, 'create']

)->name(

    'products.import.create'

);

Route::get(

    '/products/import/template',

    [ProductImportController::class, 'downloadTemplate']

)->name(

    'products.import.template'

);

Route::post(

    '/products/import/preview',

    [ProductImportController::class, 'preview']

)->name(

    'products.import.preview'

);

Route::post(

    '/products/import',

    [ProductImportController::class, 'store']

)->name(

    'products.import.store'

);

Route::patch(

    '/products/{product}/toggle-status',

    [

        ProductController::class,

        'toggleStatus',

    ]

)->name(

    'products.toggle-status'

);

Route::resource(

    'products',

    ProductController::class

);

/*

|--------------------------------------------------------------------------

| Godown

|--------------------------------------------------------------------------

|

| Custom Godown inventory routes MUST stay before the resource route.

|

*/

/*

|--------------------------------------------------------------------------

| Godown Products

|--------------------------------------------------------------------------

*/

Route::get(

    '/godowns/{godown}/products',

    [

        GodownController::class,

        'products',

    ]

)->name(

    'godowns.products'

);

/*

|--------------------------------------------------------------------------

| Godown Products CSV

|--------------------------------------------------------------------------

*/

Route::get(

    '/godowns/{godown}/products/export/csv',

    [

        GodownController::class,

        'exportProductsCsv',

    ]

)->name(

    'godowns.products.export.csv'

);

/*

|--------------------------------------------------------------------------

| Godown Products PDF

|--------------------------------------------------------------------------

*/

Route::get(

    '/godowns/{godown}/products/export/pdf',

    [

        GodownController::class,

        'exportProductsPdf',

    ]

)->name(

    'godowns.products.export.pdf'

);

/*

|--------------------------------------------------------------------------

| Godown Products Print

|--------------------------------------------------------------------------

*/

Route::get(

    '/godowns/{godown}/products/print',

    [

        GodownController::class,

        'printProducts',

    ]

)->name(

    'godowns.products.print'

);

/*

|--------------------------------------------------------------------------

| Godown Status

|--------------------------------------------------------------------------

*/

Route::patch(

    '/godowns/{godown}/toggle-status',

    [

        GodownController::class,

        'toggleStatus',

    ]

)->name(

    'godowns.toggle-status'

);

/*

|--------------------------------------------------------------------------

| Godown Resource

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

| Do not trust company_id or store_id supplied from React.

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

| This helper remains available for modules that genuinely need to load

| branches for a company.

|

| Business write operations must never trust arbitrary company/store IDs

| from the browser.

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

/*

|--------------------------------------------------------------------------

| Mechanic

|--------------------------------------------------------------------------

|

| Mechanics are company masters shared through the mechanic_stores pivot.

| Custom routes must remain above the resource route.

|

*/

/*

|--------------------------------------------------------------------------

| Mechanic Import

|--------------------------------------------------------------------------

*/

Route::get(

    '/mechanics/import/template',

    [MechanicImportController::class, 'downloadTemplate']

)->name(

    'mechanics.import.template'

);

Route::get(

    '/mechanics/import',

    [MechanicImportController::class, 'create']

)->name(

    'mechanics.import.create'

);

Route::post(

    '/mechanics/import/preview',

    [MechanicImportController::class, 'preview']

)->name(

    'mechanics.import.preview'

);

Route::post(

    '/mechanics/import',

    [MechanicImportController::class, 'store']

)->name(

    'mechanics.import.store'

);

Route::patch(

    '/mechanics/{mechanic}/toggle-status',

    [

        MechanicController::class,

        'toggleStatus',

    ]

)

    ->whereNumber('mechanic')

    ->name(

        'mechanics.toggle-status'

    );

Route::delete(

    '/mechanics/{mechanic}/remove-from-branch',

    [

        MechanicController::class,

        'removeFromBranch',

    ]

)

    ->whereNumber('mechanic')

    ->name(

        'mechanics.remove-from-branch'

    );

Route::resource(

    'mechanics',

    MechanicController::class

)->where([

    'mechanic' => '[0-9]+',

]);
