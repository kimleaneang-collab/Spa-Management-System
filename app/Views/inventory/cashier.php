<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Cashier');
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-products-page" data-role="cashier">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">
                    <span class="brand-leaf">⌁</span>
                    <div class="brand-text"><div class="brand-name">Relax</div><div class="brand-subtitle">SPA</div></div>
                </div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS / New Sale</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard#recent-transactions"><span class="nav-icon">▧</span><span>Transactions</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=receipts"><span class="nav-icon">▤</span><span>Receipt</span></a>
                </div>
                <div class="nav-section" data-label="Quick Access">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Service</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=cashier-profile"><span class="nav-icon">◎</span><span>My Profile</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-actions cashier-products-topbar">
                    <label class="cashier-products-search">
                        <span aria-hidden="true">⌕</span>
                        <span class="visually-hidden">Search products</span>
                        <input type="search" placeholder="Search customer, appointments, services..." data-cashier-product-search>
                    </label>
                    <div class="cashier-products-tools" aria-hidden="true"><span>♧</span><span>▣</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $userInitials) ?>
                        <span class="cashier-products-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-products-content">
                <div class="cashier-products-heading">
                    <div><h1>Products</h1><p>Tiers, benefits, and current members.</p></div>
                    <button class="cashier-products-add" type="button" disabled aria-disabled="true" title="Products are managed by an administrator">＋ Add product</button>
                </div>

                <section class="cashier-products-panel" aria-label="Products managed by the administrator">
                    <div class="table-wrap">
                        <table class="cashier-products-table">
                            <thead>
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Size</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($products === []): ?>
                                    <tr><td class="cashier-products-empty" colspan="5">No products have been added yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                            $stock = (float) ($product['stock_quantity'] ?? 0);
                                            $isActive = (bool) ($product['active'] ?? false);
                                            $isLowStock = $isActive && $stock <= (float) ($product['reorder_level'] ?? 0);
                                            $statusLabel = !$isActive
                                                ? 'Inactive'
                                                : ($stock <= 0 ? 'Out of stock' : ($isLowStock ? 'Low stock' : 'In stock'));
                                            $statusClass = !$isActive
                                                ? 'inactive'
                                                : ($stock <= 0 ? 'out-of-stock' : ($isLowStock ? 'low-stock' : 'in-stock'));
                                            $stockDisplay = rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.');
                                        ?>
                                        <tr data-cashier-product-row>
                                            <td><?= e((string) $product['name']) ?></td>
                                            <td><?= e((string) $product['unit']) ?></td>
                                            <td><?= e((string) $product['price']) ?></td>
                                            <td><?= e($stockDisplay) ?></td>
                                            <td><span class="cashier-product-status <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr data-cashier-product-no-results hidden><td class="cashier-products-empty" colspan="5">No products match your search.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
