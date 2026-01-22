<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('products', ProductController::class);
Route::get('/products', [ProductController::class, 'getNews']);
Route::apiResource('/reviews', ProductReviewsController::class);
//Route::get('/reviews', [ProductReviewsController::class, 'getProductReviews']);
//Route::get('/reviews/count/{product_id}', [ProductReviewsController::class, 'getProductReviewCount']);
