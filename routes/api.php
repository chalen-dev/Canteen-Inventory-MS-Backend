<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryLogController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;


$admin = UserRole::ADMIN->value;
$cashier = UserRole::CASHIER->value;
$customer = UserRole::CUSTOMER->value;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');





Route::middleware('auth:sanctum')->group(function () use ($admin, $cashier, $customer) {

    //Admin Only Routes
    Route::middleware('role:' . $admin)->group(function () {

        //Menu Item
        Route::post('/menu-items', [MenuItemController::class, 'store']);
        Route::match(['put', 'patch'], '/menu-items/{menu_item}', [MenuItemController::class, 'update']);
        Route::delete('/menu-items/{menu_item}', [MenuItemController::class, 'destroy']);
        Route::post('/menu-items/bulk-delete', [MenuItemController::class, 'bulkDelete']);

        //Category
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        //Order
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    });

    //Admin & Cashier Routes
    Route::middleware('role:' . $admin . ',' . $cashier)->group(function () {

        //Inventory Log
        Route::post('/inventory-logs', [InventoryLogController::class, 'store']);
        Route::match(['put', 'patch'], '/inventory-logs/{inventory_log}', [InventoryLogController::class, 'update']);
        Route::delete('/inventory-logs/{inventory_log}', [InventoryLogController::class, 'destroy']);
        Route::post('/menu-items/{menu_item}/inventory-logs', [InventoryLogController::class, 'store']);
        Route::post('/inventory-logs/bulk-delete', [InventoryLogController::class, 'bulkDelete']);
        Route::patch('/inventory-logs/{inventory_log}/quantity', [InventoryLogController::class, 'updateQuantity']);
        Route::patch('/inventory-logs/{inventory_log}/toggle-availability', [InventoryLogController::class, 'toggleAvailability']);
        Route::post('/inventory-logs/bulk-toggle-availability', [InventoryLogController::class, 'bulkToggleAvailability']);
    });

    //Cashier Only Routes
    Route::middleware('role:' . $cashier)->group(function () {

    });

    //Cashier and Customer Routes
    Route::middleware('role:' . $cashier . ',' . $customer)->group(function () {

        //Order
        Route::post('/orders', [OrderController::class, 'store']);
        Route::match(['put', 'patch'], '/orders/{order}', [OrderController::class, 'update']);

    });

    //Customer Only Routes
    Route::middleware('role:' . $customer)->group(function () {

    });

    //Menu Item
    Route::get('/menu-items', [MenuItemController::class, 'index']);
    Route::get('/menu-items/{menu_item}', [MenuItemController::class, 'show']);

    //Category
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    //Order
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    //Inventory Log
    Route::get('/inventory-logs', [InventoryLogController::class, 'index']);
    Route::get('/inventory-logs/{inventory_log}', [InventoryLogController::class, 'show']);
    Route::get('/menu-items/{menu_item}/inventory-logs', [InventoryLogController::class, 'index']);

    //Order Item
    Route::get('/order-items', [OrderItemController::class, 'index']);
    Route::get('/order-items/{order_item}', [OrderItemController::class, 'show']);
    Route::post('/order-items', [OrderItemController::class, 'store']);
    Route::match(['put', 'patch'], '/order-items/{order_item}', [OrderItemController::class, 'update']);
    Route::delete('/order-items/{order_item}', [OrderItemController::class, 'destroy']);

});
