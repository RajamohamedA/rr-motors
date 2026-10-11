<?php

use App\Http\Controllers\BarcodeLabelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Barcode Labels
    |--------------------------------------------------------------------------
    |
    | Preview, print and PDF use POST because the request contains selected
    | products, label quantities, paper size and display preferences.
    |
    */

    Route::get(
        '/barcode-labels',
        [BarcodeLabelController::class, 'index']
    )->name('barcode-labels.index');

    Route::get(
        '/barcode-labels/products/search',
        [BarcodeLabelController::class, 'searchProducts']
    )->name('barcode-labels.products.search');

    Route::post(
        '/barcode-labels/generate-missing',
        [BarcodeLabelController::class, 'generateMissingBarcodes']
    )->name('barcode-labels.generate-missing');

    Route::post(
        '/barcode-labels/preview',
        [BarcodeLabelController::class, 'preview']
    )->name('barcode-labels.preview');

    Route::post(
        '/barcode-labels/print',
        [BarcodeLabelController::class, 'print']
    )->name('barcode-labels.print');

    Route::post(
        '/barcode-labels/pdf',
        [BarcodeLabelController::class, 'pdf']
    )->name('barcode-labels.pdf');
});
