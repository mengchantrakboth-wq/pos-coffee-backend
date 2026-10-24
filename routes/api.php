<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', [AuthController::class, 'login']);

// Logged in
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);


});

// Admin only (Super Admin / Manager)
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});

 // Orders (today/status must stay above {order_id})
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/today', [OrderController::class, 'getTodayOrders']);
    Route::get('/orders/status/{status}', [OrderController::class, 'getByStatus']);
    Route::get('/orders/{order_id}', [OrderController::class, 'show']);
    Route::match(['put', 'patch'], '/orders/{order_id}', [OrderController::class, 'update']);
    Route::delete('/orders/{order_id}', [OrderController::class, 'destroy']);

    // Order items
    Route::get('/orders/{order_id}/items', [OrderItemController::class, 'index']);
    Route::post('/orders/{order_id}/items', [OrderItemController::class, 'store']);
    Route::match(['put', 'patch'], '/orders/{order_id}/items/{item_id}', [OrderItemController::class, 'update']);
    Route::delete('/orders/{order_id}/items/{item_id}', [OrderItemController::class, 'destroy']);

    // Products (POST route allows image upload on update)
    Route::post('/products/{product}', [ProductController::class, 'update']);
    Route::apiResource('products', ProductController::class);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Product Variants
    Route::apiResource('variants', ProductVariantController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    //tables
    Route::apiResource('tables', TableController::class);
