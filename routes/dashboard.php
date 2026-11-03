<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\ProductReviewController;
use App\Http\Controllers\Dashboard\ProductCategoryController;
use App\Http\Controllers\Dashboard\InventoryController;
use App\Http\Controllers\Dashboard\PickupPointController;
use App\Http\Controllers\Dashboard\OrderController;

Route::get('/', function () {
    return view('dashboard.app');
})->middleware(['verified'])->name('dashboard');

Route::name('dashboard.')->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('reviews', ProductReviewController::class);
    Route::resource('categories', ProductCategoryController::class);
    Route::put('reviews/{review}/confirm', [ProductReviewController::class, 'confirm'])
        ->name('reviews.confirm');
    Route::post('products/{product}/inventory/adjust', [InventoryController::class, 'adjust'])
        ->name('inventory.adjust');
    Route::resource('pickup-points', PickupPointController::class)->only(['index', 'store', 'update']);
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}/delivery', [OrderController::class, 'delivery'])->name('orders.delivery');
    Route::post('orders/{order}/paid', [OrderController::class, 'markPaid'])->name('orders.paid');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/returned', [OrderController::class, 'returned'])->name('orders.returned');
});
