<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Custom PC ERP') ?> - Supply Chain & Inventory</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<!-- Top Unified Navbar -->
<header class="global-navbar">
    <div class="header-left">
        <a href="public/index.php?page=home" class="logo">GO<span>PC</span></a>
    </div>
    <nav>
        <a href="public/index.php?page=home">首页 Home</a>
        <a href="public/index.php?page=builder">💻 配件组装 (Builder)</a>
        <a href="public/index.php?page=components">📦 硬件目录 (Components)</a>
        <a href="index.php?route=inventory/dashboard" class="active" style="color: #00f2fe;">📊 ERP 后台 (Admin)</a>
    </nav>
    <div class="header-right">
        <div class="nav-actions">
            <span class="admin-badge">⚡ ERP 后台模式</span>
        </div>
    </div>
</header>
<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <span>⚡ Custom PC ERP</span>
            <span class="brand-badge">v1.0</span>
        </div>
        <ul class="sidebar-nav">
            <li class="nav-section-title">Storefront</li>
            <li>
                <a href="public/index.php?page=home" target="_blank" style="color: #00f2fe;">
                    <span>🛒 Storefront & Builder</span>
                    <span style="font-size: 0.7rem; opacity: 0.8;">&nearr;</span>
                </a>
            </li>

            <li class="nav-section-title">Core Overview</li>
            <li>
                <a href="index.php?route=inventory/dashboard" class="<?= ($route ?? '') === 'inventory/dashboard' ? 'active' : '' ?>">
                    <span>📊 Dashboard</span>
                </a>
            </li>

            <li class="nav-section-title">Module 3: Inventory & SN</li>
            <li>
                <a href="index.php?route=products/index" class="<?= strpos($route ?? '', 'products') === 0 ? 'active' : '' ?>">
                    <span>💻 Product Catalog</span>
                </a>
            </li>
            <li>
                <a href="index.php?route=inventory/serials" class="<?= strpos($route ?? '', 'inventory/serials') === 0 || strpos($route ?? '', 'inventory/create-serial') === 0 ? 'active' : '' ?>">
                    <span>🏷️ Serial Numbers (SN)</span>
                </a>
            </li>
            <li>
                <a href="index.php?route=inventory/low-stock" class="<?= ($route ?? '') === 'inventory/low-stock' ? 'active' : '' ?>">
                    <span>⚠️ Low-Stock Alerts</span>
                </a>
            </li>
            <li>
                <a href="index.php?route=inventory/costing" class="<?= ($route ?? '') === 'inventory/costing' ? 'active' : '' ?>">
                    <span>💰 Inventory Costing</span>
                </a>
            </li>

            <li class="nav-section-title">Module 4: Suppliers & Restock</li>
            <li>
                <a href="index.php?route=suppliers/index" class="<?= strpos($route ?? '', 'suppliers') === 0 ? 'active' : '' ?>">
                    <span>🏢 Supplier Profiles</span>
                </a>
            </li>
            <li>
                <a href="index.php?route=restock/index" class="<?= ($route ?? '') === 'restock/index' || ($route ?? '') === 'restock/create' || ($route ?? '') === 'restock/view' ? 'active' : '' ?>">
                    <span>📦 Restock Orders</span>
                </a>
            </li>
            <li>
                <a href="index.php?route=restock/history" class="<?= ($route ?? '') === 'restock/history' ? 'active' : '' ?>">
                    <span>📜 Purchasing History</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div>Environment: Windows CLI</div>
            <div style="margin-top: 4px; color: #475569;">MySQL (PDO) &bull; Native PHP</div>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
