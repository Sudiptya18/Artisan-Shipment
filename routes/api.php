<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\FormatController;
use App\Http\Controllers\API\NavigationController;
use App\Http\Controllers\API\OriginController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductLookupController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

Route::get('/navigations', [NavigationController::class, 'index']);

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/{product}/images', [ProductController::class, 'storeImages'])->middleware('auth:sanctum');
    Route::delete('/{product}/images/{image}', [ProductController::class, 'destroyImage'])->middleware('auth:sanctum');
});

Route::get('/products/lookups', ProductLookupController::class)->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('formats', FormatController::class);
    Route::apiResource('origins', OriginController::class);
});

