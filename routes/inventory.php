<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLedgerController;


Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Stock Adjustment
    |--------------------------------------------------------------------------
    */
    Route::resource('stock-adjustments', StockAdjustmentController::class);

    Route::get(
        'stock-adjustments/product-stock/{product}/{company}/{store}',
        [StockAdjustmentController::class, 'productStock']
    )->name('stock-adjustments.product-stock');

    Route::get(
        'stock-adjustments/create/{store}/{product}',
        [StockAdjustmentController::class, 'create']
    )->name('stock-adjustments.create.product');

    Route::get(
        'ajax/product-stock',
        [StockAdjustmentController::class, 'getProductStock']
    )->name('ajax.product-stock');


    /*
    |--------------------------------------------------------------------------
    | Stock Ledger
    |--------------------------------------------------------------------------
    */
    Route::resource('stock-ledgers', StockLedgerController::class);
});