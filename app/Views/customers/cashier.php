<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Cashier');
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$initials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$currencySymbol = match (strtoupper($currency)) {
    'USD' => '$',
    'KHR' => '៛',
    default => $currency . ' ',
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customers | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-customers-page" data-role="cashier">
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
                    <a class="nav-item" href="#recent-transactions"><span class="nav-icon">▧</span><span>Transactions</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>Customers</span></a>
                    <a class="nav-item" href="#recent-transactions"><span class="nav-icon">▤</span><span>Receipt</span></a>
                </div>
                <div class="nav-section" data-label="Quick Access">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
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
                <div class="topbar-actions cashier-customers-topbar">
                    <div class="search-box"><span class="search-icon" aria-hidden="true">⌕</span><span>Search</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span class="cashier-customers-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-customers-content">
                <div class="cashier-customers-heading">
                    <div>
                        <h1 class="page-title">Customers</h1>
                        <p><?= number_format(count($customers)) ?> registered customers</p>
                    </div>
                    <button class="primary-button" type="button" data-open-modal="cashier-add-customer-modal">＋ Add customer</button>
                </div>

                <section class="cashier-customers-panel" aria-label="Customer directory">
                    <div class="cashier-customers-toolbar">
                        <label class="cashier-customers-search">
                            <span aria-hidden="true">⌕</span>
                            <span class="visually-hidden">Search customers by name or phone</span>
                            <input type="search" placeholder="Search customers..." data-cashier-customer-search>
                        </label>
                        <span class="cashier-customers-count"><?= number_format(count($customers)) ?> customers</span>
                    </div>
                    <div class="table-wrap">
                        <table class="cashier-customers-table">
                            <thead>
                                <tr><th>Customers</th><th>Phone</th><th>Membership</th><th>Total Visits</th><th>Total Spent</th><th>View</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($customers === []): ?>
                                    <tr><td class="cashier-customers-empty" colspan="6">No customers registered yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <?php
                                            $customerName = (string) ($customer['full_name'] ?? '');
                                            $customerNameParts = preg_split('/\s+/', trim($customerName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                            $customerInitials = $customerNameParts === []
                                                ? '?'
                                                : strtoupper(substr($customerNameParts[0], 0, 1) . substr($customerNameParts[count($customerNameParts) - 1], 0, 1));
                                            $membershipName = (string) ($customer['membership'] ?? 'None');
                                            $hasMembership = $membershipName !== '' && strtolower($membershipName) !== 'none';
                                        ?>
                                        <tr data-cashier-customer-row>
                                            <td>
                                                <span class="cashier-customer-identity">
                                                    <span class="cashier-customer-avatar"><?= e($customerInitials) ?></span>
                                                    <strong><?= e($customerName) ?></strong>
                                                </span>
                                            </td>
                                            <td><?= e((string) ($customer['phone'] ?? '')) ?></td>
                                            <td><span class="cashier-membership-badge<?= $hasMembership ? ' is-member' : '' ?>"><?= e($hasMembership ? $membershipName . ' Member' : 'Regular') ?></span></td>
                                            <td><?= number_format((int) ($customer['visits'] ?? 0)) ?></td>
                                            <td><?= e($currencySymbol) . e(number_format((float) ($customer['total_spent'] ?? 0), 2)) ?></td>
                                            <td><button class="cashier-customer-view" type="button"
                                                data-cashier-customer-view
                                                data-name="<?= e($customerName) ?>"
                                                data-phone="<?= e((string) ($customer['phone'] ?? '')) ?>"
                                                data-membership="<?= e($hasMembership ? $membershipName . ' Member' : 'Regular') ?>"
                                                data-visits="<?= e((string) ($customer['visits'] ?? 0)) ?>"
                                                data-spent="<?= e(number_format((float) ($customer['total_spent'] ?? 0), 2)) ?>"
                                                data-currency="<?= e($currencySymbol) ?>">View</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr data-cashier-customer-no-results hidden><td class="cashier-customers-empty" colspan="6">No customers match your search.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="cashier-add-customer-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="cashier-add-customer-title">
            <div class="modal-header">
                <div><p class="eyebrow">Customer</p><h2 id="cashier-add-customer-title">Add customer</h2></div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="cashier-add-customer-modal">×</button>
            </div>
            <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=customers">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?= e((string) ($_SESSION['csrf_token'] ?? '')) ?>">
                <div class="form-grid two-col">
                    <label><span>Name</span><input type="text" name="customer_name" minlength="2" maxlength="150" required></label>
                    <label><span>Phone</span><input type="tel" name="customer_phone" pattern="[0-9+()\-\s]{7,30}" maxlength="30" required></label>
                    <label><span>Email</span><input type="email" name="customer_email" maxlength="150"></label>
                </div>
                <div class="modal-actions">
                    <button class="secondary-button" type="button" data-close-modal="cashier-add-customer-modal">Cancel</button>
                    <button class="primary-button" type="submit">Save customer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop hidden" id="cashier-customer-details-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="cashier-customer-details-title">
            <div class="modal-header">
                <div><p class="eyebrow">Customer details</p><h2 id="cashier-customer-details-title" data-cashier-detail="name"></h2></div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="cashier-customer-details-modal">×</button>
            </div>
            <dl class="cashier-customer-details">
                <div><dt>Phone</dt><dd data-cashier-detail="phone"></dd></div>
                <div><dt>Membership</dt><dd data-cashier-detail="membership"></dd></div>
                <div><dt>Total visits</dt><dd data-cashier-detail="visits"></dd></div>
                <div><dt>Total spent</dt><dd><span data-cashier-detail="currency"></span><span data-cashier-detail="spent"></span></dd></div>
            </dl>
        </div>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
