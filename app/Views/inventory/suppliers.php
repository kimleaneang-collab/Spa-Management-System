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
    <title>Suppliers | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
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
                        <h1 class="page-title">SUPPLIERS</h1>
                        <div class="page-date">Vendor list</div>
                    </div>
                    <?php if ($canManageInventory): ?>
                        <button class="primary-button" type="button" data-open-modal="supplier-modal">+ Add supplier</button>
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
                                <tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Email</th><th>Products</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><?= e((string) $supplier['name']) ?><br><small><?= e((string) $supplier['supplier_code']) ?></small></td>
                                        <td><?= e((string) ($supplier['contact_person'] ?? '-')) ?></td>
                                        <td><?= e((string) ($supplier['phone'] ?? '-')) ?></td>
                                        <td><?= e((string) ($supplier['email'] ?? '-')) ?></td>
                                        <td><?= e((string) ($supplier['products'] ?? '') ?: '-') ?></td>
                                        <td><span class="status-pill <?= $supplier['status'] === 'active' ? 'status-confirmed' : 'status-pending' ?>"><?= e(ucfirst((string) $supplier['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if ($suppliers === []): ?>
                                    <tr><td colspan="6">No suppliers have been added.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <?php if ($canManageInventory): ?>
        <div class="modal-backdrop <?= $formError !== '' ? '' : 'hidden' ?>" id="supplier-modal" aria-hidden="<?= $formError !== '' ? 'false' : 'true' ?>">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="supplier-title">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow">Inventory</p>
                        <h2 id="supplier-title">Add supplier</h2>
                    </div>
                    <button class="modal-close" type="button" aria-label="Close" data-close-modal="supplier-modal">×</button>
                </div>
                <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=suppliers">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create_supplier">
                    <div class="form-grid two-col">
                        <label>
                            <span>Supplier code</span>
                            <input type="text" name="supplier_code" maxlength="40" required value="<?= e((string) ($formValues['supplier_code'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Supplier name</span>
                            <input type="text" name="supplier_name" maxlength="150" required value="<?= e((string) ($formValues['supplier_name'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Contact person (optional)</span>
                            <input type="text" name="contact_person" maxlength="150" value="<?= e((string) ($formValues['contact_person'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Phone (optional)</span>
                            <input type="tel" name="phone" maxlength="30" value="<?= e((string) ($formValues['phone'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Email (optional)</span>
                            <input type="email" name="email" maxlength="150" value="<?= e((string) ($formValues['email'] ?? '')) ?>">
                        </label>
                        <label>
                            <span>Address (optional)</span>
                            <input type="text" name="address" maxlength="255" value="<?= e((string) ($formValues['address'] ?? '')) ?>">
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button class="primary-button" type="submit">Save supplier</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
