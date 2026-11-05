        <!-- Top Navbar -->
        <header class="top-navbar">
            <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Custom PC ERP') ?></h1>
            <div class="top-actions">
                <a href="index.php?route=products/create" class="btn btn-outline btn-sm">+ Hardware</a>
                <a href="index.php?route=inventory/create-serial" class="btn btn-outline btn-sm">+ Register SN</a>
                <a href="index.php?route=restock/create" class="btn btn-primary btn-sm">+ Restock Order</a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="content-body">
            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?>">
                    <span><?= htmlspecialchars($flash['message'] ?? '') ?></span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:inherit;">&times;</button>
                </div>
            <?php endif; ?>
