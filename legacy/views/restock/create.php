<!-- Hidden template select for fallback -->
<select id="global-products-template" style="display:none;">
    <option value="">-- Choose Hardware Product --</option>
    <?php foreach ($allProducts as $p): ?>
        <option value="<?= (int)$p['id'] ?>" data-price="<?= htmlspecialchars($p['cost_price']) ?>">
            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category']) ?>) - Catalog: $<?= htmlspecialchars($p['cost_price']) ?>
        </option>
    <?php endforeach; ?>
</select>

<div class="card" style="max-width: 960px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Draft New Restock Purchase Order</span>
        <a href="index.php?route=restock/index" class="btn btn-outline btn-sm">&larr; Back to Orders</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=restock/store" method="POST" id="restock-order-form">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Vendor / Supplier *</label>
                    <select name="supplier_id" id="restock-supplier-select" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" <?= $preselectedSupplier === (int)$s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['company_name']) ?> (<?= (int)$s['mapped_products_count'] ?> mapped products)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Order Date *</label>
                    <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="expected_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Order Notes / Purchase Instructions</label>
                    <input type="text" name="notes" class="form-control" placeholder="e.g. Urgent restock for custom builds">
                </div>
            </div>

            <!-- Line Items Table -->
            <div style="margin-top: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
                    <h3 style="font-size: 1rem; font-weight:700;">Purchase Order Line Items</h3>
                    <button type="button" id="add-restock-item-btn" class="btn btn-secondary btn-sm">+ Add Item Row</button>
                </div>

                <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hardware Product</th>
                                <th style="width: 130px;">Quantity</th>
                                <th style="width: 160px;">Unit Cost ($)</th>
                                <th style="width: 160px;">Line Subtotal</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="restock-items-table-body">
                            <!-- Populated dynamically via JS or initialized with 1 row -->
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8fafc; font-weight:700;">
                                <td colspan="3" class="text-right" style="padding:14px;">Grand Total Purchase Cost:</td>
                                <td class="font-mono" style="font-size: 1.1rem; color: #2563eb; padding:14px;">
                                    $<span id="restock-grand-total">0.00</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div style="margin-top: 24px; display:flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=restock/index" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Purchase Request</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Automatically trigger initial row
    const addBtn = document.getElementById('add-restock-item-btn');
    if (addBtn) {
        addBtn.click();
    }
});
</script>
