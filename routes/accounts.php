<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountDashboardController;
use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\MechanicPaymentController;
use App\Http\Controllers\MechanicLedgerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OutstandingReportController;
use App\Http\Controllers\CashBankBookController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Accounts Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get(
        '/accounts/dashboard',
        [AccountDashboardController::class, 'index']
    )->name('accounts.dashboard');
    /*
    |--------------------------------------------------------------------------
    | Customer / Supplier Outstanding Report
    |--------------------------------------------------------------------------
    |
    | Export routes must remain above the report index and account bindings.
    |
    */
    Route::get(
        '/accounts/outstanding/print',
        [OutstandingReportController::class, 'print']
    )->name('accounts.outstanding.print');
    Route::get(
        '/accounts/outstanding/pdf',
        [OutstandingReportController::class, 'pdf']
    )->name('accounts.outstanding.pdf');
    Route::get(
        '/accounts/outstanding/excel',
        [OutstandingReportController::class, 'excel']
    )->name('accounts.outstanding.excel');
    Route::get(
        '/accounts/outstanding',
        [OutstandingReportController::class, 'index']
    )->name('accounts.outstanding.index');

    /*
    |--------------------------------------------------------------------------
    | Cash / Bank Book
    |--------------------------------------------------------------------------
    |
    | Export routes remain above the main book route and account bindings.
    |
    */
    Route::get(
        '/accounts/cash-bank-book/print',
        [CashBankBookController::class, 'print']
    )->name('accounts.cash-bank-book.print');

    Route::get(
        '/accounts/cash-bank-book/pdf',
        [CashBankBookController::class, 'pdf']
    )->name('accounts.cash-bank-book.pdf');

    Route::get(
        '/accounts/cash-bank-book/excel',
        [CashBankBookController::class, 'excel']
    )->name('accounts.cash-bank-book.excel');

    Route::get(
        '/accounts/cash-bank-book',
        [CashBankBookController::class, 'index']
    )->name('accounts.cash-bank-book.index');

    /*
    |--------------------------------------------------------------------------
    | Cash / Bank Account Master
    |--------------------------------------------------------------------------
    |
    | Fixed routes must remain above /accounts/{account}.
    |
    */
    Route::get(
        '/accounts',
        [AccountController::class, 'index']
    )->name('accounts.index');
    Route::get(
        '/accounts/create',
        [AccountController::class, 'create']
    )->name('accounts.create');
    Route::post(
        '/accounts',
        [AccountController::class, 'store']
    )->name('accounts.store');
    Route::get(
        '/accounts/{account}',
        [AccountController::class, 'show']
    )
        ->whereNumber('account')
        ->name('accounts.show');
    Route::get(
        '/accounts/{account}/edit',
        [AccountController::class, 'edit']
    )
        ->whereNumber('account')
        ->name('accounts.edit');
    Route::match(
        ['put', 'patch'],
        '/accounts/{account}',
        [AccountController::class, 'update']
    )
        ->whereNumber('account')
        ->name('accounts.update');
    Route::patch(
        '/accounts/{account}/toggle-status',
        [AccountController::class, 'toggleStatus']
    )
        ->whereNumber('account')
        ->name('accounts.toggle-status');
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
    /*
    |--------------------------------------------------------------------------
    | Mechanic Payments
    |--------------------------------------------------------------------------
    |
    | Keep search and commission lookup routes above the dynamic show route.
    |
    */
    Route::get(
        '/mechanic-payments/mechanics/search',
        [
            MechanicPaymentController::class,
            'searchMechanics',
        ]
    )->name(
        'mechanic-payments.mechanics.search'
    );
    Route::get(
        '/mechanic-payments/mechanics/{mechanic}/commissions',
        [
            MechanicPaymentController::class,
            'mechanicCommissions',
        ]
    )
        ->whereNumber('mechanic')
        ->name(
            'mechanic-payments.mechanics.commissions'
        );
    Route::patch(
        '/mechanic-payments/{mechanicPayment}/cancel',
        [
            MechanicPaymentController::class,
            'cancel',
        ]
    )
        ->whereNumber('mechanicPayment')
        ->name(
            'mechanic-payments.cancel'
        );
    Route::get(
        '/mechanic-payments/{mechanicPayment}/print',
        [
            MechanicPaymentController::class,
            'print',
        ]
    )
        ->whereNumber('mechanicPayment')
        ->name(
            'mechanic-payments.print'
        );
    Route::get(
        '/mechanic-payments/{mechanicPayment}/pdf',
        [
            MechanicPaymentController::class,
            'pdf',
        ]
    )
        ->whereNumber('mechanicPayment')
        ->name(
            'mechanic-payments.pdf'
        );
    Route::get(
        '/mechanic-payments',
        [
            MechanicPaymentController::class,
            'index',
        ]
    )->name(
        'mechanic-payments.index'
    );
    Route::get(
        '/mechanic-payments/create',
        [
            MechanicPaymentController::class,
            'create',
        ]
    )->name(
        'mechanic-payments.create'
    );
    Route::post(
        '/mechanic-payments',
        [
            MechanicPaymentController::class,
            'store',
        ]
    )->name(
        'mechanic-payments.store'
    );
    Route::get(
        '/mechanic-payments/{mechanicPayment}',
        [
            MechanicPaymentController::class,
            'show',
        ]
    )
        ->whereNumber('mechanicPayment')
        ->name(
            'mechanic-payments.show'
        );
    /*
    |--------------------------------------------------------------------------
    | Mechanic Ledger
    |--------------------------------------------------------------------------
    |
    | The mechanic ledger is read-only. Commission, payment, cancellation and
    | reversal services create its entries; routes only display/export them.
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Mechanic Accounts Compatibility Route
    |--------------------------------------------------------------------------
    |
    | Retains compatibility with sidebar links that use /mechanic-accounts.
    |
    */
    Route::get(
        '/mechanic-accounts',
        [
            MechanicLedgerController::class,
            'index',
        ]
    )->name(
        'mechanic-accounts.index'
    );
    Route::get(
        '/mechanic-ledger/mechanics/search',
        [
            MechanicLedgerController::class,
            'searchMechanics',
        ]
    )->name(
        'mechanic-ledger.mechanics.search'
    );
    Route::get(
        '/mechanic-ledger/print',
        [
            MechanicLedgerController::class,
            'print',
        ]
    )->name(
        'mechanic-ledger.print'
    );
    Route::get(
        '/mechanic-ledger/pdf',
        [
            MechanicLedgerController::class,
            'pdf',
        ]
    )->name(
        'mechanic-ledger.pdf'
    );
    Route::get(
        '/mechanic-ledger/excel',
        [
            MechanicLedgerController::class,
            'excel',
        ]
    )->name(
        'mechanic-ledger.excel'
    );
    Route::get(
        '/mechanic-ledger',
        [
            MechanicLedgerController::class,
            'index',
        ]
    )->name(
        'mechanic-ledger.index'
    );
    /*
    |--------------------------------------------------------------------------
    | Expenses
    |--------------------------------------------------------------------------
    |
    | Fixed print, PDF and cancel routes must remain above the show route.
    |
    */
    Route::get(
        '/expenses',
        [ExpenseController::class, 'index']
    )->name('expenses.index');
    Route::get(
        '/expenses/create',
        [ExpenseController::class, 'create']
    )->name('expenses.create');
    Route::post(
        '/expenses',
        [ExpenseController::class, 'store']
    )->name('expenses.store');
    Route::patch(
        '/expenses/{expense}/cancel',
        [ExpenseController::class, 'cancel']
    )
        ->whereNumber('expense')
        ->name('expenses.cancel');
    Route::get(
        '/expenses/{expense}/print',
        [ExpenseController::class, 'print']
    )
        ->whereNumber('expense')
        ->name('expenses.print');
    Route::get(
        '/expenses/{expense}/pdf',
        [ExpenseController::class, 'pdf']
    )
        ->whereNumber('expense')
        ->name('expenses.pdf');
    Route::get(
        '/expenses/{expense}',
        [ExpenseController::class, 'show']
    )
        ->whereNumber('expense')
        ->name('expenses.show');
    /*
    |--------------------------------------------------------------------------
    | Receipt and Payment Vouchers
    |--------------------------------------------------------------------------
    |
    | Keep fixed URLs above /vouchers/{voucher}. Otherwise Laravel may treat
    | "create" or "parties" as a voucher route-model binding value.
    |
    */
    Route::get(
        '/vouchers',
        [VoucherController::class, 'index']
    )->name('vouchers.index');
    Route::get(
        '/vouchers/create',
        [VoucherController::class, 'create']
    )->name('vouchers.create');
    Route::post(
        '/vouchers',
        [VoucherController::class, 'store']
    )->name('vouchers.store');
    Route::get(
        '/vouchers/parties/search',
        [VoucherController::class, 'searchParties']
    )->name('vouchers.parties.search');
    Route::patch(
        '/vouchers/{voucher}/cancel',
        [VoucherController::class, 'cancel']
    )
        ->whereNumber('voucher')
        ->name('vouchers.cancel');
    Route::get(
        '/vouchers/{voucher}/print',
        [VoucherController::class, 'print']
    )
        ->whereNumber('voucher')
        ->name('vouchers.print');
    Route::get(
        '/vouchers/{voucher}/pdf',
        [VoucherController::class, 'pdf']
    )
        ->whereNumber('voucher')
        ->name('vouchers.pdf');
    Route::get(
        '/vouchers/{voucher}',
        [VoucherController::class, 'show']
    )
        ->whereNumber('voucher')
        ->name('vouchers.show');
});
