<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::resource('/shop/products/reviews', App\Http\Controllers\ProductReviewController::class);
Route::resource('/shop/products', App\Http\Controllers\ProductController::class)->only(['index', 'show']);
Route::get('/shop/pickup-points', [App\Http\Controllers\OrderController::class, 'pickupPoints']);
Route::post('/shop/orders', [App\Http\Controllers\OrderController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/image', [App\Http\Controllers\ImageController::class, 'crop'])->name('image.crop');
