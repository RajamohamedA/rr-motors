<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Purchase - Custom Routes
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | These routes must be declared BEFORE Route::resource().
    |
    | Especially:
    |
    | /purchases/search
    |
    | Otherwise Laravel may interpret:
    |
    | search
    |
    | as:
    |
    | {purchase}
    |
    | and return 404.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchases/search',
        [
            PurchaseController::class,
            'search',
        ]
    )->name(
        'purchases.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchases/{purchase}/pdf',
        [
            PurchaseController::class,
            'pdf',
        ]
    )->name(
        'purchases.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchases/{purchase}/print',
        [
            PurchaseController::class,
            'print',
        ]
    )->name(
        'purchases.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Stores
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility.
    |
    */

    Route::get(
        '/purchases/stores/{company}',
        [
            PurchaseController::class,
            'getStores',
        ]
    )->name(
        'purchases.stores'
    );


    /*
    |--------------------------------------------------------------------------
    | Godowns
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchases/godowns/{store}',
        [
            PurchaseController::class,
            'getGodowns',
        ]
    )->name(
        'purchases.godowns'
    );


    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchases/suppliers/{company}',
        [
            PurchaseController::class,
            'getSuppliers',
        ]
    )->name(
        'purchases.suppliers'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Resource
    |--------------------------------------------------------------------------
    |
    | Generates:
    |
    | GET       /purchases
    | GET       /purchases/create
    | POST      /purchases
    | GET       /purchases/{purchase}
    | GET       /purchases/{purchase}/edit
    | PUT       /purchases/{purchase}
    | PATCH     /purchases/{purchase}
    | DELETE    /purchases/{purchase}
    |
    */

    Route::resource(
        'purchases',
        PurchaseController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return - Custom Routes
    |--------------------------------------------------------------------------
    |
    | Put custom routes BEFORE the resource for the same reason.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchase-return/search',
        [
            PurchaseReturnController::class,
            'search',
        ]
    )->name(
        'purchase-return.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Invoice
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchase-return/get-invoice/{id}',
        [
            PurchaseReturnController::class,
            'getInvoice',
        ]
    )->name(
        'purchase-return.invoice'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchase-returns/{id}/print',
        [
            PurchaseReturnController::class,
            'print',
        ]
    )->name(
        'purchase-returns.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'purchase-returns',
        PurchaseReturnController::class
    );
});
