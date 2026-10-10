<?php

use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\SalesReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Purchase Report
    |--------------------------------------------------------------------------
    |
    | Fixed export endpoints must remain above the dynamic purchase route.
    |
    */

    Route::get(
        '/purchase-reports',
        [PurchaseReportController::class, 'index']
    )->name('purchase-reports.index');

    Route::get(
        '/purchase-reports/print',
        [PurchaseReportController::class, 'print']
    )->name('purchase-reports.print');

    Route::get(
        '/purchase-reports/pdf',
        [PurchaseReportController::class, 'pdf']
    )->name('purchase-reports.pdf');

    Route::get(
        '/purchase-reports/excel',
        [PurchaseReportController::class, 'excel']
    )->name('purchase-reports.excel');

    Route::get(
        '/purchase-reports/{purchase}',
        [PurchaseReportController::class, 'show']
    )
        ->whereNumber('purchase')
        ->name('purchase-reports.show');

    /*
    |--------------------------------------------------------------------------
    | Sales Report
    |--------------------------------------------------------------------------
    |
    | Fixed export endpoints must remain above the dynamic sale route.
    |
    */

    Route::get(
        '/sales-reports',
        [SalesReportController::class, 'index']
    )->name('sales-reports.index');

    Route::get(
        '/sales-reports/print',
        [SalesReportController::class, 'print']
    )->name('sales-reports.print');

    Route::get(
        '/sales-reports/pdf',
        [SalesReportController::class, 'pdf']
    )->name('sales-reports.pdf');

    Route::get(
        '/sales-reports/excel',
        [SalesReportController::class, 'excel']
    )->name('sales-reports.excel');

    Route::get(
        '/sales-reports/{sale}',
        [SalesReportController::class, 'show']
    )
        ->whereNumber('sale')
        ->name('sales-reports.show');
});
