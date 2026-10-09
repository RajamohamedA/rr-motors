<?php

use App\Http\Controllers\PurchaseReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Purchase Report
    |--------------------------------------------------------------------------
    |
    | Keep the fixed export paths above the dynamic purchase route.
    | Otherwise Laravel may interpret "print", "pdf", or "excel" as a
    | purchase model identifier.
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
});
