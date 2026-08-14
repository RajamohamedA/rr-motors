<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */

    Route::resource('purchases', PurchaseController::class);

    Route::get(
        '/purchases/{purchase}/print',
        [PurchaseController::class, 'print']
    )->name('purchases.print');

    Route::get(
        '/purchases/stores/{company}',
        [PurchaseController::class, 'getStores']
    )->name('purchases.stores');

    Route::get(
        '/purchases/godowns/{store}',
        [PurchaseController::class, 'getGodowns']
    )->name('purchases.godowns');

    Route::get(
        '/purchases/suppliers/{company}',
        [PurchaseController::class, 'getSuppliers']
    )->name('purchases.suppliers');

    /*
    |--------------------------------------------------------------------------
    | Purchase Return
    |--------------------------------------------------------------------------
    */

    Route::resource('purchase-returns', PurchaseReturnController::class);

    Route::get(
        '/purchase-return/search',
        [PurchaseReturnController::class, 'search']
    )->name('purchase-return.search');

    Route::get(
        '/purchase-return/get-invoice/{id}',
        [PurchaseReturnController::class, 'getInvoice']
    )->name('purchase-return.invoice');

    Route::get(
        '/purchase-returns/{id}/print',
        [PurchaseReturnController::class, 'print']
    )->name('purchase-returns.print');


});
