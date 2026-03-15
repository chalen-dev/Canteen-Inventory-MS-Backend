<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryLogController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\UserController;


$admin = UserRole::ADMIN->value;
$cashier = UserRole::CASHIER->value;
$customer = UserRole::CUSTOMER->value;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () use ($admin, $cashier, $customer) {

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
        Route::post('/inventory-logs/bulk-archive', [InventoryLogController::class, 'bulkArchive']);
        Route::post('/inventory-logs/bulk-unarchive', [InventoryLogController::class, 'bulkUnarchive']);
        Route::post('/inventory-logs/bulk-toggle-availability', [InventoryLogController::class, 'bulkToggleAvailability']);
        Route::get('/inventory-logs/available-pos', [InventoryLogController::class, 'availableForPos']);

        // Customer list (for order assignment)
        Route::get('/users/customers', [UserController::class, 'customers']);

        //Order
        // Store order for a customer (admin/cashier only)
        Route::post('/orders/for-customer', [OrderController::class, 'storeForCustomerByStaff']);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/bulk-delete', [OrderController::class, 'bulkDelete']);
        Route::put('/orders/{order}/with-items', [OrderController::class, 'updateWithItems']);

        //User
        Route::get('/users/pos', [UserController::class, 'posUser']);
    });

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

        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        //Dashboard
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/sales-by-period', [DashboardController::class, 'salesByPeriod']);
        Route::get('/dashboard/best-selling-items', [DashboardController::class, 'bestSellingItems']);
        Route::get('/dashboard/sales-by-category', [DashboardController::class, 'salesByCategory']);
        Route::get('/dashboard/order-volume', [DashboardController::class, 'orderVolume']);
    });



    //Cashier Only Routes
    Route::middleware('role:' . $cashier)->group(function () {
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });

    //Cashier and Customer Routes
    Route::middleware('role:' . $cashier . ',' . $customer)->group(function () {

    });

    //Customer Only Routes
    Route::middleware('role:' . $customer)->group(function () {
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'customerCancel']);
    });

    //Menu Item
    Route::get('/menu-items/stock-status', [MenuItemController::class, 'stockStatus']);
    Route::get('/menu-items', [MenuItemController::class, 'index']);
    Route::get('/menu-items/{menu_item}', [MenuItemController::class, 'show']);
    Route::get('/menu-items/{menu_item}/best-inventory', [MenuItemController::class, 'bestInventory']);


    //Category
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    //Order
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::match(['put', 'patch'], '/orders/{order}', [OrderController::class, 'update']);


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

    //Notification
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
