<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Main Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BranchContextController;
use App\Http\Controllers\BranchSelectionController;


/*
|--------------------------------------------------------------------------
| Authentication Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Auth\BusinessRegistrationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\LoginRedirectController;


/*
|--------------------------------------------------------------------------
| Business Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Business\DashboardController as BusinessDashboardController;
use App\Http\Controllers\Business\CompanyController as BusinessCompanyController;
use App\Http\Controllers\Business\NotificationController as BusinessNotificationController;
use App\Http\Controllers\Business\BranchController as BusinessBranchController;
use App\Http\Controllers\Business\UserController as BusinessUserController;
use App\Http\Controllers\Business\RoleController as BusinessRoleController;
use App\Http\Controllers\Business\CompanyDashboardController;


/*
|--------------------------------------------------------------------------
| Super Admin Controllers
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\CompanyController as SuperAdminCompanyController;
use App\Http\Controllers\SuperAdmin\BranchController as SuperAdminBranchController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminRoleController;
use App\Http\Controllers\SuperAdmin\PermissionController as SuperAdminPermissionController;
use App\Http\Controllers\SuperAdmin\ActivityLogController;
use App\Http\Controllers\SuperAdmin\SettingController as SuperAdminSettingController;
use App\Http\Controllers\SuperAdmin\NotificationController;


/*
|--------------------------------------------------------------------------
| Public Home
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    fn() =>
    Inertia::render(
        'Welcome'
    )
)->name(
    'home'
);


/*
|--------------------------------------------------------------------------
| React Test
|--------------------------------------------------------------------------
|
| Development helper.
| Remove in production if no longer required.
|
*/

Route::get(
    '/react-test',
    function () {

        return Inertia::render(
            'Test'
        );
    }
)->middleware(
    'auth'
);


/*
|--------------------------------------------------------------------------
| Generic Dashboard Redirect
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    function () {

        $user =
            auth()->user();


        if (
            $user->is_super_user
        ) {

            return redirect()
                ->route(
                    'super-admin.dashboard'
                );
        }


        return redirect()
            ->route(
                'business.dashboard'
            );
    }
)->middleware(
    'auth'
)->name(
    'dashboard'
);


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'guest',
])->group(function () {

    Route::get(
        '/register-business',
        [
            BusinessRegistrationController::class,
            'create',
        ]
    )->name(
        'business.register'
    );


    Route::post(
        '/register-business',
        [
            BusinessRegistrationController::class,
            'store',
        ]
    )->name(
        'business.register.store'
    );


    Route::get(
        '/forgot-password',
        [
            PasswordResetLinkController::class,
            'create',
        ]
    )->name(
        'password.request'
    );


    Route::post(
        '/forgot-password',
        [
            PasswordResetLinkController::class,
            'store',
        ]
    )->name(
        'password.email'
    );
});


/*
|--------------------------------------------------------------------------
| Company-Level Business Management
|--------------------------------------------------------------------------
|
| These routes require login but do NOT require an active branch.
|
| This is intentional so Company Admin can:
|
| - view company dashboard
| - manage profile
| - manage branches
| - manage users
| - manage company roles
| - read notifications
|
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Company Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/company/dashboard',
        [
            CompanyDashboardController::class,
            'index',
        ]
    )->name(
        'business.company.dashboard'
    );


    /*
    |--------------------------------------------------------------------------
    | Company Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/company/profile',
        [
            BusinessCompanyController::class,
            'show',
        ]
    )->name(
        'business.company.show'
    );


    Route::put(
        '/company/profile',
        [
            BusinessCompanyController::class,
            'update',
        ]
    )->name(
        'business.company.update'
    );


    /*
    |--------------------------------------------------------------------------
    | Business Notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications',
        [
            BusinessNotificationController::class,
            'index',
        ]
    )->name(
        'notifications.index'
    );


    Route::post(
        '/notifications/{notification}/read',
        [
            BusinessNotificationController::class,
            'markAsRead',
        ]
    )->name(
        'notifications.read'
    );


    Route::post(
        '/notifications/read-all',
        [
            BusinessNotificationController::class,
            'markAllAsRead',
        ]
    )->name(
        'notifications.read-all'
    );


    /*
    |--------------------------------------------------------------------------
    | Company Users & Access
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'company/users'
    )
        ->name(
            'business.users.'
        )
        ->controller(
            BusinessUserController::class
        )
        ->group(function () {

            Route::get(
                '/',
                'index'
            )->name(
                'index'
            );


            Route::get(
                '/create',
                'create'
            )->name(
                'create'
            );


            Route::post(
                '/',
                'store'
            )->name(
                'store'
            );


            Route::get(
                '/{user}',
                'show'
            )->name(
                'show'
            );


            Route::get(
                '/{user}/edit',
                'edit'
            )->name(
                'edit'
            );


            Route::put(
                '/{user}',
                'update'
            )->name(
                'update'
            );


            Route::patch(
                '/{user}/status',
                'updateStatus'
            )->name(
                'status'
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Company Roles & Permissions
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'company/roles'
    )
        ->name(
            'business.roles.'
        )
        ->controller(
            BusinessRoleController::class
        )
        ->group(function () {

            Route::get(
                '/',
                'index'
            )->name(
                'index'
            );


            Route::get(
                '/create',
                'create'
            )->name(
                'create'
            );


            Route::post(
                '/',
                'store'
            )->name(
                'store'
            );


            Route::get(
                '/{role}',
                'show'
            )->name(
                'show'
            );


            Route::get(
                '/{role}/edit',
                'edit'
            )->name(
                'edit'
            );


            Route::put(
                '/{role}',
                'update'
            )->name(
                'update'
            );


            Route::patch(
                '/{role}/status',
                'updateStatus'
            )->name(
                'status'
            );


            Route::delete(
                '/{role}',
                'destroy'
            )->name(
                'destroy'
            );
        });


    /*
    |--------------------------------------------------------------------------
    | Company Branch Management
    |--------------------------------------------------------------------------
    */

    Route::prefix(
        'company'
    )
        ->name(
            'business.'
        )
        ->group(function () {

            Route::get(
                '/branches',
                [
                    BusinessBranchController::class,
                    'index',
                ]
            )->name(
                'branches.index'
            );


            Route::get(
                '/branches/create',
                [
                    BusinessBranchController::class,
                    'create',
                ]
            )->name(
                'branches.create'
            );


            Route::post(
                '/branches',
                [
                    BusinessBranchController::class,
                    'store',
                ]
            )->name(
                'branches.store'
            );


            Route::get(
                '/branches/{store}',
                [
                    BusinessBranchController::class,
                    'show',
                ]
            )->name(
                'branches.show'
            );


            Route::get(
                '/branches/{store}/edit',
                [
                    BusinessBranchController::class,
                    'edit',
                ]
            )->name(
                'branches.edit'
            );


            Route::put(
                '/branches/{store}',
                [
                    BusinessBranchController::class,
                    'update',
                ]
            )->name(
                'branches.update'
            );
        });
});


/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'super.admin',
])
    ->prefix(
        'super-admin'
    )
    ->name(
        'super-admin.'
    )
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                SuperAdminDashboardController::class,
                'index',
            ]
        )->name(
            'dashboard'
        );


        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/companies',
            [
                SuperAdminCompanyController::class,
                'index',
            ]
        )->name(
            'companies.index'
        );


        Route::get(
            '/companies/create',
            [
                SuperAdminCompanyController::class,
                'create',
            ]
        )->name(
            'companies.create'
        );


        Route::post(
            '/companies',
            [
                SuperAdminCompanyController::class,
                'store',
            ]
        )->name(
            'companies.store'
        );


        Route::get(
            '/companies/{company}',
            [
                SuperAdminCompanyController::class,
                'show',
            ]
        )->name(
            'companies.show'
        );


        Route::get(
            '/companies/{company}/edit',
            [
                SuperAdminCompanyController::class,
                'edit',
            ]
        )->name(
            'companies.edit'
        );


        Route::put(
            '/companies/{company}',
            [
                SuperAdminCompanyController::class,
                'update',
            ]
        )->name(
            'companies.update'
        );


        Route::post(
            '/companies/{company}/activate',
            [
                SuperAdminCompanyController::class,
                'activate',
            ]
        )->name(
            'companies.activate'
        );


        Route::post(
            '/companies/{company}/deactivate',
            [
                SuperAdminCompanyController::class,
                'deactivate',
            ]
        )->name(
            'companies.deactivate'
        );


        Route::delete(
            '/companies/{company}',
            [
                SuperAdminCompanyController::class,
                'destroy',
            ]
        )->name(
            'companies.destroy'
        );


        Route::post(
            '/companies/{id}/restore',
            [
                SuperAdminCompanyController::class,
                'restore',
            ]
        )->name(
            'companies.restore'
        );


        /*
        |--------------------------------------------------------------------------
        | Company Branches
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/companies/{company}/branches/create',
            [
                SuperAdminBranchController::class,
                'create',
            ]
        )->name(
            'companies.branches.create'
        );


        Route::post(
            '/companies/{company}/branches',
            [
                SuperAdminBranchController::class,
                'store',
            ]
        )->name(
            'companies.branches.store'
        );


        Route::get(
            '/companies/{company}/branches/{store}/edit',
            [
                SuperAdminBranchController::class,
                'edit',
            ]
        )->name(
            'companies.branches.edit'
        );


        Route::put(
            '/companies/{company}/branches/{store}',
            [
                SuperAdminBranchController::class,
                'update',
            ]
        )->name(
            'companies.branches.update'
        );


        Route::post(
            '/companies/{company}/branches/{store}/activate',
            [
                SuperAdminBranchController::class,
                'activate',
            ]
        )->name(
            'companies.branches.activate'
        );


        Route::post(
            '/companies/{company}/branches/{store}/deactivate',
            [
                SuperAdminBranchController::class,
                'deactivate',
            ]
        )->name(
            'companies.branches.deactivate'
        );


        /*
        |--------------------------------------------------------------------------
        | All Branches
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/branches',
            [
                SuperAdminBranchController::class,
                'index',
            ]
        )->name(
            'branches.index'
        );


        /*
        |--------------------------------------------------------------------------
        | Enter Company / Branch Context
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | This does NOT logout Super Admin.
        |
        | It only enters:
        |
        | active_company_id
        | active_store_id
        | super_admin_business_context
        |
        */

        Route::get(
            '/companies/{company}/branches/{store}/enter',
            [
                BranchContextController::class,
                'enter',
            ]
        )->name(
            'branch.enter'
        );


        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/users',
            [
                SuperAdminUserController::class,
                'index',
            ]
        )->name(
            'users.index'
        );


        Route::get(
            '/users/create',
            [
                SuperAdminUserController::class,
                'create',
            ]
        )->name(
            'users.create'
        );


        Route::post(
            '/users',
            [
                SuperAdminUserController::class,
                'store',
            ]
        )->name(
            'users.store'
        );


        Route::get(
            '/users/{user}',
            [
                SuperAdminUserController::class,
                'show',
            ]
        )->name(
            'users.show'
        );


        Route::get(
            '/users/{user}/edit',
            [
                SuperAdminUserController::class,
                'edit',
            ]
        )->name(
            'users.edit'
        );


        Route::put(
            '/users/{user}',
            [
                SuperAdminUserController::class,
                'update',
            ]
        )->name(
            'users.update'
        );


        Route::post(
            '/users/{user}/activate',
            [
                SuperAdminUserController::class,
                'activate',
            ]
        )->name(
            'users.activate'
        );


        Route::post(
            '/users/{user}/deactivate',
            [
                SuperAdminUserController::class,
                'deactivate',
            ]
        )->name(
            'users.deactivate'
        );


        Route::delete(
            '/users/{user}',
            [
                SuperAdminUserController::class,
                'destroy',
            ]
        )->name(
            'users.destroy'
        );


        /*
        |--------------------------------------------------------------------------
        | Branch Options For User
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/companies/{company}/branches/options',
            [
                SuperAdminUserController::class,
                'branchOptions',
            ]
        )->name(
            'users.branch-options'
        );


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/roles',
            [
                SuperAdminRoleController::class,
                'index',
            ]
        )->name(
            'roles.index'
        );


        Route::get(
            '/roles/create',
            [
                SuperAdminRoleController::class,
                'create',
            ]
        )->name(
            'roles.create'
        );


        Route::post(
            '/roles',
            [
                SuperAdminRoleController::class,
                'store',
            ]
        )->name(
            'roles.store'
        );


        Route::get(
            '/roles/{role}',
            [
                SuperAdminRoleController::class,
                'show',
            ]
        )->name(
            'roles.show'
        );


        Route::get(
            '/roles/{role}/edit',
            [
                SuperAdminRoleController::class,
                'edit',
            ]
        )->name(
            'roles.edit'
        );


        Route::put(
            '/roles/{role}',
            [
                SuperAdminRoleController::class,
                'update',
            ]
        )->name(
            'roles.update'
        );


        Route::delete(
            '/roles/{role}',
            [
                SuperAdminRoleController::class,
                'destroy',
            ]
        )->name(
            'roles.destroy'
        );


        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/permissions',
            [
                SuperAdminPermissionController::class,
                'index',
            ]
        )->name(
            'permissions.index'
        );


        Route::get(
            '/permissions/create',
            [
                SuperAdminPermissionController::class,
                'create',
            ]
        )->name(
            'permissions.create'
        );


        Route::post(
            '/permissions',
            [
                SuperAdminPermissionController::class,
                'store',
            ]
        )->name(
            'permissions.store'
        );


        Route::get(
            '/permissions/{permission}/edit',
            [
                SuperAdminPermissionController::class,
                'edit',
            ]
        )->name(
            'permissions.edit'
        );


        Route::put(
            '/permissions/{permission}',
            [
                SuperAdminPermissionController::class,
                'update',
            ]
        )->name(
            'permissions.update'
        );


        Route::delete(
            '/permissions/{permission}',
            [
                SuperAdminPermissionController::class,
                'destroy',
            ]
        )->name(
            'permissions.destroy'
        );


        /*
        |--------------------------------------------------------------------------
        | Activity Logs
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/activity-logs',
            [
                ActivityLogController::class,
                'index',
            ]
        )->name(
            'activity-logs.index'
        );


        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/settings',
            [
                SuperAdminSettingController::class,
                'index',
            ]
        )->name(
            'settings.index'
        );


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

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


/*
|--------------------------------------------------------------------------
| Branch / Business Context
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Select Branch
    |--------------------------------------------------------------------------
    |
    | Used by Company Admin / Store Users.
    |
    */

    Route::post(
        '/select-branch/{store}',
        [
            BranchContextController::class,
            'select',
        ]
    )->name(
        'branch.select.store'
    );


    /*
    |--------------------------------------------------------------------------
    | Exit Branch / Company Context
    |--------------------------------------------------------------------------
    |
    | Super Admin:
    |
    | - stays logged in
    | - clears active company/store
    | - returns to Super Admin dashboard
    |
    | Company user:
    |
    | - clears active store context
    | - controller decides whether to return to branch selection
    |
    */

    Route::post(
        '/branch/exit',
        [
            BranchContextController::class,
            'exit',
        ]
    )->name(
        'branch.exit'
    );
});


/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
|
| ONE profile route group only.
|
| Profile Show.jsx automatically chooses:
|
| SuperAdminLayout
| BusinessLayout
|
*/

Route::middleware([
    'auth',
    'verified',
    'business.context',
])->group(function () {

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit',
        ]
    )->name(
        'profile.edit'
    );


    Route::put(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    )->name(
        'profile.update'
    );


    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Password Update
    |--------------------------------------------------------------------------
    */

    Route::put(
        '/profile/password',
        [
            ProfileController::class,
            'updatePassword',
        ]
    )->name(
        'profile.password.update'
    );


    /*
    |--------------------------------------------------------------------------
    | Delete Profile
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy',
        ]
    )->name(
        'profile.destroy'
    );
});


/*
|--------------------------------------------------------------------------
| Login Redirect
|--------------------------------------------------------------------------
|
| Determines:
|
| Super Admin       → Super Admin dashboard
| Company Admin     → Business / branch flow
| Branch User       → Business / branch flow
|
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    Route::get(
        '/login/redirect',
        [
            LoginRedirectController::class,
            'handle',
        ]
    )->name(
        'login.redirect'
    );
});


/*
|--------------------------------------------------------------------------
| Branch Selection
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    Route::get(
        '/select-branch',
        [
            BranchSelectionController::class,
            'index',
        ]
    )->name(
        'branch.select'
    );
});


/*
|--------------------------------------------------------------------------
| Business Dashboard
|--------------------------------------------------------------------------
|
| Operational business pages require a valid active company + branch context.
|
*/

Route::middleware([
    'auth',
    'verified',
    'business.context',
])->group(function () {

    Route::get(
        '/business/dashboard',
        [
            BusinessDashboardController::class,
            'index',
        ]
    )->name(
        'business.dashboard'
    );
});


/*
|--------------------------------------------------------------------------
| Customer Payments
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
    'business.context',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Customer Payments Resource
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'customer-payments',
        CustomerPaymentController::class
    );


    /*
    |--------------------------------------------------------------------------
    | Search Sales Invoice
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customer-payments/search/invoice',
        [
            CustomerPaymentController::class,
            'searchInvoice',
        ]
    )->name(
        'customer-payments.search'
    );


    /*
    |--------------------------------------------------------------------------
    | Invoice Details
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customer-payments/invoice/{id}',
        [
            CustomerPaymentController::class,
            'invoiceDetails',
        ]
    )->name(
        'customer-payments.invoice'
    );


    /*
    |--------------------------------------------------------------------------
    | Print Receipt
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customer-payments/{customer_payment}/print',
        [
            CustomerPaymentController::class,
            'print',
        ]
    )->name(
        'customer-payments.print'
    );


    /*
    |--------------------------------------------------------------------------
    | Customer Ledger
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customer-ledger',
        [
            CustomerPaymentController::class,
            'customerLedger',
        ]
    )->name(
        'customer-ledger'
    );
});


/*
|--------------------------------------------------------------------------
| Future Reports
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Sales Report
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'sales-reports',
    //     SalesReportController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Report
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'purchase-return-reports',
    //     PurchaseReturnReportController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | Sales Return Report
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'sales-return-reports',
    //     SalesReturnReportController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | Stock Report
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'stock-reports',
    //     StockReportController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'supplier-ledgers',
    //     SupplierLedgerController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | Customer Ledger
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'customer-ledgers',
    //     CustomerLedgerController::class
    // );


    /*
    |--------------------------------------------------------------------------
    | GST Report
    |--------------------------------------------------------------------------
    */

    // Route::resource(
    //     'gst-reports',
    //     GSTReportController::class
    // );
});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| Operational Business Routes
|--------------------------------------------------------------------------
|
| Every route loaded below requires:
|
| - authenticated user
| - verified account
| - valid active company
| - valid active branch
| - valid branch access
|
| Individual route files may still apply permission middleware for each
| module/action.
|
*/

Route::middleware([
    'auth',
    'verified',
    'business.context',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Master Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/masters.php';


    /*
    |--------------------------------------------------------------------------
    | Purchase Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/purchase.php';


    /*
    |--------------------------------------------------------------------------
    | Sales Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/sales.php';


    /*
    |--------------------------------------------------------------------------
    | Inventory Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/inventory.php';

    /*
    |--------------------------------------------------------------------------
    | supplier-payment-routes Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/supplier-payment-routes.php';


    /*
    |--------------------------------------------------------------------------
    | Report Routes
    |--------------------------------------------------------------------------
    */

    require __DIR__ . '/reports.php';


});
