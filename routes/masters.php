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
use App\Models\Store;
use App\Http\Controllers\GodownController;

/*
|--------------------------------------------------------------------------
| Master Modules
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::resource('companies', CompanyController::class);
    Route::resource('stores', StoreController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('units', UnitController::class);
    Route::resource('products', ProductController::class);
    Route::resource('godowns', GodownController::class);
});


/*
|--------------------------------------------------------------------------
| Ajax
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/api/products/search',
        [ProductController::class, 'search']
    )
        ->name('products.search');


    Route::get('/get-stores/{company}', function ($company) {

        return Store::where('company_id', $company)
            ->where('status', 1)
            ->orderBy('store_name')
            ->get([
                'id',
                'store_name'
            ]);
    });

});
