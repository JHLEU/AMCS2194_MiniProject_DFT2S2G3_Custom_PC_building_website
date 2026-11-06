<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title">Register New Vendor / Supplier Profile</span>
        <a href="index.php?route=suppliers/index" class="btn btn-outline btn-sm">&larr; Back to Suppliers</a>
    </div>
    <div class="card-body">
        <form action="index.php?route=suppliers/store" method="POST">
            <div class="form-grid">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Company Name *</label>
                    <input type="text" name="company_name" class="form-control" placeholder="e.g. TechData Distribution or ASUS APAC Direct" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact Person Name *</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="e.g. Sarah Tan" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" placeholder="e.g. +60 12-345 6789" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="orders@supplier-domain.com" required>
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label class="form-label">Physical Warehouse / Office Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Full address, city, state, postal code"></textarea>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="index.php?route=suppliers/index" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Supplier Profile</button>
            </div>
        </form>
    </div>
</div>
