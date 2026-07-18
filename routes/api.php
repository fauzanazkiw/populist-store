<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Payment gateway webhook (public; authenticated via Midtrans signature_key)
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle']);

// Public product catalog
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Route yang perlu login (pake middleware auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart management
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add/{product}', [CartController::class, 'add']);
        Route::patch('/update/{cartItem}', [CartController::class, 'update']);
        Route::delete('/remove/{cartItem}', [CartController::class, 'remove']);
        Route::post('/clear', [CartController::class, 'clear']);
    });

    // Admin product management
    Route::prefix('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'adminIndex']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::patch('/products/{product}/toggle-active', [ProductController::class, 'toggleActive']);

        // Image management
        Route::post('/products/{product}/images', [ProductController::class, 'storeImages']);
        Route::patch('/products/{product}/images/{image}/set-main', [ProductController::class, 'setMainImage']);
        Route::delete('/products/{product}/images/{image}', [ProductController::class, 'deleteImage']);

        // Stock management
        Route::patch('/products/{product}/stock/deduct', [ProductController::class, 'deductStock']);
        Route::patch('/products/{product}/stock/restore', [ProductController::class, 'restoreStock']);
    });
});
