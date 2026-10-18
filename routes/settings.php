<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingPrintSettingsController;

Route::middleware(['auth'])
    ->prefix('settings')
    ->name('settings.')
    ->group(function () {

        // Settings home
        Route::redirect('/', '/settings/billing-print')
            ->name('index');

        // Company defaults
        Route::get(
            '/company/billing-print',
            [BillingPrintSettingsController::class, 'company']
        )->name('company.billing-print.edit');

        Route::put(
            '/company/billing-print',
            [BillingPrintSettingsController::class, 'updateCompany']
        )->name('company.billing-print.update');

        // Active branch overrides
        Route::get(
            '/billing-print',
            [BillingPrintSettingsController::class, 'edit']
        )->name('billing-print.edit');

        Route::put(
            '/billing-print',
            [BillingPrintSettingsController::class, 'update']
        )->name('billing-print.update');
    });


    
