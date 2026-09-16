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
    <title>Customers | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css">
</head>
<body class="dashboard-page">
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
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=appointments"><span class="nav-icon">☰</span><span>Appointments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customer-history"><span class="nav-icon">◍</span><span>Customer History</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=suppliers"><span class="nav-icon">◧</span><span>Supplier</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, Admin!</div></div>
                <div class="topbar-actions">
                    <div class="search-box search-input-wrap">
                        <span class="search-icon">⌕</span>
                        <input type="search" class="search-input" placeholder="Search" aria-label="Search">
                    </div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <a class="icon-button add-button" href="<?= e(APP_URL) ?>/?route=customers#add-customer" aria-label="Add customer" title="Add customer">＋</a>
                    <div class="user-chip">
                        <span class="user-name">SV</span>
                        <span class="user-role">Admin</span>
                        <form method="post" action="<?= e(APP_URL) ?>/?route=logout" class="logout-form">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button class="logout-link" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">CUSTOMERS</h1>
                        <div class="page-date">Customer directory</div>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="customer-modal">+ Add customer</button>
                </div>
                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table searchable-table">
                            <thead>
                                <tr><th>Customer</th><th>Phone</th><th>Visits</th><th>Total spent</th><th>Membership</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($customers === []): ?>
                                    <tr><td colspan="6">No customers available.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?= e($customer['full_name'] ?? '') ?></td>
                                            <td><?= e($customer['phone'] ?? '') ?></td>
                                            <td><?= e((string) ($customer['visits'] ?? 0)) ?></td>
                                            <td>$<?= e(number_format((float) ($customer['total_spent'] ?? 0), 2)) ?></td>
                                            <td><span class="status-pill status-confirmed"><?= e($customer['membership'] ?? 'None') ?></span></td>
                                            <td>
                                                <a class="secondary-button" href="<?= e(APP_URL) ?>/?route=customers&amp;edit=<?= e((string) ($customer['id'] ?? 0)) ?>">Edit</a>
                                                <form method="post" action="<?= e(APP_URL) ?>/?route=customers" style="display:inline" onsubmit="return confirm('Delete this customer?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= e((string) ($customer['id'] ?? 0)) ?>">
                                                    <button class="secondary-button" type="submit">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop <?= $editingCustomer !== null ? '' : 'hidden' ?>" id="customer-modal" aria-hidden="<?= $editingCustomer !== null ? 'false' : 'true' ?>">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">Customer</p>
                    <h2><?= $editingCustomer !== null ? 'Edit customer' : 'Add customer' ?></h2>
                </div>
                <button class="modal-close" type="button" data-close-modal="customer-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=customers">
                <input type="hidden" name="action" value="<?= $editingCustomer !== null ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= e((string) ($editingCustomer['id'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label>
                        <span>Name</span>
                        <input type="text" name="customer_name" value="<?= e($editingCustomer['full_name'] ?? '') ?>" minlength="2" maxlength="150" required>
                    </label>
                    <label>
                        <span>Phone</span>
                        <input type="tel" name="customer_phone" value="<?= e($editingCustomer['phone'] ?? '') ?>" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required>
                    </label>
                    <label>
                        <span>Email</span>
                        <input type="email" name="customer_email" value="<?= e($editingCustomer['email'] ?? '') ?>" maxlength="150">
                    </label>
                    <label>
                        <span>Membership</span>
                        <select name="customer_membership">
                            <option value="">Select</option>
                            <option>Silver</option>
                            <option>Gold</option>
                            <option>Platinum</option>
                        </select>
                    </label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="customer-modal">Cancel</button>
                    <button class="primary-button" type="submit"><?= $editingCustomer !== null ? 'Update customer' : 'Save customer' ?></button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
