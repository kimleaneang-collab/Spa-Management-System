<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}
// Show a membership date in a short, readable format when it is valid.
$formatMembershipDate = static function (string $date): string {
    $timestamp = strtotime($date);

    return $timestamp === false ? $date : date('M j, Y', $timestamp);
};
// Choose the CSS class that matches the membership tier.
$tierTone = static function (string $tier): string {
    return match (strtolower($tier)) {
        'bronze' => 'bronze',
        'silver' => 'silver',
        'gold' => 'gold',
        'platinum' => 'platinum',
        default => 'default',
    };
};
$defaultStartDate = date('Y-m-d');
$defaultExpiryDate = date('Y-m-d', strtotime('+1 year -1 day'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Memberships | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page" data-role="<?= e(strtolower((string) ($_SESSION['user']['role'] ?? ''))) ?>">
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
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
                        <h1 class="page-title">MEMBERSHIPS</h1>
                        <div class="page-date">Membership tiers and active members</div>
                    </div>
                    <?php if ($isAdministrator): ?>
                        <button class="primary-button" type="button" data-open-modal="membership-modal">＋ New membership</button>
                    <?php endif; ?>
                </div>
                <?php if ($formSuccess !== ''): ?>
                    <div class="pos-alert success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>
                <section class="membership-layout">
                    <div class="service-grid">
                        <?php foreach ($membershipLevels as $level): ?>
                            <?php $tierToneClass = $tierTone((string) $level['name']); ?>
                            <article class="service-card membership-admin-tier <?= e($tierToneClass) ?>">
                                <div class="service-body">
                                    <div class="service-label"><?= e((string) $level['name']) ?></div>
                                    <h3><?= (float) $level['annual_fee'] > 0 ? e(number_format((float) $level['annual_fee'], 2)) . '<small>/year</small>' : 'No fee' ?></h3>
                                    <p><?= e(rtrim(rtrim(number_format((float) $level['discount_percent'], 2), '0'), '.')) ?>% auto-discount</p>
                                    <p><?= number_format((int) $level['active_members']) ?> active members · Loyalty points ×<?= e(rtrim(rtrim(number_format((float) $level['loyalty_points_multiplier'], 2), '0'), '.')) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                        <?php if ($membershipLevels === []): ?>
                            <p class="cashier-memberships-empty">No membership tiers have been configured.</p>
                        <?php endif; ?>
                    </div>
                    <div class="module-panel expiring-panel">
                        <strong>Active members</strong>
                        <table class="data-table">
                            <thead><tr><th>Membership no.</th><th>Customer</th><th>Tier</th><th>Joined</th><th>Expires</th></tr></thead>
                            <tbody>
                                <?php if ($members === []): ?>
                                    <tr><td colspan="5" class="cashier-members-empty">No active members yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?= e((string) $member['membership_no']) ?></td>
                                            <td><?= e((string) $member['full_name']) ?></td>
                                            <td><span class="cashier-member-tier <?= e($tierTone((string) $member['tier'])) ?>"><?= e((string) $member['tier']) ?></span></td>
                                            <td><?= e($formatMembershipDate((string) $member['start_date'])) ?></td>
                                            <td><?= e($formatMembershipDate((string) $member['expiry_date'])) ?></td>
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
    <?php if ($isAdministrator): ?>
        <div class="modal-backdrop <?= $showMembershipModal ? '' : 'hidden' ?>" id="membership-modal" aria-hidden="<?= $showMembershipModal ? 'false' : 'true' ?>">
            <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="membership-modal-title">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow">Customer enrollment</p>
                        <h2 id="membership-modal-title">New membership</h2>
                    </div>
                    <button class="modal-close" type="button" aria-label="Close" data-close-modal="membership-modal">×</button>
                </div>
                <?php if ($formError !== ''): ?>
                    <p class="pos-alert error" role="alert"><?= e($formError) ?></p>
                <?php endif; ?>
                <?php if ($customers === []): ?>
                    <p class="pos-alert error" role="status">Add an active customer before creating a membership.</p>
                <?php else: ?>
                    <form class="appointment-form" method="post" action="<?= e(APP_URL) ?>/?route=memberships">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">
                        <div class="form-grid two-col">
                            <label>
                                <span>Customer</span>
                                <select name="customer_id" required>
                                    <option value="">Select customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= e((string) $customer['id']) ?>" <?= (string) ($formValues['customer_id'] ?? '') === (string) $customer['id'] ? 'selected' : '' ?>>
                                            <?= e((string) $customer['full_name']) ?><?= trim((string) ($customer['phone'] ?? '')) !== '' ? ' · ' . e((string) $customer['phone']) : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Membership tier</span>
                                <select name="level_id" required>
                                    <option value="">Select tier</option>
                                    <?php foreach ($membershipLevels as $level): ?>
                                        <option value="<?= e((string) $level['id']) ?>" <?= (string) ($formValues['level_id'] ?? '') === (string) $level['id'] ? 'selected' : '' ?>>
                                            <?= e((string) $level['name']) ?> (<?= e(number_format((float) $level['discount_percent'], 2)) ?>% discount)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <span>Start date</span>
                                <input type="date" name="start_date" value="<?= e((string) ($formValues['start_date'] ?? $defaultStartDate)) ?>" required>
                            </label>
                            <label>
                                <span>Expiry date</span>
                                <input type="date" name="expiry_date" value="<?= e((string) ($formValues['expiry_date'] ?? $defaultExpiryDate)) ?>" required>
                            </label>
                        </div>
                        <p class="page-date">Defaults to a one-year membership. This enrolls the customer but does not record a payment.</p>
                        <div class="modal-actions">
                            <button class="secondary-button" type="button" data-close-modal="membership-modal">Cancel</button>
                            <button class="primary-button" type="submit">Add membership</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
