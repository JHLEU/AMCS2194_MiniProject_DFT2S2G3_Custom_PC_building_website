<div class="filter-bar">
    <form action="index.php" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center; width:100%;">
        <input type="hidden" name="route" value="restock/index">

        <select name="status" class="form-select" style="max-width: 170px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Pending" <?= ($selectedStatus ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Approved" <?= ($selectedStatus ?? '') === 'Approved' ? 'selected' : '' ?>>Approved</option>
            <option value="Completed" <?= ($selectedStatus ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
            <option value="Cancelled" <?= ($selectedStatus ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <select name="supplier_id" class="form-select" style="max-width: 250px;" onchange="this.form.submit()">
            <option value="">All Suppliers</option>
            <?php foreach ($suppliers as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= ((int)($selectedSupplier ?? 0)) === (int)$s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['company_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-outline">Filter</button>
        <?php if (!empty($selectedStatus) || !empty($selectedSupplier)): ?>
            <a href="index.php?route=restock/index" class="btn btn-outline" style="color:var(--text-secondary);">Clear</a>
        <?php endif; ?>

        <div style="margin-left: auto; display:flex; gap:8px;">
            <a href="index.php?route=restock/history" class="btn btn-outline">📜 Purchasing History</a>
            <a href="index.php?route=restock/create" class="btn btn-primary">+ Create Restock Request</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">📦 Restock Purchase Orders & Approval Pipeline</span>
        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($orders) ?> Total Orders</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($orders)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <h3>No Purchase Orders Found</h3>
                <p>Create a restock request to replenish depleted inventory from authorized suppliers.</p>
                <a href="index.php?route=restock/create" class="btn btn-primary" style="margin-top: 10px;">+ Create Order</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="restock-orders-table">
                    <thead>
                        <tr>
                            <th>Order Code</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Items / Units</th>
                            <th>Total Amount</th>
                            <th>Approval Status</th>
                            <th>Completed Date</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <a href="index.php?route=restock/view&id=<?= (int)$o['id'] ?>" class="font-mono" style="font-weight:700; color:#2563eb; text-decoration:none;">
                                        <?= htmlspecialchars($o['order_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($o['supplier_name']) ?></strong>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($o['supplier_contact']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($o['order_date']) ?></td>
                                <td>
                                    <?= (int)$o['total_line_items'] ?> item(s) &bull; <strong><?= (int)$o['total_units_ordered'] ?></strong> units
                                </td>
                                <td class="font-mono" style="font-weight: 700;">
                                    $<?= number_format((float)$o['total_amount'], 2) ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'badge-pending';
                                    if ($o['status'] === 'Approved')  $statusClass = 'badge-approved';
                                    if ($o['status'] === 'Completed') $statusClass = 'badge-completed';
                                    if ($o['status'] === 'Cancelled') $statusClass = 'badge-cancelled';
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($o['status']) ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                    <?= $o['completed_at'] ? date('M d, Y H:i', strtotime($o['completed_at'])) : '—' ?>
                                </td>
                                <td class="text-right">
                                    <a href="index.php?route=restock/view&id=<?= (int)$o['id'] ?>" class="btn btn-outline btn-sm">
                                        View Details / Manage &rarr;
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
