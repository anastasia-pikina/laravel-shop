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

Route::post('products/{product}/inventory/adjust', [App\Http\Controllers\Dashboard\InventoryController::class, 'adjust'])
    ->name('dashboard.inventory.adjust');
Route::get('pickup-points', [App\Http\Controllers\Dashboard\PickupPointController::class, 'index'])->name('dashboard.pickup-points.index');
Route::post('pickup-points', [App\Http\Controllers\Dashboard\PickupPointController::class, 'store'])->name('dashboard.pickup-points.store');
Route::put('pickup-points/{point}', [App\Http\Controllers\Dashboard\PickupPointController::class, 'update'])->name('dashboard.pickup-points.update');
Route::get('orders', [App\Http\Controllers\Dashboard\OrderController::class, 'index'])->name('dashboard.orders.index');
Route::get('orders/{order}', [App\Http\Controllers\Dashboard\OrderController::class, 'show'])->name('dashboard.orders.show');
Route::put('orders/{order}/delivery', [App\Http\Controllers\Dashboard\OrderController::class, 'delivery'])->name('dashboard.orders.delivery');
Route::post('orders/{order}/paid', [App\Http\Controllers\Dashboard\OrderController::class, 'markPaid'])->name('dashboard.orders.paid');
Route::post('orders/{order}/cancel', [App\Http\Controllers\Dashboard\OrderController::class, 'cancel'])->name('dashboard.orders.cancel');
Route::post('orders/{order}/returned', [App\Http\Controllers\Dashboard\OrderController::class, 'returned'])->name('dashboard.orders.returned');
