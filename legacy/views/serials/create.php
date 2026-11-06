<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Register Physical Serial Numbers</span>
        <a href="index.php?route=inventory/serials" class="btn btn-outline btn-sm">&larr; Back to SN Ledger</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=inventory/store-serial" method="POST">
            <div class="form-group">
                <label class="form-label">Target Hardware Component *</label>
                <select name="product_id" class="form-select" id="serial-product-select" required>
                    <option value="">-- Choose Product to Bind Serial Numbers --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" data-cost="<?= htmlspecialchars($p['cost_price']) ?>" <?= $preselectedProduct === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['category']) ?>) - Current In-Stock: <?= (int)$p['in_stock_count'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Batch Cost Price per Unit ($)</label>
                    <input type="number" step="0.01" name="batch_cost" id="batch-cost-input" class="form-control" placeholder="Defaults to catalog cost price">
                    <small class="text-muted">Used for exact batch inventory valuation.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Initial Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="In-Stock" selected>In-Stock (Available in Warehouse)</option>
                        <option value="Sold">Sold (Already assigned / shipped)</option>
                        <option value="RMA">RMA (Defective / Return Merchandise)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px;">
                <label class="form-label">Entry Mode</label>
                <div style="display:flex; gap: 20px; align-items:center; padding: 6px 0;">
                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <input type="radio" name="mode" value="single" checked> Single Serial Number
                    </label>
                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px;">
                        <input type="radio" name="mode" value="bulk"> Bulk Batch Entry (Multi-line)
                    </label>
                </div>
            </div>

            <!-- Single SN Section -->
            <div id="single-sn-section" class="form-group">
                <label class="form-label">Serial Number Code (SN) *</label>
                <input type="text" name="sn_code" class="form-control font-mono" placeholder="e.g. SN-7800X3D-98234 or barcode scan">
            </div>

            <!-- Bulk SN Section -->
            <div id="bulk-sn-section" class="form-group" style="display: none;">
                <label class="form-label">Batch Serial Numbers (Paste one code per line) *</label>
                <textarea name="bulk_sn_codes" class="form-control font-mono" rows="6" placeholder="SN-7800X3D-1001&#10;SN-7800X3D-1002&#10;SN-7800X3D-1003"></textarea>
                <small class="text-muted">Separate multiple serial numbers with line breaks or commas. Duplicate codes will be skipped.</small>
            </div>

            <div style="margin-top: 24px; display:flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=inventory/serials" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Serial Number(s)</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const prodSelect = document.getElementById('serial-product-select');
    const costInput = document.getElementById('batch-cost-input');
    if (prodSelect && costInput) {
        prodSelect.addEventListener('change', () => {
            const opt = prodSelect.options[prodSelect.selectedIndex];
            const defaultCost = opt.getAttribute('data-cost');
            if (defaultCost && !costInput.value) {
                costInput.value = parseFloat(defaultCost).toFixed(2);
            }
        });
    }
});
</script>
