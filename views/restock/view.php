<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 4px;">
            Restock Order: <span class="font-mono" style="color: #2563eb;"><?= htmlspecialchars($order['order_code']) ?></span>
        </h2>
        <div style="font-size: 0.85rem; color: var(--text-secondary);">
            Ordered on <?= htmlspecialchars($order['order_date']) ?> &bull; Supplier: <strong><?= htmlspecialchars($order['supplier_name']) ?></strong>
        </div>
    </div>
    <div style="display:flex; gap: 8px;">
        <a href="index.php?route=restock/index" class="btn btn-outline">&larr; Back to Orders</a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Order Line Items -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦 Order Line Items & Received Components</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hardware Product</th>
                            <th>Category</th>
                            <th>Supplier SKU</th>
                            <th>Ordered Qty</th>
                            <th>Unit Cost</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge" style="background: #f1f5f9; color: #475569;">
                                        <?= htmlspecialchars($item['product_category']) ?>
                                    </span>
                                </td>
                                <td class="font-mono text-muted">
                                    <?= htmlspecialchars($item['supplier_sku'] ?: '—') ?>
                                </td>
                                <td>
                                    <strong><?= (int)$item['quantity'] ?></strong> units
                                </td>
                                <td class="font-mono">
                                    $<?= number_format((float)$item['unit_cost'], 2) ?>
                                </td>
                                <td class="text-right font-mono" style="font-weight: 700;">
                                    $<?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc; font-weight:700;">
                            <td colspan="5" class="text-right" style="padding:14px;">Total Purchase Valuation:</td>
                            <td class="text-right font-mono" style="font-size:1.15rem; color:#2563eb; padding:14px;">
                                $<?= number_format((float)$order['total_amount'], 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Status & Supplier Summary Card -->
    <div>
        <!-- Status Box -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Approval & Status Workflow</span>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 16px;">
                    <div class="text-muted" style="font-size:0.8rem; margin-bottom:4px;">Current Status</div>
                    <?php
                    $statusClass = 'badge-pending';
                    if ($order['status'] === 'Approved')  $statusClass = 'badge-approved';
                    if ($order['status'] === 'Completed') $statusClass = 'badge-completed';
                    if ($order['status'] === 'Cancelled') $statusClass = 'badge-cancelled';
                    ?>
                    <span class="badge <?= $statusClass ?>" style="font-size: 0.9rem; padding: 6px 12px;">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                    <?php if ($order['completed_at']): ?>
                        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:6px;">
                            Completed: <?= date('M d, Y H:i', strtotime($order['completed_at'])) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Workflow Actions -->
                <?php if ($order['status'] === 'Pending'): ?>
                    <form action="index.php?route=restock/update-status" method="POST" style="margin-bottom: 10px;">
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="Approved">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            ✓ Approve Purchase Order
                        </button>
                    </form>
                    <form action="index.php?route=restock/update-status" method="POST" data-confirm="Are you sure you want to cancel this order?">
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="Cancelled">
                        <button type="submit" class="btn btn-outline" style="width: 100%; color: var(--danger); border-color:#fecaca;">
                            Cancel Order
                        </button>
                    </form>
                <?php elseif ($order['status'] === 'Approved'): ?>
                    <form action="index.php?route=restock/update-status" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="Completed">

                        <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:6px; margin-bottom:14px; font-size:0.82rem;">
                            <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                                <input type="checkbox" name="auto_generate_sn" value="1" checked style="margin-top:2px;">
                                <span><strong>Auto-provision Serial Numbers:</strong> Automatically register physical In-Stock serial numbers for all line items at agreed unit costs.</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success" style="width: 100%; margin-bottom: 8px;">
                            ✓ Receive Stock & Complete Order
                        </button>
                    </form>
                    <form action="index.php?route=restock/update-status" method="POST" data-confirm="Cancel this approved order?">
                        <input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="Cancelled">
                        <button type="submit" class="btn btn-outline btn-sm" style="width: 100%; color: var(--danger);">
                            Cancel Order
                        </button>
                    </form>
                <?php elseif ($order['status'] === 'Completed'): ?>
                    <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; padding:12px; border-radius:6px; font-size:0.85rem;">
                        ✅ This transaction is finalized. Stock has been received into inventory.
                    </div>
                <?php else: ?>
                    <div style="background:#f1f5f9; color:#475569; padding:12px; border-radius:6px; font-size:0.85rem;">
                        This order has been cancelled.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Supplier Info -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Supplier Information</span>
            </div>
            <div class="card-body" style="font-size:0.85rem;">
                <p><strong>Company:</strong> <?= htmlspecialchars($order['supplier_name']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($order['supplier_contact']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['supplier_phone']) ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($order['supplier_email']) ?>" style="color:#2563eb;"><?= htmlspecialchars($order['supplier_email']) ?></a></p>
                <p style="margin-top:6px; color:var(--text-secondary);"><?= htmlspecialchars($order['supplier_address'] ?: '') ?></p>

                <?php if (!empty($order['notes'])): ?>
                    <div style="margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0;">
                        <strong>Order Notes:</strong>
                        <div style="color:var(--text-secondary); margin-top:2px;"><?= htmlspecialchars($order['notes']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
