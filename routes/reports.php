<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\PurchaseReportController;


Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Sales Report
    |--------------------------------------------------------------------------
    */
    Route::get('/reports/sales', [SalesReportController::class, 'index'])
        ->name('reports.sales');

    Route::get('/reports/sales/print', [SalesReportController::class, 'print'])
        ->name('reports.sales.print');

    Route::get('/reports/sales/pdf', [SalesReportController::class, 'pdf'])
        ->name('reports.sales.pdf');

    Route::get(
        '/reports/sales/csv',
        [SalesReportController::class, 'csv']
    )->name('reports.sales.csv');

    /*
    |--------------------------------------------------------------------------
    | stock Report
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/inventory/current-stock',
        [StockReportController::class, 'currentStock']
    )
        ->name('inventory.current-stock');

    Route::get(
        '/inventory/current-stock/print',
        [StockReportController::class, 'print']
    )
        ->name('inventory.current-stock.print');

    Route::get(
        '/inventory/current-stock/pdf',
        [StockReportController::class, 'pdf']
    )
        ->name('inventory.current-stock.pdf');

    Route::get(
        '/inventory/current-stock/csv',
        [StockReportController::class, 'csv']
    )
        ->name('inventory.current-stock.csv');


    /*
    |--------------------------------------------------------------------------
    | Purchase Report
    |--------------------------------------------------------------------------
    */

    // Report screen
    Route::get(
        '/purchase-reports',
        [PurchaseReportController::class, 'index']
    )->name('purchase-reports.index');

    // AJAX search
    Route::post(
        '/purchase-reports/search',
        [PurchaseReportController::class, 'search']
    )->name('purchase-reports.search');

    // Print Report
    Route::get(
        '/purchase-reports/print',
        [PurchaseReportController::class, 'print']
    )->name('purchase-reports.print');

    // Excel Export
    Route::get(
        '/purchase-reports/excel',
        [PurchaseReportController::class, 'excel']
    )->name('purchase-reports.excel');

    // PDF Export
    Route::get(
        '/purchase-reports/pdf',
        [PurchaseReportController::class, 'pdf']
    )->name('purchase-reports.pdf');

});