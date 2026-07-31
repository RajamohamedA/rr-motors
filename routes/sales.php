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

    Route::resource('sales', SalesController::class);

    Route::get(
        '/sales/{sale}/print',
        [SalesController::class, 'print']
    )->name('sales.print');

    /*
    |--------------------------------------------------------------------------
    | Sales Return
    |--------------------------------------------------------------------------
    */

    Route::resource('sales-returns', SalesReturnController::class);

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


    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */
    Route::resource('quotations', QuotationController::class);

    Route::get(
        'quotations/{quotation}/print',
        [QuotationController::class, 'print']
    )->name('quotations.print');

    Route::get(
        'quotations/{quotation}/convert',
        [QuotationController::class, 'convertToSales']
    )->name('quotations.convert');


});