<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\FormatController;
use App\Http\Controllers\API\NavigationController;
use App\Http\Controllers\API\OriginController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductLookupController;
use App\Http\Controllers\API\UserPermissionController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
});

Route::get('/navigations', [NavigationController::class, 'index'])->middleware('auth:sanctum');
Route::get('/navigations/check-permission/{routeName}', [NavigationController::class, 'checkRoutePermission'])->middleware('auth:sanctum');

Route::prefix('dashboard')->middleware('auth:sanctum')->group(function () {
    Route::get('/total-products', [DashboardController::class, 'totalProducts']);
    Route::get('/total-brands', [DashboardController::class, 'totalBrands']);
    Route::get('/total-categories', [DashboardController::class, 'totalCategories']);
    Route::get('/total-formats', [DashboardController::class, 'totalFormats']);
    Route::get('/products-by-brand', [DashboardController::class, 'productsByBrand']);
});

Route::prefix('products')->group(function () {
    Route::get('/lookups', ProductLookupController::class)->middleware('auth:sanctum');
    Route::get('/', [ProductController::class, 'index'])->middleware('auth:sanctum');
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/bulk', [ProductController::class, 'bulkStore'])->middleware('auth:sanctum');
    Route::get('/{product}', [ProductController::class, 'show'])->middleware('auth:sanctum');
    Route::put('/{product}', [ProductController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('/{product}/images', [ProductController::class, 'storeImages'])->middleware('auth:sanctum');
    Route::delete('/{product}/images/{image}', [ProductController::class, 'destroyImage'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('formats', FormatController::class);
    Route::apiResource('origins', OriginController::class);

            // User Permissions
            Route::prefix('user-permissions')->group(function () {
                Route::get('/users', [UserPermissionController::class, 'getUsers']);
                Route::get('/roles', [UserPermissionController::class, 'getRoles']);
                Route::get('/navigations', [UserPermissionController::class, 'getNavigations']);
                Route::get('/user/{userId}', [UserPermissionController::class, 'getUserPermissions']);
                Route::post('/set', [UserPermissionController::class, 'setUserPermissions']);
            });

            // Activity Logs (only for Super Admin)
            Route::get('/activity-logs', [\App\Http\Controllers\API\ActivityLogController::class, 'index']);
        });

