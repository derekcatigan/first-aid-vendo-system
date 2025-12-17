<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Item\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('login');
    Route::post('/', [AuthController::class, 'authenticate'])->name('login.post');
});

Route::middleware('auth')->group(function () {

    // Route for Admin 
    Route::middleware('role:admin')->group(function () {
        Route::get('/manage/user', [UserController::class, 'index'])->name('user.index');
        Route::post('/manage/user', [UserController::class, 'store'])->name('user.store');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/items/inventory', [ItemController::class, 'index'])->name('item.index');
        Route::post('/items/inventory', [ItemController::class, 'store'])->name('item.store');
        Route::delete('/item/{id}', [ItemController::class, 'destroy'])->name('item.destroy');
        Route::patch('/item/{item}/quantity', [ItemController::class, 'updateQuantity'])
            ->name('item.updateQuantity');
    });

    // Routes for Admin and Worker
    Route::middleware('role:admin,worker')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/items/{item}/restock', [DashboardController::class, 'restock']);
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
        Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
        Route::get('/recent-logs', [DashboardController::class, 'recentLogs'])->name('dashboard.recentLogs');
    });

    // Logout Route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
