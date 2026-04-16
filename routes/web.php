<?php

use App\Http\Controllers\ProfileController;
//add dashboard controller
use App\Http\Controllers\DashboardController;
//add the catogary controller
use App\Http\Controllers\CategoryController;
//add company controller
use App\Http\Controllers\CompanyController;
//add store controller
use App\Http\Controllers\StoreController;
//add unit controller
use App\Http\Controllers\UnitController;

use Illuminate\Support\Facades\Route;

//landing page
Route::get('/', function () {
    return view('welcome');
});

//dashboard page
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

//add the routes for company
Route::middleware(['auth'])->group(function () {
    Route::resource('companies', CompanyController::class);
});

//add the routes for categories
Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class);
});

//add the routes for unit
Route::middleware(['auth'])->group(function () {
    Route::resource('units', UnitController::class);
});

//add the routes for store
Route::middleware(['auth'])->group(function () {
    Route::resource('stores', StoreController::class);
});

//add the page for profiles
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
