<div class="filter-bar">
    <form action="index.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; width:100%;">
        <input type="hidden" name="route" value="inventory/serials">

        <input type="text" name="search" class="form-control" placeholder="Search by SN code or Product..." value="<?= htmlspecialchars($search ?? '') ?>" style="max-width: 280px;">

        <select name="status" class="form-select" style="max-width: 170px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="In-Stock" <?= ($selectedStatus ?? '') === 'In-Stock' ? 'selected' : '' ?>>In-Stock</option>
            <option value="Sold" <?= ($selectedStatus ?? '') === 'Sold' ? 'selected' : '' ?>>Sold</option>
            <option value="RMA" <?= ($selectedStatus ?? '') === 'RMA' ? 'selected' : '' ?>>RMA</option>
        </select>

        <select name="product_id" class="form-select" style="max-width: 250px;" onchange="this.form.submit()">
            <option value="">All Hardware Products</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= ((int)($selectedProduct ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if (!empty($search) || !empty($selectedStatus) || !empty($selectedProduct)): ?>
            <a href="index.php?route=inventory/serials" class="btn btn-outline" style="color:var(--text-secondary);">Clear</a>
        <?php endif; ?>

        <div style="margin-left: auto;">
            <a href="index.php?route=inventory/create-serial" class="btn btn-primary">+ Register Serial Numbers</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏷️ Physical Item Serial Number Ledger</span>
        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($serials) ?> Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($serials)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <h3>No Serial Numbers Found</h3>
                <p>Register new physical units to begin tracking serial numbers and calculating batch costing.</p>
                <a href="index.php?route=inventory/create-serial" class="btn btn-primary" style="margin-top: 10px;">+ Register Serial Numbers</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="serials-table">
                    <thead>
                        <tr>
                            <th>Serial Number (SN)</th>
                            <th>Hardware Product</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Batch Unit Cost</th>
                            <th>Restock Ref</th>
                            <th>Registered Date</th>
                            <th class="text-right">Change Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serials as $sn): ?>
                            <tr>
                                <td>
                                    <strong class="font-mono" style="font-size: 0.95rem; color: #1e293b;">
                                        <?= htmlspecialchars($sn['sn_code']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <a href="index.php?route=products/edit&id=<?= (int)$sn['product_id'] ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                                        <?= htmlspecialchars($sn['product_name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge" style="background: #f1f5f9; color: #475569;">
                                        <?= htmlspecialchars($sn['product_category']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $statusClass = 'badge-instock';
                                    if ($sn['status'] === 'Sold') $statusClass = 'badge-sold';
                                    if ($sn['status'] === 'RMA')  $statusClass = 'badge-rma';
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($sn['status']) ?>
                                    </span>
                                </td>
                                <td class="font-mono">
                                    $<?= number_format((float)$sn['batch_cost'], 2) ?>
                                </td>
                                <td>
                                    <?php if (!empty($sn['order_code'])): ?>
                                        <a href="index.php?route=restock/view&id=<?= (int)$sn['restock_order_id'] ?>" class="font-mono" style="font-size:0.8rem; color:#2563eb;">
                                            <?= htmlspecialchars($sn['order_code']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                    <?= date('M d, Y H:i', strtotime($sn['created_at'])) ?>
                                </td>
                                <td class="text-right">
                                    <!-- Inline Quick Status Updater -->
                                    <form action="index.php?route=inventory/update-serial-status" method="POST" style="display:inline-flex; align-items:center; gap:4px;">
                                        <input type="hidden" name="id" value="<?= (int)$sn['id'] ?>">
                                        <select name="status" class="form-select" style="padding: 2px 6px; font-size: 0.75rem; width: 100px;" onchange="this.form.submit()">
                                            <option value="In-Stock" <?= $sn['status'] === 'In-Stock' ? 'selected' : '' ?>>In-Stock</option>
                                            <option value="Sold" <?= $sn['status'] === 'Sold' ? 'selected' : '' ?>>Sold</option>
                                            <option value="RMA" <?= $sn['status'] === 'RMA' ? 'selected' : '' ?>>RMA</option>
                                        </select>
                                    </form>

                                    <!-- Delete Button -->
                                    <form action="index.php?route=inventory/delete-serial" method="POST" style="display:inline-block;" data-confirm="Delete serial number <?= htmlspecialchars($sn['sn_code']) ?>?">
                                        <input type="hidden" name="id" value="<?= (int)$sn['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#fecaca; padding:2px 6px;">&times;</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
