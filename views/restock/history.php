<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Procurement Spend</div>
        <div class="stat-value" style="color: #2563eb;">
            $<?= number_format((float)($metrics['total_spent'] ?? 0), 2) ?>
        </div>
        <div class="stat-desc">Historical spend on fulfilled orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Completed Restock Orders</div>
        <div class="stat-value" style="color: #10b981;">
            <?= (int)($metrics['total_completed_orders'] ?? 0) ?>
        </div>
        <div class="stat-desc">Finalized supplier purchase orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Hardware Procured</div>
        <div class="stat-value" style="color: #06b6d4;">
            <?= (int)($metrics['total_items_procured'] ?? 0) ?>
        </div>
        <div class="stat-desc">Physical components received into inventory</div>
    </div>
</div>

<div class="filter-bar">
    <form action="index.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; width:100%;">
        <input type="hidden" name="route" value="restock/history">

        <input type="text" name="search" class="form-control" placeholder="Search order code or supplier..." value="<?= htmlspecialchars($search ?? '') ?>" style="max-width: 250px;">

        <select name="supplier_id" class="form-select" style="max-width: 220px;">
            <option value="">All Suppliers</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= ((int)($selectedSupplier ?? 0)) === (int)$s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['company_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:0.8rem; color:var(--text-secondary);">From:</span>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom ?? '') ?>" style="max-width: 150px;">
        </div>

        <div style="display:flex; align-items:center; gap:6px;">
            <span style="font-size:0.8rem; color:var(--text-secondary);">To:</span>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo ?? '') ?>" style="max-width: 150px;">
        </div>

        <button type="submit" class="btn btn-outline">Apply Filter</button>
        <?php if (!empty($search) || !empty($selectedSupplier) || !empty($dateFrom) || !empty($dateTo)): ?>
            <a href="index.php?route=restock/history" class="btn btn-outline" style="color:var(--text-secondary);">Reset</a>
        <?php endif; ?>

        <div style="margin-left: auto;">
            <a href="index.php?route=restock/index" class="btn btn-outline">&larr; Active Orders Pipeline</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📜 Read-Only Fulfilled Purchasing Archive</span>
        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($history) ?> Completed Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($history)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <h3>No Completed Transactions</h3>
                <p>When restock orders are finalized and marked as Completed, they will be archived here for financial tracking and auditing.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="history-table">
                    <thead>
                        <tr>
                            <th>Order Code</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Fulfilled / Received Date</th>
                            <th>Lines / Units</th>
                            <th class="text-right">Transaction Total</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <strong class="font-mono" style="color:#2563eb;">
                                        <?= htmlspecialchars($h['order_code']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($h['supplier_name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($h['supplier_contact']) ?> (<?= htmlspecialchars($h['supplier_email']) ?>)</div>
                                </td>
                                <td><?= htmlspecialchars($h['order_date']) ?></td>
                                <td>
                                    <span class="badge badge-completed">
                                        ✓ <?= $h['completed_at'] ? date('M d, Y H:i', strtotime($h['completed_at'])) : 'Completed' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= (int)$h['total_line_items'] ?> line(s) &bull; <strong><?= (int)$h['total_units_received'] ?> units</strong>
                                </td>
                                <td class="text-right font-mono" style="font-weight: 700; font-size: 0.95rem;">
                                    $<?= number_format((float)$h['total_amount'], 2) ?>
                                </td>
                                <td class="text-right">
                                    <a href="index.php?route=restock/view&id=<?= (int)$h['id'] ?>" class="btn btn-outline btn-sm">
                                        View Slip
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
