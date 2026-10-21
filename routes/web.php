<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::resource('/shop/products/reviews', App\Http\Controllers\ProductReviewController::class);
Route::resource('/shop/products', App\Http\Controllers\ProductController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/image', [App\Http\Controllers\ImageController::class, 'crop'])->name('image.crop');
