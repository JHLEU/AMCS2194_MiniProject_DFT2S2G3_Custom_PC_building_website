<?php

use App\Http\Controllers\Api\CompatibilityController as ApiCompatibilityController;
use App\Http\Controllers\Api\LiveChatController as ApiLiveChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Customer\SupportTicketController as CustomerSupportTicketController;
use App\Http\Controllers\Customer\WarrantyController as CustomerWarrantyController;
use App\Http\Controllers\Erp\CompatibilityController as ErpCompatibilityController;
use App\Http\Controllers\Erp\DashboardController as ErpDashboardController;
use App\Http\Controllers\Erp\FinanceController as ErpFinanceController;
use App\Http\Controllers\Erp\InventoryController;
use App\Http\Controllers\Erp\LiveChatController as ErpLiveChatController;
use App\Http\Controllers\Erp\OrderController as ErpOrderController;
use App\Http\Controllers\Erp\ProductController;
use App\Http\Controllers\Erp\RestockController;
use App\Http\Controllers\Erp\SupplierController;
use App\Http\Controllers\Erp\SupportTicketController as ErpSupportTicketController;
use App\Http\Controllers\Erp\TechScheduleController as ErpTechScheduleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PcBuilderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Custom PC & ERP System
|--------------------------------------------------------------------------
*/

// Public Storefront (Module 7: frontend-storefront)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/builder', [PcBuilderController::class, 'index'])->name('builder.index');
Route::post('/builder/checkout', [PcBuilderController::class, 'buildToCart'])->name('builder.checkout');
Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator.index');
Route::post('/calculator/evaluate', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');

// Checkout & Dummy/Sandbox Payment Gateway (Module 4: order-checkout)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');

// Public Order Tracking & Customer Order History (Module 4: order-checkout)
Route::get('/orders/track', [OrderTrackingController::class, 'track'])->name('orders.track');
Route::get('/customer/orders', [OrderTrackingController::class, 'myOrders'])->name('customer.orders')->middleware('auth');

// Customer Hardware Warranties & RMA / Support Tickets (Module 6: warranty-techsupport)
Route::middleware('auth')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/warranties', [CustomerWarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/support', [CustomerSupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/create', [CustomerSupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [CustomerSupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [CustomerSupportTicketController::class, 'show'])->name('support.show');
});

// Compatibility & Power Estimation Engine JSON Endpoints (Module 3: compatibility-engine)
Route::prefix('api/compatibility')->name('api.compatibility.')->group(function () {
    Route::post('/evaluate', [ApiCompatibilityController::class, 'evaluate'])->name('evaluate');
    Route::get('/compatible-parts', [ApiCompatibilityController::class, 'compatibleParts'])->name('compatible-parts');
    Route::post('/bottleneck', [ApiCompatibilityController::class, 'bottleneck'])->name('bottleneck');
});

// Customer Live Chat Service API (Module 8: live-chat-service)
Route::prefix('api/chat')->name('api.chat.')->group(function () {
    Route::get('/messages', [ApiLiveChatController::class, 'messages'])->name('messages');
    Route::post('/send', [ApiLiveChatController::class, 'send'])->name('send');
});

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

    // Hardware Compatibility & Power Test Bench (Accessible to all staff roles)
    Route::get('/compatibility', [ErpCompatibilityController::class, 'index'])->name('compatibility.index');

    // Sales & Assembly Orders Management (Module 4: order-checkout, Accessible to all staff)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [ErpOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [ErpOrderController::class, 'show'])->name('show');
        Route::post('/{order}/status', [ErpOrderController::class, 'updateStatus'])->name('status');
        Route::post('/{order}/shipping', [ErpOrderController::class, 'updateShipping'])->name('shipping');
    });

    // Admin Only: User Management Placeholder
    Route::prefix('users')->name('users.')->middleware('role:Admin')->group(function () {
        Route::get('/', function () {
            return "User Management (Admin Only)";
        })->name('index');
    });

    // Admin Only: Financials, Cashflow & Profit/Loss (Module 5: finance-cashflow)
    Route::prefix('finance')->name('finance.')->middleware('role:Admin')->group(function () {
        Route::get('/', [ErpFinanceController::class, 'index'])->name('index');
        Route::get('/pnl', [ErpFinanceController::class, 'pnl'])->name('pnl');
        Route::get('/cashflow', [ErpFinanceController::class, 'cashflow'])->name('cashflow');
    });

    // Warehouse & Admin: Supply Chain & Inventory Management (Module 2: inventory-supply)
    Route::middleware('role:Admin,Staff_Warehouse')->group(function () {
        // Inventory Overview, Low-Stock & Costing
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
            Route::get('/costing', [InventoryController::class, 'costing'])->name('costing');
        });

        // Serial Numbers Tracking
        Route::prefix('inventory/serials')->name('serials.')->group(function () {
            Route::get('/', [InventoryController::class, 'serials'])->name('index');
            Route::get('/create', [InventoryController::class, 'createSerial'])->name('create');
            Route::post('/', [InventoryController::class, 'storeSerial'])->name('store');
            Route::patch('/{serialNumber}/status', [InventoryController::class, 'updateSerialStatus'])->name('status');
            Route::delete('/{serialNumber}', [InventoryController::class, 'destroySerial'])->name('destroy');
        });

        // Hardware Product Catalog CRUD
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        // Suppliers & Hardware Mapping
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::get('/create', [SupplierController::class, 'create'])->name('create');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::get('/{supplier}/mapping', [SupplierController::class, 'mapping'])->name('mapping');
            Route::post('/{supplier}/mapping', [SupplierController::class, 'saveMapping'])->name('mapping.save');
            Route::delete('/{supplier}/mapping/{product}', [SupplierController::class, 'removeMapping'])->name('mapping.remove');
        });

        // Restock Purchase Orders & History
        Route::prefix('restock')->name('restock.')->group(function () {
            Route::get('/', [RestockController::class, 'index'])->name('index');
            Route::get('/create', [RestockController::class, 'create'])->name('create');
            Route::post('/', [RestockController::class, 'store'])->name('store');
            Route::get('/supplier-products', [RestockController::class, 'supplierProducts'])->name('supplier-products');
            Route::get('/history', [RestockController::class, 'history'])->name('history');
            Route::get('/{restockOrder}', [RestockController::class, 'show'])->name('show');
            Route::post('/{restockOrder}/status', [RestockController::class, 'updateStatus'])->name('status');
        });
    });

    // Tech & Admin: Support & Workshop Dispatch (Module 6: warranty-techsupport)
    Route::prefix('support')->name('support.')->middleware('role:Admin,Staff_Tech')->group(function () {
        Route::get('/', [ErpSupportTicketController::class, 'index'])->name('index');
        Route::get('/tickets/{ticket}', [ErpSupportTicketController::class, 'show'])->name('show');
        Route::post('/tickets/{ticket}/status', [ErpSupportTicketController::class, 'updateStatus'])->name('status');
        Route::post('/tickets/{ticket}/assign', [ErpSupportTicketController::class, 'assignSchedule'])->name('assign');
        Route::get('/schedules', [ErpTechScheduleController::class, 'index'])->name('schedules');
        Route::get('/warranties', [ErpSupportTicketController::class, 'warranties'])->name('warranties');
    });

    // Customer Service Live Chat Desk (Module 8: live-chat-service)
    Route::prefix('chat')->name('chat.')->middleware('role:Admin,Staff_Tech')->group(function () {
        Route::get('/', [ErpLiveChatController::class, 'index'])->name('index');
        Route::get('/{sessionId}/history', [ErpLiveChatController::class, 'history'])->name('history');
        Route::post('/{sessionId}/reply', [ErpLiveChatController::class, 'reply'])->name('reply');
    });
});
