<?php

use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\SupplierPaymentController;
use Illuminate\Support\Facades\Route;


Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Supplier Payments
    |--------------------------------------------------------------------------
    |
    | All custom routes must remain above the dynamic show route.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | AJAX Supplier Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments/suppliers/search',
        [
            SupplierPaymentController::class,
            'searchSuppliers',
        ]
    )->name(
        'supplier-payments.suppliers.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Supplier Outstanding Purchases
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments/suppliers/{supplier}/purchases',
        [
            SupplierPaymentController::class,
            'supplierPurchases',
        ]
    )
        ->whereNumber(
            'supplier'
        )
        ->name(
            'supplier-payments.suppliers.purchases'
        );


    /*
    |--------------------------------------------------------------------------
    | Cancel Supplier Payment
    |--------------------------------------------------------------------------
    |
    | Cancellation creates account and supplier-ledger reversal entries.
    |
    */

    Route::patch(
        '/supplier-payments/{supplierPayment}/cancel',
        [
            SupplierPaymentController::class,
            'cancel',
        ]
    )
        ->whereNumber(
            'supplierPayment'
        )
        ->name(
            'supplier-payments.cancel'
        );


    /*
    |--------------------------------------------------------------------------
    | Supplier Payment Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments/{supplierPayment}/print',
        [
            SupplierPaymentController::class,
            'print',
        ]
    )
        ->whereNumber(
            'supplierPayment'
        )
        ->name(
            'supplier-payments.print'
        );


    /*
    |--------------------------------------------------------------------------
    | Supplier Payment PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments/{supplierPayment}/pdf',
        [
            SupplierPaymentController::class,
            'pdf',
        ]
    )
        ->whereNumber(
            'supplierPayment'
        )
        ->name(
            'supplier-payments.pdf'
        );


    /*
    |--------------------------------------------------------------------------
    | Supplier Payment Index
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments',
        [
            SupplierPaymentController::class,
            'index',
        ]
    )->name(
        'supplier-payments.index'
    );


    /*
    |--------------------------------------------------------------------------
    | Create Supplier Payment
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-payments/create',
        [
            SupplierPaymentController::class,
            'create',
        ]
    )->name(
        'supplier-payments.create'
    );


    /*
    |--------------------------------------------------------------------------
    | Store Supplier Payment
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/supplier-payments',
        [
            SupplierPaymentController::class,
            'store',
        ]
    )->name(
        'supplier-payments.store'
    );


    /*
    |--------------------------------------------------------------------------
    | Show Supplier Payment
    |--------------------------------------------------------------------------
    |
    | Keep this route after all custom supplier-payment routes.
    |
    */

    Route::get(
        '/supplier-payments/{supplierPayment}',
        [
            SupplierPaymentController::class,
            'show',
        ]
    )
        ->whereNumber(
            'supplierPayment'
        )
        ->name(
            'supplier-payments.show'
        );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger
    |--------------------------------------------------------------------------
    |
    | Keep search, print, PDF and Excel routes before the main ledger route.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | AJAX Supplier Search
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-ledger/suppliers/search',
        [
            SupplierLedgerController::class,
            'searchSuppliers',
        ]
    )->name(
        'supplier-ledger.suppliers.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger Print
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-ledger/print',
        [
            SupplierLedgerController::class,
            'print',
        ]
    )->name(
        'supplier-ledger.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-ledger/pdf',
        [
            SupplierLedgerController::class,
            'pdf',
        ]
    )->name(
        'supplier-ledger.pdf'
    );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger Excel
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-ledger/excel',
        [
            SupplierLedgerController::class,
            'excel',
        ]
    )->name(
        'supplier-ledger.excel'
    );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger Register
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/supplier-ledger',
        [
            SupplierLedgerController::class,
            'index',
        ]
    )->name(
        'supplier-ledger.index'
    );
});
