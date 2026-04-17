<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Warehouse\DashboardController as WarehouseDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Inventory\ProductController;
use App\Http\Controllers\Admin\Inventory\GoodsReceiptController;
use App\Http\Controllers\Admin\Inventory\StockController;
use App\Http\Controllers\Admin\Inventory\InventoryMovementController;
use App\Http\Controllers\Admin\Inventory\WasteController;
use App\Http\Controllers\Admin\Inventory\StockOpnameController;
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'warehouse' => redirect()->route('warehouse.dashboard'),
        'driver' => redirect()->route('driver.dashboard'),
        default => redirect()->route('login'),
    };
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', ProductController::class)->except(['show', 'destroy']);
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
        Route::get('goods-receipts/create', [GoodsReceiptController::class, 'create'])->name('goods-receipts.create');
        Route::post('goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
        Route::get('inventory-movements', [InventoryMovementController::class, 'index'])->name('inventory-movements.index');
        Route::get('wastes', [WasteController::class, 'index'])->name('wastes.index');
        Route::get('wastes/create', [WasteController::class, 'create'])->name('wastes.create');
        Route::post('wastes', [WasteController::class, 'store'])->name('wastes.store');
        Route::get('stock-opnames', [StockOpnameController::class, 'index'])->name('stock-opnames.index');
        Route::get('stock-opnames/create', [StockOpnameController::class, 'create'])->name('stock-opnames.create');
        Route::post('stock-opnames', [StockOpnameController::class, 'store'])->name('stock-opnames.store');
    });
    Route::get('/warehouse/dashboard', [WarehouseDashboardController::class, 'index'])->name('warehouse.dashboard');
    Route::get('/driver/dashboard', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
});