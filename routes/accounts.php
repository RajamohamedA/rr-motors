<?php

use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerLedgerController;
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

    /*
    |--------------------------------------------------------------------------
    | Customer Payment AJAX Routes
    |--------------------------------------------------------------------------
    |
    | These routes must stay above /customer-payments/{customerPayment}.
    |
    */

    Route::get(
        '/customer-payments/customers/search',
        [
            CustomerPaymentController::class,
            'searchCustomers',
        ]
    )->name(
        'customer-payments.customers.search'
    );

    Route::get(
        '/customer-payments/customers/{customer}/sales',
        [
            CustomerPaymentController::class,
            'customerSales',
        ]
    )
        ->whereNumber('customer')
        ->name(
            'customer-payments.customers.sales'
        );

    /*
    |--------------------------------------------------------------------------
    | Compatible Invoice Search Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/customer-payments/invoices/search',
        [
            CustomerPaymentController::class,
            'searchInvoice',
        ]
    )->name(
        'customer-payments.invoices.search'
    );

    Route::get(
        '/customer-payments/invoices/{sale}',
        [
            CustomerPaymentController::class,
            'invoiceDetails',
        ]
    )
        ->whereNumber('sale')
        ->name(
            'customer-payments.invoices.show'
        );

    /*
    |--------------------------------------------------------------------------
    | Cancel Customer Payment
    |--------------------------------------------------------------------------
    |
    | Posted receipts are never edited or deleted. Cancellation creates
    | reversing account and customer-ledger entries.
    |
    */

    Route::patch(
        '/customer-payments/{customerPayment}/cancel',
        [
            CustomerPaymentController::class,
            'cancel',
        ]
    )
        ->whereNumber('customerPayment')
        ->name(
            'customer-payments.cancel'
        );

    /*
    |--------------------------------------------------------------------------
    | Print and PDF
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/customer-payments/{customerPayment}/print',
        [
            CustomerPaymentController::class,
            'print',
        ]
    )
        ->whereNumber('customerPayment')
        ->name(
            'customer-payments.print'
        );

    Route::get(
        '/customer-payments/{customerPayment}/pdf',
        [
            CustomerPaymentController::class,
            'pdf',
        ]
    )
        ->whereNumber('customerPayment')
        ->name(
            'customer-payments.pdf'
        );

    /*
    |--------------------------------------------------------------------------
    | Main Customer Payment Routes
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/customer-payments',
        [
            CustomerPaymentController::class,
            'index',
        ]
    )->name(
        'customer-payments.index'
    );

    Route::get(
        '/customer-payments/create',
        [
            CustomerPaymentController::class,
            'create',
        ]
    )->name(
        'customer-payments.create'
    );

    Route::post(
        '/customer-payments',
        [
            CustomerPaymentController::class,
            'store',
        ]
    )->name(
        'customer-payments.store'
    );

    Route::get(
        '/customer-payments/{customerPayment}',
        [
            CustomerPaymentController::class,
            'show',
        ]
    )
        ->whereNumber('customerPayment')
        ->name(
            'customer-payments.show'
        );

        /*
        |--------------------------------------------------------------------------
        | Customer Ledger
        |--------------------------------------------------------------------------
        |
        | Customer Ledger is a read-only accounting register. Entries are created
        | by sales, sales returns, customer receipts and their reversing actions.
        | They must never be created, edited or deleted directly through routes.
        |
        */

    /*
        |--------------------------------------------------------------------------
        | AJAX Customer Search
        |--------------------------------------------------------------------------
        */

    Route::get(
            '/customer-ledger/customers/search',
            [
                CustomerLedgerController::class,
                'searchCustomers',
            ]
        )->name(
            'customer-ledger.customers.search'
        );
    
        /*
        |--------------------------------------------------------------------------
        | Print Report
        |--------------------------------------------------------------------------
        */
    
        Route::get(
            '/customer-ledger/print',
            [
                CustomerLedgerController::class,
                'print',
            ]
        )->name(
            'customer-ledger.print'
        );
    
        /*
        |--------------------------------------------------------------------------
        | PDF Report
        |--------------------------------------------------------------------------
        */
    
        Route::get(
            '/customer-ledger/pdf',
            [
                CustomerLedgerController::class,
                'pdf',
            ]
        )->name(
            'customer-ledger.pdf'
        );
    
        /*
        |--------------------------------------------------------------------------
        | Excel / CSV Report
        |--------------------------------------------------------------------------
        */
    
        Route::get(
            '/customer-ledger/excel',
            [
                CustomerLedgerController::class,
                'excel',
            ]
        )->name(
            'customer-ledger.excel'
        );
    
        /*
        |--------------------------------------------------------------------------
        | Customer Ledger Register
        |--------------------------------------------------------------------------
        */
    
        Route::get(
            '/customer-ledger',
            [
                CustomerLedgerController::class,
                'index',
            ]
        )->name(
            'customer-ledger.index'
        );


});
