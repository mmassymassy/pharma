<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Public\OrderController as PublicOrderController;
use App\Http\Controllers\Public\ProductController as PublicProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('clients', ClientController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('orders', AdminOrderController::class);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    });
});

Route::prefix('public')->middleware('throttle:60,1')->group(function () {
    Route::get('/products', [PublicProductController::class, 'index']);
    Route::get('/products/{product}', [PublicProductController::class, 'show']);
    Route::post('/orders', [PublicOrderController::class, 'store'])->middleware('throttle:10,1');
});
