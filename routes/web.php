<?php

use App\Http\Controllers\ProfileController;
//add dashboard controller
use App\Http\Controllers\DashboardController;
//add the catogary controller
use App\Http\Controllers\CategoryController;
//use company controller
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

//add the routes for company
Route::middleware(['auth'])->group(function () {

    Route::get('/company', [CompanyController::class, 'edit'])
        ->name('company.edit');

    Route::post('/company', [CompanyController::class, 'update'])
        ->name('company.update');

});

Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
