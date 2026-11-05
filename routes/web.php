<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Erp\DashboardController as ErpDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Custom PC & ERP System
|--------------------------------------------------------------------------
*/

// Public Storefront (Placeholder for module 7)
Route::get('/', function () {
    return view('home');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ERP Backend Routes (Protected: Admin, Staff_Warehouse, Staff_Tech)
Route::prefix('erp')->name('erp.')->middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/', [ErpDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [ErpDashboardController::class, 'index']);

    // Admin Only: User Management Placeholder
    Route::prefix('users')->name('users.')->middleware('role:Admin')->group(function () {
        Route::get('/', function () {
            return "User Management (Admin Only)";
        })->name('index');
    });

    // Warehouse & Admin: Inventory Placeholder
    Route::prefix('inventory')->name('inventory.')->middleware('role:Admin,Staff_Warehouse')->group(function () {
        Route::get('/', function () {
            return "Inventory Management (Warehouse & Admin)";
        })->name('index');
    });

    // Tech & Admin: Support Placeholder
    Route::prefix('support')->name('support.')->middleware('role:Admin,Staff_Tech')->group(function () {
        Route::get('/', function () {
            return "Tech Support & Scheduling (Tech & Admin)";
        })->name('index');
    });
});
