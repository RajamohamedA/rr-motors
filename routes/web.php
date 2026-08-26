<?php

use Illuminate\Support\Facades\Route;




use App\Http\Controllers\CustomerPaymentController;


use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

use Inertia\Inertia;

use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;

use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;

use App\Http\Controllers\SuperAdmin\BranchController as SuperAdminBranchController;

use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;

use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminRoleController;

use App\Http\Controllers\SuperAdmin\PermissionController as SuperAdminPermissionController;

use App\Http\Controllers\SuperAdmin\ActivityLogController;

use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;

use App\Http\Controllers\ProfileController;

use App\Http\Controllers\SuperAdmin\NotificationController;




use App\Http\Controllers\BranchContextController;

use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;

use App\Http\Controllers\Auth\LoginRedirectController;

use App\Http\Controllers\BranchSelectionController;

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

Route::get('/react-test', function () {
    return Inertia::render('Test');
})->middleware('auth');


Route::get('/', function () {
    return view('welcome');
});



/*
|--------------------------------------------------------------------------
| company
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'super.admin',
])->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    Route::get(
            '/companies',
            [SuperAdminCompanyController::class, 'index']
        )->name('companies.index');

        Route::get(
            '/companies/create',
            [SuperAdminCompanyController::class, 'create']
        )->name('companies.create');

        Route::post(
            '/companies',
            [SuperAdminCompanyController::class, 'store']
        )->name('companies.store');

        Route::get(
            '/companies/{company}',
            [SuperAdminCompanyController::class, 'show']
        )->name('companies.show');

        Route::get(
            '/companies/{company}/edit',
            [SuperAdminCompanyController::class, 'edit']
        )->name('companies.edit');

        Route::put(
            '/companies/{company}',
            [SuperAdminCompanyController::class, 'update']
        )->name('companies.update');

        Route::post(
            '/companies/{company}/activate',
            [SuperAdminCompanyController::class, 'activate']
        )->name('companies.activate');

        Route::post(
            '/companies/{company}/deactivate',
            [SuperAdminCompanyController::class, 'deactivate']
        )->name('companies.deactivate');

        Route::delete(
            '/companies/{company}',
            [SuperAdminCompanyController::class, 'destroy']
        )->name('companies.destroy');

        Route::post(
            '/companies/{id}/restore',
            [SuperAdminCompanyController::class, 'restore']
        )->name('companies.restore');


        /*
        |--------------------------------------------------------------------------
        | branches route under companies
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/companies/{company}/branches/create',
            [SuperAdminBranchController::class, 'create']
        )->name('companies.branches.create');

        Route::post(
            '/companies/{company}/branches',
            [SuperAdminBranchController::class, 'store']
        )->name('companies.branches.store');

        Route::get(
            '/companies/{company}/branches/{store}/edit',
            [SuperAdminBranchController::class, 'edit']
        )->name('companies.branches.edit');

        Route::put(
            '/companies/{company}/branches/{store}',
            [SuperAdminBranchController::class, 'update']
        )->name('companies.branches.update');

        Route::post(
            '/companies/{company}/branches/{store}/activate',
            [SuperAdminBranchController::class, 'activate']
        )->name('companies.branches.activate');

        Route::post(
            '/companies/{company}/branches/{store}/deactivate',
            [SuperAdminBranchController::class, 'deactivate']
        )->name('companies.branches.deactivate');


        /*
        |--------------------------------------------------------------------------
        | branches for all company
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/branches',
            [SuperAdminBranchController::class, 'index']
        )->name('branches.index');


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [SuperAdminUserController::class, 'index']
        )->name('users.index');

        Route::get(
            '/users/create',
            [SuperAdminUserController::class, 'create']
        )->name('users.create');

        Route::post(
            '/users',
            [SuperAdminUserController::class, 'store']
        )->name('users.store');

        Route::get(
            '/users/{user}/edit',
            [SuperAdminUserController::class, 'edit']
        )->name('users.edit');

        Route::put(
            '/users/{user}',
            [SuperAdminUserController::class, 'update']
        )->name('users.update');

        Route::post(
            '/users/{user}/activate',
            [SuperAdminUserController::class, 'activate']
        )->name('users.activate');

        Route::post(
            '/users/{user}/deactivate',
            [SuperAdminUserController::class, 'deactivate']
        )->name('users.deactivate');

        Route::get(
            '/users/{user}',
            [SuperAdminUserController::class, 'show']
        )->name('users.show');

        Route::get(
            '/companies/{company}/branches/options',
            [SuperAdminUserController::class, 'branchOptions']
        )->name('users.branch-options');

        Route::delete(
            '/users/{user}',
            [SuperAdminUserController::class, 'destroy']
        )->name('users.destroy');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/roles',
            [SuperAdminRoleController::class, 'index']
        )->name('roles.index');

        Route::get(
            '/roles/create',
            [SuperAdminRoleController::class, 'create']
        )->name('roles.create');

        Route::post(
            '/roles',
            [SuperAdminRoleController::class, 'store']
        )->name('roles.store');

        Route::get(
            '/roles/{role}',
            [SuperAdminRoleController::class, 'show']
        )->name('roles.show');

        Route::get(
            '/roles/{role}/edit',
            [SuperAdminRoleController::class, 'edit']
        )->name('roles.edit');

        Route::put(
            '/roles/{role}',
            [SuperAdminRoleController::class, 'update']
        )->name('roles.update');

        Route::delete(
            '/roles/{role}',
            [SuperAdminRoleController::class, 'destroy']
        )->name('roles.destroy');


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/permissions',
            [SuperAdminPermissionController::class, 'index']
        )->name('permissions.index');

        Route::get(
            '/permissions/create',
            [SuperAdminPermissionController::class, 'create']
        )->name('permissions.create');

        Route::post(
            '/permissions',
            [SuperAdminPermissionController::class, 'store']
        )->name('permissions.store');

        Route::get(
            '/permissions/{permission}/edit',
            [SuperAdminPermissionController::class, 'edit']
        )->name('permissions.edit');

        Route::put(
            '/permissions/{permission}',
            [SuperAdminPermissionController::class, 'update']
        )->name('permissions.update');

        Route::delete(
            '/permissions/{permission}',
            [SuperAdminPermissionController::class, 'destroy']
        )->name('permissions.destroy');


        Route::get(
            '/activity-logs',
            [ActivityLogController::class, 'index']
        )->name('activity-logs.index');

        Route::get(
            '/settings',
            [SuperAdminSettingController::class, 'index']
        )->name('settings.index');

    Route::post(
        '/notifications/{id}/read',
        [
            NotificationController::class,
            'read',
        ]
    )->name(
        'notifications.read'
    );


    Route::patch(
        '/notifications/{id}/read',
        [
            NotificationController::class,
            'markRead',
        ]
    )->name(
        'notifications.mark-read'
    );


    Route::patch(
        '/notifications/read-all',
        [
            NotificationController::class,
            'markAllRead',
        ]
    )->name(
        'notifications.read-all'
    );
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get(
        '/super-admin/companies/{company}/branches/{store}/enter',
        [BranchContextController::class, 'enter']
    )->name('super-admin.branch.enter');

    Route::post(
        '/select-branch/{store}',
        [BranchContextController::class, 'select']
    )->name('branch.select.store');

    Route::post(
        '/branch/exit',
        [BranchContextController::class, 'exit']
    )->name('branch.exit');

});


/*
|--------------------------------------------------------------------------
| profile controller
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit',
        ]
    )->name('profile.edit');


    Route::put(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    )->name('profile.update');


    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    );

    Route::put(
        '/profile/password',
        [
            ProfileController::class,
            'updatePassword',
        ]
    )->name('profile.password.update');


    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy',
        ]
    )->name('profile.destroy');

});


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get(
        '/business/dashboard',
        [BusinessDashboardController::class, 'index']
    )->name('business.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get(
        '/login/redirect',
        [LoginRedirectController::class, 'handle']
    )->name('login.redirect');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get(
        '/select-branch',
        [BranchSelectionController::class, 'index']
    )->name('branch.select');
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