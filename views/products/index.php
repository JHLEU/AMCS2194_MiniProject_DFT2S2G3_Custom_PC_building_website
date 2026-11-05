<div class="filter-bar">
    <input type="text" data-table-search="#products-table" class="form-control" placeholder="Search catalog by name, category, or specs..." style="max-width: 380px;">
    
    <div style="margin-left: auto; display: flex; gap: 8px;">
        <a href="index.php?route=products/create" class="btn btn-primary">+ Add New Hardware</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">💻 Hardware Catalog & Compatibility Specifications</span>
        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($products) ?> Total Products</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($products)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <h3>No Products Found</h3>
                <p>Start by adding hardware items to your catalog.</p>
                <a href="index.php?route=products/create" class="btn btn-primary" style="margin-top: 10px;">+ Add Product</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Compatibility Specs</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock Levels</th>
                            <th>Low-Stock Alert</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="font-mono text-muted">#<?= (int)$p['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge" style="background: #f1f5f9; color: #334155;">
                                        <?= htmlspecialchars($p['category']) ?>
                                    </span>
                                </td>
                                <td style="max-width: 260px; font-size: 0.8rem; color: var(--text-secondary);">
                                    <?= htmlspecialchars($p['specs'] ?: '—') ?>
                                </td>
                                <td class="font-mono">$<?= number_format((float)$p['cost_price'], 2) ?></td>
                                <td class="font-mono">$<?= number_format((float)$p['selling_price'], 2) ?></td>
                                <td>
                                    <div style="display:flex; gap:4px; font-size:0.75rem;">
                                        <span class="badge badge-instock" title="In-Stock Units"><?= (int)$p['in_stock_count'] ?> In</span>
                                        <span class="badge badge-sold" title="Sold Units"><?= (int)$p['sold_count'] ?> Sold</span>
                                        <?php if ((int)$p['rma_count'] > 0): ?>
                                            <span class="badge badge-rma" title="RMA Units"><?= (int)$p['rma_count'] ?> RMA</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ((int)$p['is_low_stock']): ?>
                                        <span class="badge badge-lowstock">
                                            ⚠️ Low (≤ <?= (int)$p['low_stock_threshold'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#e0e7ff; color:#3730a3;">
                                            OK (Min: <?= (int)$p['low_stock_threshold'] ?>)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="index.php?route=products/edit&id=<?= (int)$p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                    <a href="index.php?route=inventory/create-serial&product_id=<?= (int)$p['id'] ?>" class="btn btn-secondary btn-sm">+ SN</a>
                                    <form action="index.php?route=products/delete" method="POST" style="display:inline-block;" data-confirm="Are you sure you want to delete this hardware product?">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#fecaca;">Delete</button>
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
