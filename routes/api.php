<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes (no login needed)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (must be logged in)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin only routes (must be logged in + admin role)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
});
