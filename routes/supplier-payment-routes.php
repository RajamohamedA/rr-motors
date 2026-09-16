<?php

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
    | Custom endpoints must remain above the resource route. Otherwise the
    | resource show route can interpret "suppliers" as a supplier payment ID.
    |
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

    Route::get(
        '/supplier-payments/suppliers/{supplier}/purchases',
        [
            SupplierPaymentController::class,
            'supplierPurchases',
        ]
    )->whereNumber(
        'supplier'
    )->name(
        'supplier-payments.suppliers.purchases'
    );

    Route::patch(
        '/supplier-payments/{supplierPayment}/cancel',
        [
            SupplierPaymentController::class,
            'cancel',
        ]
    )->whereNumber(
        'supplierPayment'
    )->name(
        'supplier-payments.cancel'
    );

    Route::get(
        '/supplier-payments/{supplierPayment}/print',
        [SupplierPaymentController::class, 'print']
    )->whereNumber('supplierPayment')
        ->name('supplier-payments.print');

    Route::get(
        '/supplier-payments/{supplierPayment}/pdf',
        [SupplierPaymentController::class, 'pdf']
    )->whereNumber('supplierPayment')
        ->name('supplier-payments.pdf');

    Route::get(
        '/supplier-payments',
        [
            SupplierPaymentController::class,
            'index',
        ]
    )->name(
        'supplier-payments.index'
    );

    Route::get(
        '/supplier-payments/create',
        [
            SupplierPaymentController::class,
            'create',
        ]
    )->name(
        'supplier-payments.create'
    );

    Route::post(
        '/supplier-payments',
        [
            SupplierPaymentController::class,
            'store',
        ]
    )->name(
        'supplier-payments.store'
    );

    Route::get(
        '/supplier-payments/{supplierPayment}',
        [
            SupplierPaymentController::class,
            'show',
        ]
    )->whereNumber(
        'supplierPayment'
    )->name(
        'supplier-payments.show'
    );
});
