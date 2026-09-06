<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PURCHASE - CUSTOM ROUTES
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Custom routes must be declared BEFORE Route::resource().
    |
    | Otherwise routes such as:
    |
    | /purchases/search
    |
    | may be interpreted as:
    |
    | /purchases/{purchase}
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Purchase Product Search
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
    | Purchase Stores
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility with forms that load stores dynamically.
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
    | Purchase Godowns
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
    | Purchase Suppliers
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
    | Purchase Resource
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | PURCHASE RETURN
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Search
    |--------------------------------------------------------------------------
    |
    | Search original purchases by:
    |
    | Purchase No
    | Supplier Invoice No
    | Supplier
    |
    */

    Route::get(
        '/purchase-returns/search',
        [
            PurchaseReturnController::class,
            'search',
        ]
    )->name(
        'purchase-returns.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Get Original Purchase / Invoice
    |--------------------------------------------------------------------------
    |
    | Loads the selected purchase together with:
    |
    | supplier
    | products
    | godown/shop
    | purchased quantity
    | previously returned quantity
    | available return quantity
    |
    */

    Route::get(
        '/purchase-returns/purchase/{id}',
        [
            PurchaseReturnController::class,
            'getInvoice',
        ]
    )->name(
        'purchase-returns.invoice'
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
    | Purchase Return PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/purchase-returns/{id}/pdf',
        [
            PurchaseReturnController::class,
            'pdf',
        ]
    )->name(
        'purchase-returns.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Resource
    |--------------------------------------------------------------------------
    |
    | GET       /purchase-returns
    | GET       /purchase-returns/create
    | POST      /purchase-returns
    | GET       /purchase-returns/{purchase_return}
    | GET       /purchase-returns/{purchase_return}/edit
    | PUT       /purchase-returns/{purchase_return}
    | PATCH     /purchase-returns/{purchase_return}
    | DELETE    /purchase-returns/{purchase_return}
    |
    */

    Route::resource(
        'purchase-returns',
        PurchaseReturnController::class
    );
});
