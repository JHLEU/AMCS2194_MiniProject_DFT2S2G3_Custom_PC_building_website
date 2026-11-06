<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
            Hardware Supply Mapping: <?= htmlspecialchars($supplier['company_name']) ?>
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-secondary);">
            Contact: <strong><?= htmlspecialchars($supplier['contact_person']) ?></strong> &bull; <?= htmlspecialchars($supplier['phone']) ?> &bull; <?= htmlspecialchars($supplier['email']) ?>
        </p>
    </div>
    <div style="display:flex; gap: 8px;">
        <a href="index.php?route=restock/create&supplier_id=<?= (int)$supplier['id'] ?>" class="btn btn-primary">+ Create Restock Order</a>
        <a href="index.php?route=suppliers/index" class="btn btn-outline">&larr; Back to Suppliers</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px;">
    <!-- Active Mappings Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦 Currently Mapped Hardware Components</span>
            <span class="badge" style="background:#e0f2fe; color:#0369a1;"><?= count($mappedProducts) ?> Products</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($mappedProducts)): ?>
                <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                    No hardware products mapped yet. Use the form on the right to assign components this vendor can supply.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Supplier SKU</th>
                                <th>Agreed Cost</th>
                                <th>Catalog Cost</th>
                                <th>Lead Time</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappedProducts as $m): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($m['product_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #475569;">
                                            <?= htmlspecialchars($m['product_category']) ?>
                                        </span>
                                    </td>
                                    <td class="font-mono text-muted">
                                        <?= htmlspecialchars($m['supplier_sku'] ?: '—') ?>
                                    </td>
                                    <td class="font-mono" style="font-weight: 700; color: #2563eb;">
                                        $<?= number_format((float)$m['agreed_unit_price'], 2) ?>
                                    </td>
                                    <td class="font-mono text-muted">
                                        $<?= number_format((float)$m['catalog_cost_price'], 2) ?>
                                    </td>
                                    <td>
                                        <?= (int)$m['lead_time_days'] ?> day(s)
                                    </td>
                                    <td class="text-right">
                                        <form action="index.php?route=suppliers/remove-mapping" method="POST" style="display:inline-block;" data-confirm="Remove this hardware mapping?">
                                            <input type="hidden" name="supplier_id" value="<?= (int)$supplier['id'] ?>">
                                            <input type="hidden" name="product_id" value="<?= (int)$m['product_id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#fecaca;" title="Remove mapping">&times; Remove</button>
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

    <!-- Assign Hardware Form Card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">+ Assign New Hardware</span>
        </div>
        <div class="card-body">
            <?php if (empty($unmappedProducts)): ?>
                <div style="padding: 10px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                    All current catalog hardware items have already been assigned to this vendor.
                </div>
            <?php else: ?>
                <form action="index.php?route=suppliers/save-mapping" method="POST">
                    <input type="hidden" name="supplier_id" value="<?= (int)$supplier['id'] ?>">

                    <div class="form-group">
                        <label class="form-label">Select Hardware Product *</label>
                        <select name="product_id" class="form-select" id="mapping-product-select" required>
                            <option value="">-- Choose Product --</option>
                            <?php foreach ($unmappedProducts as $up): ?>
                                <option value="<?= (int)$up['id'] ?>" data-cost="<?= htmlspecialchars($up['cost_price']) ?>">
                                    <?= htmlspecialchars($up['name']) ?> (<?= htmlspecialchars($up['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vendor Specific SKU / Part #</label>
                        <input type="text" name="supplier_sku" class="form-control font-mono" placeholder="e.g. TD-CPU-AM5-7800">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Agreed Unit Purchase Price ($) *</label>
                        <input type="number" step="0.01" name="agreed_unit_price" id="mapping-price-input" class="form-control" placeholder="0.00" min="0.01" required>
                        <small class="text-muted">Contracted unit cost from this supplier.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Estimated Delivery Lead Time (Days) *</label>
                        <input type="number" name="lead_time_days" class="form-control" value="3" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                        Save Hardware Mapping
                    </button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const sel = document.getElementById('mapping-product-select');
                    const priceIn = document.getElementById('mapping-price-input');
                    if (sel && priceIn) {
                        sel.addEventListener('change', () => {
                            const opt = sel.options[sel.selectedIndex];
                            const defaultCost = opt.getAttribute('data-cost');
                            if (defaultCost) {
                                priceIn.value = parseFloat(defaultCost).toFixed(2);
                            }
                        });
                    }
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>
