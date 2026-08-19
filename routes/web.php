<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;



use App\Http\Controllers\CustomerPaymentController;


use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});


/*
|--------------------------------------------------------------------------
| customer-payments
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('customer-payments', CustomerPaymentController::class);

    // Search Sales Invoice
    Route::get(
        'customer-payments/search/invoice',
        [CustomerPaymentController::class, 'searchInvoice']
    )->name('customer-payments.search');

    // Get Invoice Details
    Route::get(
        'customer-payments/invoice/{id}',
        [CustomerPaymentController::class, 'invoiceDetails']
    )->name('customer-payments.invoice');

    // Print Receipt
    Route::get(
        'customer-payments/{customer_payment}/print',
        [CustomerPaymentController::class, 'print']
    )->name('customer-payments.print');

    Route::get(
        'customer-ledger',
        [CustomerPaymentController::class, 'customerLedger']
    )->name('customer-ledger');
});

/*
|--------------------------------------------------------------------------
| Future Reports
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Sales Report
    // Route::resource('sales-reports', SalesReportController::class);

    // Purchase Return Report
    // Route::resource('purchase-return-reports', PurchaseReturnReportController::class);

    // Sales Return Report
    // Route::resource('sales-return-reports', SalesReturnReportController::class);

    // Stock Report
    // Route::resource('stock-reports', StockReportController::class);

    // Supplier Ledger
    // Route::resource('supplier-ledgers', SupplierLedgerController::class);

    // Customer Ledger
    // Route::resource('customer-ledgers', CustomerLedgerController::class);

    // GST Report
    // Route::resource('gst-reports', GstReportController::class);

});

Route::resource('roles', RoleController::class);

Route::resource('users', UserController::class);

Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])
    ->name('users.deactivate');

Route::post('/users/{user}/activate', [UserController::class, 'activate'])
    ->name('users.activate');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
require __DIR__ . '/masters.php';
require __DIR__ . '/purchase.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/inventory.php';
require __DIR__ . '/reports.php';
require __DIR__ . '/stocktransfer.php';