<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseReportController;

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
