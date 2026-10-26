<?php

use App\Http\Controllers\SuperAdmin\CompanyLocationController;
use Illuminate\Support\Facades\Route;

// Require this file once at the TOP LEVEL of routes/web.php.
Route::middleware(['auth', 'super.admin'])
    ->prefix('super-admin/company-locations')
    ->name('super-admin.company-locations.')
    ->group(function () {
        Route::get('/states', [CompanyLocationController::class, 'states'])->name('states');
        Route::get('/districts', [CompanyLocationController::class, 'districts'])->name('districts');
        Route::get('/cities', [CompanyLocationController::class, 'cities'])->name('cities');
    });
