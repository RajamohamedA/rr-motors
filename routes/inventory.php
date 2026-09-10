<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CurrentStockController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLedgerController;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CURRENT STOCK
    |--------------------------------------------------------------------------
    |
    | Current stock routes are read-only. Their permissions are enforced by
    | CurrentStockController through Laravel 13 controller middleware.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Current Stock Register
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory/current-stock',
        [
            CurrentStockController::class,
            'index',
        ]
    )->name(
        'current-stock.index'
    );


    /*
    |--------------------------------------------------------------------------
    | Current Stock Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory/current-stock/print',
        [
            CurrentStockController::class,
            'print',
        ]
    )->name(
        'current-stock.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Current Stock PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory/current-stock/pdf',
        [
            CurrentStockController::class,
            'pdf',
        ]
    )->name(
        'current-stock.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Current Stock Excel
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/inventory/current-stock/excel',
        [
            CurrentStockController::class,
            'excel',
        ]
    )->name(
        'current-stock.excel'
    );


    /*
    |--------------------------------------------------------------------------
    | STOCK ADJUSTMENTS
    |--------------------------------------------------------------------------
    |
    | Custom Stock Adjustment routes must be declared before the resource
    | route when their path could otherwise be treated as a resource ID.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Product Stock Lookup
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-adjustments/product-stock/{product}/{company}/{store}',
        [
            StockAdjustmentController::class,
            'productStock',
        ]
    )->name(
        'stock-adjustments.product-stock'
    );


    /*
    |--------------------------------------------------------------------------
    | Create Adjustment for Product
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-adjustments/create/{store}/{product}',
        [
            StockAdjustmentController::class,
            'create',
        ]
    )->name(
        'stock-adjustments.create.product'
    );


    /*
    |--------------------------------------------------------------------------
    | AJAX Product Stock
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/ajax/product-stock',
        [
            StockAdjustmentController::class,
            'getProductStock',
        ]
    )->name(
        'ajax.product-stock'
    );


    /*
    |--------------------------------------------------------------------------
    | Stock Adjustment Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'stock-adjustments',
        StockAdjustmentController::class
    );


    /*
    |--------------------------------------------------------------------------
    | STOCK LEDGER
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'stock-ledgers',
        StockLedgerController::class
    );
});
