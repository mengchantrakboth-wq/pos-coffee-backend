<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Public routes (no login needed)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (must be logged in)
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin only routes (must be logged in + admin role)
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth:api'])->group(function () {
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/today', [OrderController::class, 'getTodayOrders']);
    Route::get('/orders/status/{status}', [OrderController::class, 'getByStatus']);
    Route::get('/orders/{order_id}', [OrderController::class, 'show']);
    Route::put('/orders/{order_id}', [OrderController::class, 'update']);
    Route::delete('/orders/{order_id}', [OrderController::class, 'destroy']);

    // Order Items
    Route::get('/orders/{order_id}/items', [OrderItemController::class, 'index']);
    Route::post('/orders/{order_id}/items', [OrderItemController::class, 'store']);
    Route::put('/orders/{order_id}/items/{item_id}', [OrderItemController::class, 'update']);
    Route::delete('/orders/{order_id}/items/{item_id}', [OrderItemController::class, 'destroy']);

    // Products
    Route::apiResource('products', ProductController::class);

    //Categories
    Route::apiResource('categories', CategoryController::class);
});
