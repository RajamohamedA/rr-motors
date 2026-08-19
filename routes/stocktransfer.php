<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StockTransferController;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Quick Transfer from Sales
    |--------------------------------------------------------------------------
    */

    Route::post(
        'stock-transfers/quick',
        [StockTransferController::class, 'quickTransfer']
    )->name('stock-transfers.quick');

    Route::get(
        'stock-transfers',
        [StockTransferController::class, 'index']
    )->name('stock-transfers.index');

    Route::get(
        'stock-transfers/create',
        [StockTransferController::class, 'create']
    )->name('stock-transfers.create');

    Route::post(
        'stock-transfers',
        [StockTransferController::class, 'store']
    )->name('stock-transfers.store');

    Route::get(
        'stock-transfers/{id}',
        [StockTransferController::class, 'show']
    )->name('stock-transfers.show');
});