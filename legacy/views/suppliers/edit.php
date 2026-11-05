<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Edit Supplier: <?= htmlspecialchars($supplier['company_name']) ?></span>
        <a href="index.php?route=suppliers/index" class="btn btn-outline btn-sm">&larr; Back to Suppliers</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=suppliers/update" method="POST">
            <input type="hidden" name="id" value="<?= (int)$supplier['id'] ?>">

            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Company Name *</label>
                    <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($supplier['company_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Person Name *</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($supplier['contact_person']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($supplier['email']) ?>" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Physical Warehouse / Office Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=suppliers/mapping&id=<?= (int)$supplier['id'] ?>" class="btn btn-outline">Manage Hardware Mapping</a>
                <a href="index.php?route=suppliers/index" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>
