<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\QuotationController;



Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/{sale}/print',
        [SalesController::class, 'print']
    )->name('sales.print');

    Route::post(
        'sales/check-stock',
        [SalesController::class, 'checkStock']
    )->name('sales.check-stock');

    // Route::post(
    //     '/stock-transfers/quick-transfer',
    //     [SalesController::class, 'quickTransfer']
    // )->name('stock-transfers.quick-transfer');



    /*
    |--------------------------------------------------------------------------
    | get customer and store details for particular company
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/sales/company/{company}/data',
        [SalesController::class, 'companyData']
    )->name('sales.company.data');

    Route::resource('sales', SalesController::class);


    /*
    |--------------------------------------------------------------------------
    | Sales Return
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales-return/search-invoice',
        [SalesReturnController::class, 'searchInvoice']
    )->name('sales-return.search');

    Route::get(
        '/sales-return/get-invoice/{id}',
        [SalesReturnController::class, 'getInvoice']
    )->name('sales-return.invoice');

    Route::get(
        '/sales-returns/{id}/print',
        [SalesReturnController::class, 'print']
    )->name('sales-returns.print');

    Route::resource(
        'sales-returns',
        SalesReturnController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    Route::get(
        'quotations/{quotation}/print',
        [QuotationController::class, 'print']
    )->name('quotations.print');

    Route::get(
        'quotations/{quotation}/convert',
        [QuotationController::class, 'convertToSales']
    )->name('quotations.convert');

    Route::resource(
        'quotations',
        QuotationController::class
    );
});
