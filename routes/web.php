<?php

use Illuminate\Support\Facades\Route;

use App\Models\Store;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;

use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesReturnController;

use App\Http\Controllers\StockLedgerController;

use App\Http\Controllers\PurchaseReportController;

use App\Http\Controllers\QuotationController;
use App\Http\Controllers\CustomerPaymentController;


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
| Master Modules
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('companies', CompanyController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('units', UnitController::class);

    Route::resource('stores', StoreController::class);

    Route::resource('suppliers', SupplierController::class);

    Route::resource('customers', CustomerController::class);

    Route::resource('brands', BrandController::class);

    Route::resource('products', ProductController::class);
});

/*
|--------------------------------------------------------------------------
| Ajax
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/get-stores/{company}', function ($company) {

        return Store::where('company_id', $company)
            ->where('status', 1)
            ->orderBy('store_name')
            ->get([
                'id',
                'store_name'
            ]);
    });

    Route::get(
        '/api/products/search',
        [ProductController::class, 'search']
    )
        ->name('products.search');
});

/*
|--------------------------------------------------------------------------
| Purchase
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('purchases', PurchaseController::class);

    Route::get(
        '/purchases/{purchase}/print',
        [PurchaseController::class, 'print']
    )->name('purchases.print');
});

/*
|--------------------------------------------------------------------------
| Purchase Return
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('purchase-returns', PurchaseReturnController::class);

    Route::get(
        '/purchase-return/search',
        [PurchaseReturnController::class, 'search']
    )->name('purchase-return.search');

    Route::get(
        '/purchase-return/get-invoice/{id}',
        [PurchaseReturnController::class, 'getInvoice']
    )->name('purchase-return.invoice');

    Route::get(
        '/purchase-returns/{id}/print',
        [PurchaseReturnController::class, 'print']
    )->name('purchase-returns.print');
});

/*
|--------------------------------------------------------------------------
| Purchase Report
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Report screen
    Route::get(
        '/purchase-reports',
        [PurchaseReportController::class, 'index']
    )->name('purchase-reports.index');

    // AJAX search
    Route::post(
        '/purchase-reports/search',
        [PurchaseReportController::class, 'search']
    )->name('purchase-reports.search');

    // Print Report
    Route::get(
        '/purchase-reports/print',
        [PurchaseReportController::class, 'print']
    )->name('purchase-reports.print');

    // Excel Export
    Route::get(
        '/purchase-reports/excel',
        [PurchaseReportController::class, 'excel']
    )->name('purchase-reports.excel');

    // PDF Export
    Route::get(
        '/purchase-reports/pdf',
        [PurchaseReportController::class, 'pdf']
    )->name('purchase-reports.pdf');
});

/*
|--------------------------------------------------------------------------
| Sales
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('sales', SalesController::class);

    Route::get(
        '/sales/{sale}/print',
        [SalesController::class, 'print']
    )->name('sales.print');
});

/*
|--------------------------------------------------------------------------
| Sales Return
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

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
});

/*
|--------------------------------------------------------------------------
| Stock Ledger
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::resource('stock-ledgers', StockLedgerController::class);
});

/*
|--------------------------------------------------------------------------
| Purchase Reports
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/purchase-reports',
        [PurchaseReportController::class, 'index']
    )->name('purchase-reports.index');

    Route::post(
        '/purchase-reports/search',
        [PurchaseReportController::class, 'search']
    )->name('purchase-reports.search');

    Route::get(
        '/purchase-reports/print',
        [PurchaseReportController::class, 'print']
    )->name('purchase-reports.print');

    Route::get(
        '/purchase-reports/excel',
        [PurchaseReportController::class, 'excel']
    )->name('purchase-reports.excel');

    Route::get(
        '/purchase-reports/pdf',
        [
            PurchaseReportController::class,
            'pdf'
        ]
    )->name('purchase-reports.pdf');
});

/*
|--------------------------------------------------------------------------
| Quotations
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

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