<?php
/**
 * Central Entry Point & Zero-Rewrite Custom PHP Router
 * Custom PC Building ERP - Supply Chain & Inventory Modules
 * Run via CLI: php -S localhost:8000 index.php
 */

// If requested via PHP built-in server for static files in /public, return false to serve directly
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && $path !== '/index.php' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
        return false;
    }
}

// Start PHP session for flash messages and UI state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require Controllers
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/InventoryController.php';
require_once __DIR__ . '/controllers/SupplierController.php';
require_once __DIR__ . '/controllers/RestockController.php';

// Route Resolution
$route = $_GET['route'] ?? '';

// Fallback checking for PATH_INFO (e.g. index.php/inventory/costing)
if ($route === '' && !empty($_SERVER['PATH_INFO'])) {
    $route = trim($_SERVER['PATH_INFO'], '/');
}

// Fallback checking for clean REQUEST_URI when using php -S built-in server
if ($route === '' && !empty($_SERVER['REQUEST_URI'])) {
    $uriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($uriPath !== '' && $uriPath !== 'index.php') {
        $route = str_replace('index.php/', '', $uriPath);
    }
}

// Default route
if ($route === '') {
    $route = 'inventory/dashboard';
}

// Normalize route
$route = strtolower(trim($route, '/'));

// Route Table
$routes = [
    // Module 3: Inventory & Serial Numbers
    'inventory/dashboard'             => [InventoryController::class, 'dashboard'],
    'inventory/low-stock'             => [InventoryController::class, 'lowStock'],
    'inventory/costing'               => [InventoryController::class, 'costing'],
    'inventory/serials'               => [InventoryController::class, 'serials'],
    'inventory/create-serial'         => [InventoryController::class, 'createSerial'],
    'inventory/store-serial'          => [InventoryController::class, 'storeSerial'],
    'inventory/update-serial-status'  => [InventoryController::class, 'updateSerialStatus'],
    'inventory/delete-serial'         => [InventoryController::class, 'deleteSerial'],

    // Module 3: Hardware Product Catalog
    'products/index'                  => [ProductController::class, 'index'],
    'products/create'                 => [ProductController::class, 'create'],
    'products/store'                  => [ProductController::class, 'store'],
    'products/edit'                   => [ProductController::class, 'edit'],
    'products/update'                 => [ProductController::class, 'update'],
    'products/delete'                 => [ProductController::class, 'delete'],

    // Module 4: Supplier Profiles & Hardware Mapping
    'suppliers/index'                 => [SupplierController::class, 'index'],
    'suppliers/create'                => [SupplierController::class, 'create'],
    'suppliers/store'                 => [SupplierController::class, 'store'],
    'suppliers/edit'                  => [SupplierController::class, 'edit'],
    'suppliers/update'                => [SupplierController::class, 'update'],
    'suppliers/delete'                => [SupplierController::class, 'delete'],
    'suppliers/mapping'               => [SupplierController::class, 'mapping'],
    'suppliers/save-mapping'          => [SupplierController::class, 'saveMapping'],
    'suppliers/remove-mapping'        => [SupplierController::class, 'removeMapping'],

    // Module 4: Restock Orders & Purchasing History
    'restock/index'                   => [RestockController::class, 'index'],
    'restock/create'                  => [RestockController::class, 'create'],
    'restock/store'                   => [RestockController::class, 'store'],
    'restock/view'                    => [RestockController::class, 'view'],
    'restock/update-status'           => [RestockController::class, 'updateStatus'],
    'restock/history'                 => [RestockController::class, 'history'],
    'restock/api-supplier-products'   => [RestockController::class, 'getSupplierProducts'],
];

// Dispatch Request
if (isset($routes[$route])) {
    [$controllerClass, $method] = $routes[$route];
    try {
        $controller = new $controllerClass();
        $controller->$method();
    } catch (Throwable $e) {
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><title>System Error</title><style>body{font-family:Segoe UI,sans-serif;padding:30px;background:#0f172a;color:#f8fafc;} .card{background:#1e293b;border-left:4px solid #ef4444;padding:24px;border-radius:8px;} pre{background:#090d16;padding:16px;border-radius:6px;overflow:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h2 style='color:#ef4444;margin-top:0;'>Application Runtime Error</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "<p><a href='index.php?route=inventory/dashboard' style='color:#38bdf8;'>&larr; Return to Dashboard</a></p>";
        echo "</div></body></html>";
    }
} else {
    // 404 Route Not Found
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><title>404 Not Found</title><style>body{font-family:Segoe UI,sans-serif;text-align:center;padding:80px 20px;background:#0f172a;color:#f8fafc;} a{color:#38bdf8;text-decoration:none;}</style></head><body>";
    echo "<h1 style='font-size:3rem;margin-bottom:8px;'>404</h1>";
    echo "<h2>Route Not Found</h2>";
    echo "<p>The requested route <code>" . htmlspecialchars($route) . "</code> does not exist.</p>";
    echo "<p><a href='index.php?route=inventory/dashboard'>Go back to ERP Dashboard</a></p>";
    echo "</body></html>";
}
