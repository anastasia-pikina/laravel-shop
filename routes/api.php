<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductReviewsAdminController;
use Illuminate\Support\Facades\Auth;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Неверный email или пароль'
        ], 401);
    }

    $user = Auth::user();

    $token = $user->createToken(
        'api-token',
        ['*'],
        now()->addHours(2)
    )->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});

//Route::apiResource('products', ProductController::class);
//Route::get('/products', [ProductController::class, 'getNews']);
//Route::resource('/reviews', ProductReviewController::class);
//Route::get('/reviews', [ProductReviewsController::class, 'getProductReviews']);
//Route::get('/reviews/count/{product_id}', [ProductReviewsController::class, 'getProductReviewCount']);
Route::resource('products_admin', ProductReviewsAdminController::class);

