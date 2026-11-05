<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Edit Product: <?= htmlspecialchars($product['name']) ?></span>
        <a href="index.php?route=products/index" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=products/update" method="POST">
            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Hardware Product Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $cat === $product['category'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Low-Stock Safety Threshold *</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="<?= (int)$product['low_stock_threshold'] ?>" min="1" required>
                    <small class="text-muted">Current In-Stock SN Count: <strong><?= (int)$product['in_stock_count'] ?></strong></small>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Cost Price ($) *</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= htmlspecialchars($product['cost_price']) ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Standard Selling Price ($) *</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= htmlspecialchars($product['selling_price']) ?>" min="0" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Compatibility & Technical Parameters</label>
                    <textarea name="specs" class="form-control" rows="3"><?= htmlspecialchars($product['specs'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=products/index" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Linked Suppliers for this Product -->
<div class="card" style="max-width: 800px; margin: 24px auto 0;">
    <div class="card-header">
        <span class="card-title">🏢 Qualified Suppliers for this Component</span>
        <a href="index.php?route=suppliers/index" class="btn btn-outline btn-sm">Manage Suppliers</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($suppliers)): ?>
            <div style="padding: 20px; text-align: center; color: var(--text-muted);">
                No suppliers have been mapped to this hardware product yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier Company</th>
                            <th>Contact</th>
                            <th>Supplier SKU</th>
                            <th>Agreed Cost</th>
                            <th>Lead Time</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['company_name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['contact_person']) ?> (<?= htmlspecialchars($s['phone']) ?>)</td>
                                <td class="font-mono"><?= htmlspecialchars($s['supplier_sku'] ?: '—') ?></td>
                                <td class="font-mono">$<?= number_format((float)$s['agreed_unit_price'], 2) ?></td>
                                <td><?= (int)$s['lead_time_days'] ?> days</td>
                                <td class="text-right">
                                    <a href="index.php?route=restock/create&supplier_id=<?= (int)$s['supplier_id'] ?>&product_id=<?= (int)$product['id'] ?>" class="btn btn-warning btn-sm">
                                        Restock Order
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
