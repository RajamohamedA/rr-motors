<?php

use App\Http\Controllers\ProfileController;
//add dashboard controller
use App\Http\Controllers\DashboardController;
//add the catogary controller
use App\Http\Controllers\CategoryController;
//add company controller
use App\Http\Controllers\CompanyController;
//add store controller
use App\Http\Controllers\StoreController;
//add unit controller
use App\Http\Controllers\UnitController;
//add suppliers controller
use App\Http\Controllers\SupplierController;
//add brand controller
use App\Http\Controllers\BrandController;
//add product controller
use App\Http\Controllers\ProductController;
//add store model
use App\Models\Store;
//add purchase controller
use App\Http\Controllers\PurchaseController;

use Illuminate\Support\Facades\Route;

//landing page
Route::get('/', function () {
    return view('welcome');
});

//dashboard page
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

//add the routes for company
Route::middleware(['auth'])->group(function () {
    Route::resource('companies', CompanyController::class);
});

//add the routes for categories
Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class);
});

//add the routes for unit
Route::middleware(['auth'])->group(function () {
    Route::resource('units', UnitController::class);
});

//add the routes for store
Route::middleware(['auth'])->group(function () {
    Route::resource('stores', StoreController::class);
});

// add the routes for suppliers
Route::middleware(['auth'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
});

//add the routes for brand
Route::middleware(['auth'])->group(function () {
    Route::resource('brands', BrandController::class);
});

//add the routes for products
Route::middleware(['auth'])->group(function () {
    Route::resource('products', ProductController::class);
});

//add the routes for purchase
Route::middleware(['auth'])->group(function () {
    Route::resource('purchases', PurchaseController::class);
});

//add the route for get store
Route::get('/get-stores/{company}', function ($company) {

    return Store::where('company_id', $company)
            ->where('status',1)
            ->orderBy('store_name')
            ->get(['id','store_name']);

})->middleware('auth');


Route::get('/api/products/search', [ProductController::class, 'search'])
    ->name('products.search');

//add the route for purchases
Route::middleware(['auth'])->group(function () {
    Route::resource('purchases', PurchaseController::class);
}); 

//add the page for profiles
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
