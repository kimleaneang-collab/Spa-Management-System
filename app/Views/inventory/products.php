<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page inventory-page <?= strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'manager' ? 'manager-role' : '' ?>" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text">
                        <div class="brand-name">Relax</div>
                        <div class="brand-subtitle">SPA</div>
                    </div>
                </div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section" data-label="Quick Actions">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Inventory</span></a>
                </div>
                <div class="nav-section" data-label="Reports">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <div class="sidebar-role">
                <span>Current Role</span>
                <div class="role-select">
                    <?= profile_avatar($_SESSION['user'] ?? [], 'role-avatar', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'M'), 0, 1))) ?>
                    <span><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'staff'))) ?></span>
                    <span class="chevron">▾</span>
                </div>
            </div>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                </div>
                <div class="topbar-actions">
                    <button class="icon-button inventory-topbar-icon" type="button" aria-label="Notifications">♧</button>
                    <a class="icon-button inventory-topbar-icon" href="<?= e(APP_URL) ?>/?route=appointments" aria-label="Create appointment">▣</a>
                    <div class="user-chip">
                        <?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'M'), 0, 2))) ?>
                        <span class="user-identity">
                            <strong><?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User')) ?></strong>
                            <small><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></small>
                        </span>
                        <span class="user-chevron">⌄</span>
                    </div>
                </div>
            </header>
            <div class="content-wrap inventory-content">
                <div class="inventory-page-heading">
                    <div>
                        <h1 class="page-title">INVENTORY</h1>
                        <p class="inventory-summary">Track products and supply stock levels.</p>
                    </div>
                    <button class="primary-button inventory-add-button" type="button" data-open-modal="product-modal"><span>＋</span> Add Item</button>
                </div>
                <div class="inventory-filters">
                    <label class="inventory-search" for="inventory-search">
                        <span class="search-icon">⌕</span>
                        <input id="inventory-search" type="search" placeholder="Search inventory" autocomplete="off">
                    </label>
                    <label class="inventory-status-filter" for="inventory-status">
                        <select id="inventory-status" aria-label="Filter inventory by status">
                            <option value="all">Status: All</option>
                            <option value="in stock">In Stock</option>
                            <option value="reorder soon">Reorder Soon</option>
                            <option value="low stock">Low Stock</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label>
                </div>
                <section class="inventory-table-panel" aria-label="Inventory stock levels">
                    <div class="inventory-table-wrap">
                    <table class="inventory-table" id="inventory-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Stock Level</th>
                                <th>Reorder At</th>
                                <th>Unit Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr data-search="<?= e(strtolower((string) $product['name'] . ' ' . (string) $product['category'] . ' ' . (string) $product['code'])) ?>" data-status="<?= e(strtolower((string) $product['status'])) ?>">
                            <td class="inventory-item-name"><?= e((string) $product['name']) ?></td>
                            <td><?= e((string) $product['category']) ?></td>
                            <td>
                                <div class="inventory-stock-cell">
                                    <span class="inventory-stock-meter <?= e($product['status_class']) ?>" aria-hidden="true"><span style="width: <?= e(number_format((float) $product['stock_percent'], 1, '.', '')) ?>%"></span></span>
                                    <span><?= e((string) $product['stock_display']) ?></span>
                                </div>
                            </td>
                            <td><?= e((string) $product['reorder_display']) ?></td>
                            <td><?= e((string) $product['unit_cost']) ?></td>
                            <td><span class="inventory-status-pill <?= e($product['status_class']) ?>"><?= e((string) $product['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <p class="inventory-empty" id="inventory-empty" <?= $products === [] ? '' : 'hidden' ?>>No inventory items match your filter.</p>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="product-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="product-title">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Inventory</p>
                    <h2 id="product-title">Add product</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="product-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=products" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="form-grid two-col">
                    <label>
                        <span>Product code</span>
                        <input type="text" name="product_code" placeholder="PROD-001" minlength="2" maxlength="40" required>
                    </label>
                    <label>
                        <span>Product name</span>
                        <input type="text" name="product_name" placeholder="Massage Oil 100ml" minlength="2" maxlength="150" required>
                    </label>
                    <label>
                        <span>Category</span>
                        <input type="text" name="category" placeholder="Spa supplies">
                    </label>
                    <label>
                        <span>Unit</span>
                        <input type="text" name="unit" value="pcs" required>
                    </label>
                    <label>
                        <span>Cost price</span>
                        <input type="number" name="cost_price" min="0" step="0.01" value="0">
                    </label>
                    <label>
                        <span>Selling price</span>
                        <input type="number" name="selling_price" min="0" step="0.01" value="0" required>
                    </label>
                    <label>
                        <span>Stock quantity</span>
                        <input type="number" name="stock_quantity" min="0" step="0.001" value="0" required>
                    </label>
                    <label>
                        <span>Reorder level</span>
                        <input type="number" name="reorder_level" min="0" step="0.001" value="0" required>
                    </label>
                    <label>
                        <span>Expiry date</span>
                        <input type="date" name="expiry_date">
                    </label>
                    <label>
                        <span>Product picture</span>
                        <input type="file" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    </label>
                </div>
                <p class="page-date">Accepted: JPG, PNG, GIF, or WEBP. Maximum 5 MB.</p>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="product-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save product</button>
                </div>
            </form>
        </div>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
