<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProductReviewController;
use App\Http\Controllers\Dashboard\ProductCategoryController;

Route::get('/', function () {
    return view('dashboard.app');
})->middleware(['verified'])->name('dashboard');

Route::resource('products', ProductController::class)->names('dashboard.products');
Route::resource('reviews', ProductReviewController::class)->names('dashboard.reviews');
Route::resource('categories', ProductCategoryController::class)->names('dashboard.categories');;

Route::put('reviews/{review}/confirm', [ProductReviewController::class, 'confirm'])
    ->name('reviews.confirm');
