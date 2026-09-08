<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SALES
    |--------------------------------------------------------------------------
    |
    | Custom Sales routes must be declared before the Sales resource route.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/search',
        [
            SalesController::class,
            'search',
        ]
    )->name(
        'sales.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Customer Search
    |--------------------------------------------------------------------------
    |
    | AJAX customer autocomplete for Sales Create and Edit.
    |
    */

    Route::get(
        '/sales/customers/search',
        [
            SalesController::class,
            'searchCustomers',
        ]
    )->name(
        'sales.customers.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Quick Customer Add
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/sales/quick-customer',
        [
            SalesController::class,
            'quickCustomer',
        ]
    )->name(
        'sales.quick-customer'
    );


    /*
    |--------------------------------------------------------------------------
    | Quick Stock Transfer
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/sales/quick-transfer',
        [
            SalesController::class,
            'quickTransfer',
        ]
    )->name(
        'sales.quick-transfer'
    );


    /*
    |--------------------------------------------------------------------------
    | Quick Mechanic Add
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/sales/quick-mechanic',
        [
            SalesController::class,
            'quickMechanic',
        ]
    )->name(
        'sales.quick-mechanic'
    );


    /*
    |--------------------------------------------------------------------------
    | Check Stock
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/sales/check-stock',
        [
            SalesController::class,
            'checkStock',
        ]
    )->name(
        'sales.check-stock'
    );


    /*
    |--------------------------------------------------------------------------
    | Company Data
    |--------------------------------------------------------------------------
    |
    | Retained for compatibility with the older Sales screen.
    |
    */

    Route::get(
        '/sales/company/{company}/data',
        [
            SalesController::class,
            'companyData',
        ]
    )->name(
        'sales.company.data'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/{sale}/print',
        [
            SalesController::class,
            'print',
        ]
    )->name(
        'sales.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales/{sale}/pdf',
        [
            SalesController::class,
            'pdf',
        ]
    )->name(
        'sales.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'sales',
        SalesController::class
    );


    /*
    |--------------------------------------------------------------------------
    | SALES RETURNS
    |--------------------------------------------------------------------------
    |
    | Custom Sales Return routes must be declared before its resource route.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Search Sales Invoice
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales-returns/search',
        [
            SalesReturnController::class,
            'searchInvoice',
        ]
    )->name(
        'sales-returns.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Get Sales Invoice
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales-returns/invoice/{sale}',
        [
            SalesReturnController::class,
            'getInvoice',
        ]
    )->name(
        'sales-returns.invoice'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Return Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales-returns/{salesReturn}/print',
        [
            SalesReturnController::class,
            'print',
        ]
    )->name(
        'sales-returns.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Return PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/sales-returns/{salesReturn}/pdf',
        [
            SalesReturnController::class,
            'pdf',
        ]
    )->name(
        'sales-returns.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Cancel Sales Return
    |--------------------------------------------------------------------------
    |
    | Cancellation reverses the stock added by the return. The record is kept
    | for audit history and must not be permanently deleted.
    |
    */

    Route::patch(
        '/sales-returns/{salesReturn}/cancel',
        [
            SalesReturnController::class,
            'cancel',
        ]
    )->name(
        'sales-returns.cancel'
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Return Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'sales-returns',
        SalesReturnController::class
    )
        ->parameters([
            'sales-returns' => 'salesReturn',
        ])
        ->except([
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | QUOTATIONS
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Quotation Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/quotations/{quotation}/print',
        [
            QuotationController::class,
            'print',
        ]
    )->name(
        'quotations.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Convert Quotation to Sale
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/quotations/{quotation}/convert',
        [
            QuotationController::class,
            'convertToSales',
        ]
    )->name(
        'quotations.convert'
    );


    /*
    |--------------------------------------------------------------------------
    | Quotation Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'quotations',
        QuotationController::class
    );
});
