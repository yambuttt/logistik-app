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
    });
    Route::get('/warehouse/dashboard', [WarehouseDashboardController::class, 'index'])->name('warehouse.dashboard');
    Route::get('/driver/dashboard', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
});