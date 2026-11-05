<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Inventory Asset Value</div>
        <div class="stat-value" style="color: #2563eb;">
            $<?= number_format((float)($costingData['overall']['total_valuation'] ?? 0), 2) ?>
        </div>
        <div class="stat-desc">Calculated strictly from In-Stock physical serial batches</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">In-Stock Physical Units</div>
        <div class="stat-value" style="color: #10b981;">
            <?= (int)($costingData['overall']['total_units'] ?? 0) ?>
        </div>
        <div class="stat-desc">Total components currently in warehouse</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Average Unit Cost</div>
        <div class="stat-value" style="color: #06b6d4;">
            $<?= number_format((float)($costingData['overall']['avg_unit_cost'] ?? 0), 2) ?>
        </div>
        <div class="stat-desc">Weighted average across all categories</div>
    </div>
</div>

<!-- Category Breakdown -->
<div class="card">
    <div class="card-header">
        <span class="card-title">💼 Category Asset Distribution</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Hardware Category</th>
                        <th>In-Stock Units</th>
                        <th>% of Total Stock</th>
                        <th class="text-right">Total Category Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalUnits = (int)($costingData['overall']['total_units'] ?? 1) ?: 1;
                    $totalVal = (float)($costingData['overall']['total_valuation'] ?? 1.0) ?: 1.0;
                    ?>
                    <?php if (empty($costingData['by_category'])): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">No in-stock components found to calculate valuation.</td></tr>
                    <?php else: ?>
                        <?php foreach ($costingData['by_category'] as $cat): ?>
                            <?php 
                                $pct = round(((float)$cat['category_valuation'] / $totalVal) * 100, 1);
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cat['category']) ?></strong></td>
                                <td><?= (int)$cat['in_stock_count'] ?> units</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="background:#e2e8f0; width:120px; height:8px; border-radius:4px; overflow:hidden;">
                                            <div style="background:#2563eb; width:<?= min(100, $pct) ?>%; height:100%;"></div>
                                        </div>
                                        <span style="font-size:0.8rem; color:var(--text-secondary);"><?= $pct ?>%</span>
                                    </div>
                                </td>
                                <td class="text-right font-mono" style="font-weight:700;">
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

<!-- Product-Level Cost Breakdown -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📊 Granular Hardware Item Cost Breakdown</span>
        <input type="text" data-table-search="#costing-table" class="form-control" placeholder="Filter items..." style="max-width: 250px; padding: 4px 10px; font-size: 0.82rem;">
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" id="costing-table">
                <thead>
                    <tr>
                        <th>Product Item</th>
                        <th>Category</th>
                        <th>In-Stock Units</th>
                        <th>Min Batch Cost</th>
                        <th>Max Batch Cost</th>
                        <th>Avg Batch Cost</th>
                        <th class="text-right">Total Item Valuation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($costingData['by_product'])): ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding:24px;">No inventory data available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($costingData['by_product'] as $prod): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($prod['product_name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9; color:#475569;"><?= htmlspecialchars($prod['category']) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-instock"><?= (int)$prod['in_stock_count'] ?> In-Stock</span>
                                </td>
                                <td class="font-mono text-muted">$<?= number_format((float)$prod['min_batch_cost'], 2) ?></td>
                                <td class="font-mono text-muted">$<?= number_format((float)$prod['max_batch_cost'], 2) ?></td>
                                <td class="font-mono" style="font-weight:600;">$<?= number_format((float)$prod['avg_batch_cost'], 2) ?></td>
                                <td class="text-right font-mono" style="font-weight:700; color:#2563eb;">
                                    $<?= number_format((float)$prod['total_product_valuation'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
