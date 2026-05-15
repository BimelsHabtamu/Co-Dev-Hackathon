<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);
    Route::middleware('role:manager')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::get('users', [UserController::class, 'index']);

        Route::post('products',           [ProductController::class, 'store']);
        Route::put('products/{product}',  [ProductController::class, 'update']);
        Route::patch('products/{product}',[ProductController::class, 'update']);
        Route::delete('products/{product}',[ProductController::class, 'destroy']);

        Route::post('inventory/{product}/stock-in', [InventoryController::class, 'stockIn']);

        Route::get('reports', [ReportController::class, 'generate']);
    });

  
    Route::get('products',           [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);

    Route::get('inventory', [InventoryController::class, 'index']);

    Route::get('sales',        [SaleController::class, 'index']);
    Route::post('sales',       [SaleController::class, 'store']);
    Route::get('sales/{sale}', [SaleController::class, 'show']);
});
