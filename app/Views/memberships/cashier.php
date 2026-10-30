<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'Cashier');
$nameParts = preg_split('/\s+/', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$userInitials = $nameParts === []
    ? '?'
    : strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
$tierTone = static function (string $tier): string {
    return match (strtolower($tier)) {
        'bronze' => 'bronze',
        'silver' => 'silver',
        'gold' => 'gold',
        'platinum' => 'platinum',
        default => 'default',
    };
};
$formatMembershipDate = static function (string $date): string {
    $timestamp = strtotime($date);

    return $timestamp === false ? $date : date('M j, Y', $timestamp);
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Memberships | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-memberships-page" data-role="cashier">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=cashier-profile"><span class="nav-icon">◎</span><span>My Profile</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-actions cashier-memberships-topbar">
                    <label class="cashier-memberships-search">
                        <span aria-hidden="true">⌕</span>
                        <span class="visually-hidden">Search memberships</span>
                        <input type="search" placeholder="Search customer, appointments, services..." data-cashier-membership-search>
                    </label>
                    <div class="cashier-memberships-tools" aria-hidden="true"><span>♧</span><span>▣</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $userInitials) ?>
                        <span class="cashier-memberships-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-memberships-content">
                <div class="cashier-memberships-heading">
                    <div><h1>Memberships</h1><p>Tiers, benefits, and current members.</p></div>
                    <button class="cashier-memberships-add" type="button" disabled aria-disabled="true" title="Membership plans are managed by an administrator">＋ New membership</button>
                </div>

                <section class="cashier-membership-tiers" aria-label="Membership tiers">
                    <?php foreach ($membershipLevels as $level): ?>
                        <?php $tone = $tierTone((string) $level['name']); ?>
                        <article class="cashier-membership-tier <?= e($tone) ?>">
                            <h2><?= e((string) $level['name']) ?></h2>
                            <strong><?= number_format((int) $level['active_members']) ?></strong>
                            <span>active members</span>
                            <p><?= e(rtrim(rtrim(number_format((float) $level['discount_percent'], 2), '0'), '.')) ?>% discount</p>
                            <small>
                                <?= (float) $level['annual_fee'] > 0
                                    ? 'Annual fee: ' . number_format((float) $level['annual_fee'], 2)
                                    : 'Loyalty points ×' . e(rtrim(rtrim(number_format((float) $level['loyalty_points_multiplier'], 2), '0'), '.')) ?>
                            </small>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($membershipLevels === []): ?>
                        <p class="cashier-memberships-empty">No membership tiers have been configured.</p>
                    <?php endif; ?>
                </section>

                <section class="cashier-members-table-panel" aria-label="Current members">
                    <div class="table-wrap">
                        <table class="cashier-members-table">
                            <thead>
                                <tr><th scope="col">Members</th><th scope="col">Tier</th><th scope="col">Joined</th><th scope="col">Expires</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($members === []): ?>
                                    <tr><td class="cashier-members-empty" colspan="4">No active members yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($members as $member): ?>
                                        <?php
                                            $memberName = (string) $member['full_name'];
                                            $memberNameParts = preg_split('/\s+/', trim($memberName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                                            $memberInitials = $memberNameParts === []
                                                ? '?'
                                                : strtoupper(substr($memberNameParts[0], 0, 1) . substr($memberNameParts[count($memberNameParts) - 1], 0, 1));
                                        ?>
                                        <tr data-cashier-membership-row>
                                            <td><span class="cashier-member-identity"><span class="cashier-member-avatar"><?= e($memberInitials) ?></span><?= e($memberName) ?></span></td>
                                            <td><span class="cashier-member-tier <?= e($tierTone((string) $member['tier'])) ?>"><?= e((string) $member['tier']) ?></span></td>
                                            <td><?= e($formatMembershipDate((string) $member['start_date'])) ?></td>
                                            <td><?= e($formatMembershipDate((string) $member['expiry_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr data-cashier-membership-no-results hidden><td class="cashier-members-empty" colspan="4">No members match your search.</td></tr>
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
