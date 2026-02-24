<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProductReviewController;

Route::view('/login', 'dashboard');
Route::view('/reset', 'dashboard');
Route::view('/register', 'dashboard');
//Route::view('/dashboard', 'dashboard');
Route::resource('/shop/products/reviews', App\Http\Controllers\ProductReviewController::class);
Route::resource('/shop/products', App\Http\Controllers\ProductController::class);
Route::get('/dashboard', function () {
    return view('dashboard.app');
})->middleware(['auth', 'verified'])->name('dashboard.home');

Route::resource('/dashboard/products', ProductController::class);
Route::resource('/dashboard/reviews', ProductReviewController::class);
//Route::get('/profile', [ProfileController::class, 'edit']);
//Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
//Route::put('/dashboard/reviews/confirm', [ProductReviewController::class, 'confirm']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
//
//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');
//
//
//Route::middleware(['auth', 'role:admin'])->group(function () {
//    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
//    Route::resource('/admin/users', AdminController::class);
//});

require __DIR__.'/auth.php';

Auth::routes();

///Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
