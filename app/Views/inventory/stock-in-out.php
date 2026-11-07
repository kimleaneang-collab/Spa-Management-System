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
    <title>Stock Management | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'SV'), 0, 2))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span><span class="user-chevron">▾</span></div>
                </div>
            </header>
            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">STOCK MANAGEMENT</h1>
                        <div class="page-date">Incoming and outgoing inventory</div>
                    </div>
                    <?php if ($canManageInventory): ?>
                        <button class="primary-button" type="button" data-open-modal="stock-modal">+ Add stock</button>
                    <?php endif; ?>
                </div>
                <?php if ($successMessage !== ''): ?>
                    <div class="employee-management-message success" role="status"><?= e($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($formError !== ''): ?>
                    <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr><th>Product</th><th>Type</th><th>Qty</th><th>Date</th><th>Note</th><th>Recorded by</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr>
                                        <td><?= e((string) $transaction['product_name']) ?></td>
                                        <td><?= e(ucwords(str_replace('_', ' ', (string) $transaction['transaction_type']))) ?></td>
                                        <td><?= e(rtrim(rtrim(number_format((float) $transaction['quantity'], 3, '.', ''), '0'), '.') . ' ' . (string) $transaction['unit']) ?></td>
                                        <td><?= e(date('M j, Y', strtotime((string) $transaction['created_at']))) ?></td>
                                        <td><?= e((string) ($transaction['reason'] ?? '-')) ?></td>
                                        <td><?= e((string) ($transaction['created_by_name'] ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($transactions === []): ?>
                                    <tr><td colspan="6">No stock movements have been recorded.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <?php if ($canManageInventory): ?>
        <div class="modal-backdrop <?= $formError !== '' ? '' : 'hidden' ?>" id="stock-modal" aria-hidden="<?= $formError !== '' ? 'false' : 'true' ?>">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="stock-title">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow">Inventory</p>
                        <h2 id="stock-title">Record stock movement</h2>
                    </div>
                    <button class="modal-close" type="button" aria-label="Close" data-close-modal="stock-modal">×</button>
                </div>
                <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=stock-management">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="record_stock_movement">
                    <div class="form-grid two-col">
                        <label>
                            <span>Product</span>
                            <select name="product_id" required>
                                <option value="">Select product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= e((string) $product['id']) ?>" <?= (string) ($formValues['product_id'] ?? '') === (string) $product['id'] ? 'selected' : '' ?>>
                                        <?= e((string) $product['name'] . ' (' . (string) $product['product_code'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Movement type</span>
                            <select name="transaction_type" required>
                                <option value="stock_in" <?= ($formValues['transaction_type'] ?? 'stock_in') === 'stock_in' ? 'selected' : '' ?>>Stock in</option>
                                <option value="stock_out" <?= ($formValues['transaction_type'] ?? '') === 'stock_out' ? 'selected' : '' ?>>Stock out</option>
                            </select>
                        </label>
                        <label>
                            <span>Quantity</span>
                            <input type="number" name="quantity" min="0.001" step="0.001" required value="<?= e((string) ($formValues['quantity'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Unit cost (optional)</span>
                            <input type="number" name="unit_cost" min="0" step="0.01" value="<?= e((string) ($formValues['unit_cost'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Note (optional)</span>
                            <input type="text" name="reason" maxlength="255" value="<?= e((string) ($formValues['reason'] ?? '')) ?>">
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button class="primary-button" type="submit" <?= $products === [] ? 'disabled title="Add a product first"' : '' ?>>Save stock movement</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
