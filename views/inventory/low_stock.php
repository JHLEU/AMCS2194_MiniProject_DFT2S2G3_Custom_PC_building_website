<div class="filter-bar">
    <input type="text" data-table-search="#low-stock-table" class="form-control" placeholder="Search low stock hardware by name, category, or specs..." style="max-width: 400px;">
    <div style="margin-left: auto; color: var(--text-secondary); font-size: 0.85rem;">
        Total Low-Stock Items: <strong style="color: var(--danger);"><?= count($lowStockItems) ?></strong>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">⚠️ Hardware Items Falling Below Safety Thresholds</span>
        <a href="index.php?route=restock/create" class="btn btn-primary btn-sm">+ Draft Restock Order</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($lowStockItems)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">✅</div>
                <h3>Stock Levels Healthy</h3>
                <p>No products are currently under their configured low-stock thresholds.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="low-stock-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Hardware Specs</th>
                            <th>In-Stock Units</th>
                            <th>Min Threshold</th>
                            <th>Deficit Units</th>
                            <th>Est. Unit Cost</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockItems as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge" style="background: #e2e8f0; color: #334155;">
                                        <?= htmlspecialchars($item['category']) ?>
                                    </span>
                                </td>
                                <td style="max-width: 280px; font-size: 0.8rem; color: var(--text-secondary);">
                                    <?= htmlspecialchars($item['specs'] ?: 'No specs listed') ?>
                                </td>
                                <td>
                                    <span class="badge badge-lowstock">
                                        <?= (int)$item['in_stock_count'] ?> In-Stock
                                    </span>
                                </td>
                                <td>
                                    <strong><?= (int)$item['low_stock_threshold'] ?></strong>
                                </td>
                                <td>
                                    <span style="color: var(--danger); font-weight: 700;">
                                        -<?= max(0, (int)$item['deficit']) ?> units
                                    </span>
                                </td>
                                <td class="font-mono">
                                    $<?= number_format((float)$item['cost_price'], 2) ?>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="index.php?route=restock/create&product_id=<?= (int)$item['id'] ?>" class="btn btn-warning btn-sm">
                                        Order Restock
                                    </a>
                                    <a href="index.php?route=inventory/create-serial&product_id=<?= (int)$item['id'] ?>" class="btn btn-outline btn-sm">
                                        + Add SN
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
