<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Register New Hardware Product</span>
        <a href="index.php?route=products/index" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=products/store" method="POST">
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Hardware Product Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., AMD Ryzen 7 7800X3D or ASUS ROG STRIX B650-A" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Low-Stock Safety Threshold *</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="5" min="1" required>
                    <small class="text-muted">Triggers low-stock warning when In-Stock SN count falls to or below this number.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Cost Price ($) *</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Standard Selling Price ($) *</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" min="0" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Compatibility & Technical Parameters</label>
                    <textarea name="specs" class="form-control" rows="3" placeholder="e.g. Socket: AM5, Form Factor: ATX, Wattage: 120W, PCIe: 5.0 x16, RAM: DDR5 6000MHz"></textarea>
                    <small class="text-muted">Enter key compatibility parameters used by the PC Builder engine (socket, form factor, TDP wattage, RAM standard, slot clearance).</small>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=products/index" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>
