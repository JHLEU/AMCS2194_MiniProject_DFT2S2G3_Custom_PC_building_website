<div class="filter-bar">
    <input type="text" data-table-search="#suppliers-table" class="form-control" placeholder="Search suppliers by company name, contact person, or email..." style="max-width: 380px;">
    
    <div style="margin-left: auto;">
        <a href="index.php?route=suppliers/create" class="btn btn-primary">+ Register New Supplier</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏢 Supplier Directory & Verified Vendors</span>
        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= count($suppliers) ?> Registered Suppliers</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($suppliers)): ?>
            <div style="padding: 40px; text-align: center; color: var(--text-secondary);">
                <h3>No Suppliers Registered</h3>
                <p>Register suppliers to establish hardware mappings and issue restock purchase orders.</p>
                <a href="index.php?route=suppliers/create" class="btn btn-primary" style="margin-top: 10px;">+ Add Supplier</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="suppliers-table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address / Location</th>
                            <th>Mapped Hardware</th>
                            <th>Total Orders</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($s['company_name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($s['contact_person']) ?></td>
                                <td class="font-mono"><?= htmlspecialchars($s['phone']) ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($s['email']) ?>" style="color:var(--primary); text-decoration:none;">
                                        <?= htmlspecialchars($s['email']) ?>
                                    </a>
                                </td>
                                <td style="max-width: 200px; font-size: 0.8rem; color: var(--text-secondary);">
                                    <?= htmlspecialchars($s['address'] ?: '—') ?>
                                </td>
                                <td>
                                    <a href="index.php?route=suppliers/mapping&id=<?= (int)$s['id'] ?>" class="badge" style="background:#e0f2fe; color:#0369a1; text-decoration:none;">
                                        <?= (int)$s['mapped_products_count'] ?> products &rarr;
                                    </a>
                                </td>
                                <td>
                                    <span class="badge" style="background:#f1f5f9; color:#475569;">
                                        <?= (int)$s['total_orders_count'] ?> orders
                                    </span>
                                </td>
                                <td class="text-right" style="white-space: nowrap;">
                                    <a href="index.php?route=suppliers/mapping&id=<?= (int)$s['id'] ?>" class="btn btn-outline btn-sm" title="Manage hardware mapping">Hardware</a>
                                    <a href="index.php?route=restock/create&supplier_id=<?= (int)$s['id'] ?>" class="btn btn-primary btn-sm">+ Order</a>
                                    <a href="index.php?route=suppliers/edit&id=<?= (int)$s['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form action="index.php?route=suppliers/delete" method="POST" style="display:inline-block;" data-confirm="Delete supplier <?= htmlspecialchars($s['company_name']) ?>?">
                                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
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
