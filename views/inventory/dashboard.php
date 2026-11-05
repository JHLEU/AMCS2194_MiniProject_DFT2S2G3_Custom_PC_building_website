<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Inventory Valuation</div>
        <div class="stat-value" style="color: #00f2fe;">
            $<?= number_format((float)($costing['overall']['total_valuation'] ?? 0), 2) ?>
        </div>
        <div class="stat-desc">Based on batch cost of in-stock items</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">In-Stock Physical Units</div>
        <div class="stat-value" style="color: #10b981;">
            <?= (int)($costing['overall']['total_units'] ?? 0) ?>
        </div>
        <div class="stat-desc">Unique serial-tracked components</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Low Stock Alerts</div>
        <div class="stat-value" style="color: <?= count($lowStockProducts) > 0 ? '#ff334b' : '#10b981' ?>;">
            <?= count($lowStockProducts) ?>
        </div>
        <div class="stat-desc">Items below threshold</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Suppliers</div>
        <div class="stat-value" style="color: #ff334b;">
            <?= (int)$totalSuppliers ?>
        </div>
        <div class="stat-desc"><?= (int)$totalProducts ?> hardware catalog products</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Low Stock Alert Card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Critical Low-Stock Alerts</span>
            <a href="index.php?route=inventory/low-stock" class="btn btn-outline btn-sm">View All &rarr;</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($lowStockProducts)): ?>
                <div style="padding: 30px; text-align: center; color: var(--text-secondary);">
                    🎉 Great news! All hardware catalog items are above their safety thresholds.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>In-Stock</th>
                                <th>Threshold</th>
                                <th>Deficit</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($lowStockProducts, 0, 5) as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    </td>
                                    <td><span class="badge" style="background:#f1f5f9; color:#475569;"><?= htmlspecialchars($item['category']) ?></span></td>
                                    <td>
                                        <span class="badge badge-lowstock">
                                            <?= (int)$item['in_stock_count'] ?> units
                                        </span>
                                    </td>
                                    <td><?= (int)$item['low_stock_threshold'] ?></td>
                                    <td class="text-danger font-mono" style="font-weight:700;">
                                        -<?= max(0, (int)$item['deficit']) ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="index.php?route=restock/create&product_id=<?= (int)$item['id'] ?>" class="btn btn-warning btn-sm">
                                            Restock
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Category Valuation Card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">💼 Inventory by Category</span>
            <a href="index.php?route=inventory/costing" class="btn btn-outline btn-sm">Cost Report</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Units</th>
                            <th class="text-right">Valuation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($costing['by_category'])): ?>
                            <tr><td colspan="3" class="text-center text-muted" style="padding:20px;">No in-stock items found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($costing['by_category'] as $cat): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cat['category']) ?></strong></td>
                                    <td><?= (int)$cat['in_stock_count'] ?></td>
                                    <td class="text-right font-mono" style="font-weight: 600;">
                                        $<?= number_format((float)$cat['category_valuation'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
