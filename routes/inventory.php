<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CurrentStockController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\StockTransferController;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CURRENT STOCK
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

    Route::get(
        '/inventory/current-stock/print',
        [
            CurrentStockController::class,
            'print',
        ]
    )->name(
        'current-stock.print'
    );

    Route::get(
        '/inventory/current-stock/pdf',
        [
            CurrentStockController::class,
            'pdf',
        ]
    )->name(
        'current-stock.pdf'
    );

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
    */

    Route::get(
        '/stock-adjustments/products/search',
        [
            StockAdjustmentController::class,
            'searchProduct',
        ]
    )->name(
        'stock-adjustments.products.search'
    );

    Route::get(
        '/stock-adjustments/product-stock',
        [
            StockAdjustmentController::class,
            'getProductStock',
        ]
    )->name(
        'stock-adjustments.stock'
    );

    Route::get(
        '/stock-adjustments/product-stock/{product}/{company}/{store}',
        [
            StockAdjustmentController::class,
            'productStock',
        ]
    )->name(
        'stock-adjustments.product-stock'
    );

    Route::get(
        '/stock-adjustments/create/{store}/{product}',
        [
            StockAdjustmentController::class,
            'create',
        ]
    )->name(
        'stock-adjustments.create.product'
    );

    Route::get(
        '/ajax/product-stock',
        [
            StockAdjustmentController::class,
            'getProductStock',
        ]
    )->name(
        'ajax.product-stock'
    );

    Route::get(
        '/stock-adjustments/{stockAdjustment}/print',
        [
            StockAdjustmentController::class,
            'print',
        ]
    )->name(
        'stock-adjustments.print'
    );

    Route::get(
        '/stock-adjustments/{stockAdjustment}/pdf',
        [
            StockAdjustmentController::class,
            'pdf',
        ]
    )->name(
        'stock-adjustments.pdf'
    );

    Route::patch(
        '/stock-adjustments/{stockAdjustment}/cancel',
        [
            StockAdjustmentController::class,
            'cancel',
        ]
    )->name(
        'stock-adjustments.cancel'
    );

    Route::resource(
        'stock-adjustments',
        StockAdjustmentController::class
    )
        ->parameters([
            'stock-adjustments' => 'stockAdjustment',
        ])
        ->except([
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | STOCK TRANSFERS
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-transfers/products/search',
        [
            StockTransferController::class,
            'searchProduct',
        ]
    )->name(
        'stock-transfers.products.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Product Location Stock
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-transfers/product-stock',
        [
            StockTransferController::class,
            'getProductStock',
        ]
    )->name(
        'stock-transfers.product-stock'
    );


    /*
    |--------------------------------------------------------------------------
    | Quick Transfer
    |--------------------------------------------------------------------------
    |
    | This endpoint is used by the Sales billing screen when shop stock is
    | insufficient and available quantity must be moved from a godown.
    |
    */

    Route::post(
        '/stock-transfers/quick',
        [
            StockTransferController::class,
            'quickTransfer',
        ]
    )->name(
        'stock-transfers.quick'
    );


    /*
    |--------------------------------------------------------------------------
    | Stock Transfer Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-transfers/{stockTransfer}/print',
        [
            StockTransferController::class,
            'print',
        ]
    )->name(
        'stock-transfers.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Stock Transfer PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-transfers/{stockTransfer}/pdf',
        [
            StockTransferController::class,
            'pdf',
        ]
    )->name(
        'stock-transfers.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Cancel Stock Transfer
    |--------------------------------------------------------------------------
    |
    | Cancellation creates reversing ledger entries. The original transfer
    | and its items remain in the database for audit purposes.
    |
    */

    Route::patch(
        '/stock-transfers/{stockTransfer}/cancel',
        [
            StockTransferController::class,
            'cancel',
        ]
    )->name(
        'stock-transfers.cancel'
    );


    /*
    |--------------------------------------------------------------------------
    | Stock Transfer Resource
    |--------------------------------------------------------------------------
    |
    | Transfers cannot be edited or permanently deleted after their stock
    | ledger entries have been created.
    |
    */

    Route::resource(
        'stock-transfers',
        StockTransferController::class
    )
        ->parameters([
            'stock-transfers' => 'stockTransfer',
        ])
        ->only([
            'index',
            'create',
            'store',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | STOCK LEDGER
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/stock-ledgers/search',
        [
            StockLedgerController::class,
            'search',
        ]
    )->name(
        'stock-ledgers.search'
    );

    Route::get(
        '/stock-ledgers/print',
        [
            StockLedgerController::class,
            'printReport',
        ]
    )->name(
        'stock-ledgers.print'
    );

    Route::get(
        '/stock-ledgers/pdf',
        [
            StockLedgerController::class,
            'pdfReport',
        ]
    )->name(
        'stock-ledgers.pdf'
    );

    Route::get(
        '/stock-ledgers/excel',
        [
            StockLedgerController::class,
            'csvReport',
        ]
    )->name(
        'stock-ledgers.excel'
    );

    Route::resource(
        'stock-ledgers',
        StockLedgerController::class
    )
        ->parameters([
            'stock-ledgers' => 'stockLedger',
        ])
        ->only([
            'index',
            'show',
        ]);
});
