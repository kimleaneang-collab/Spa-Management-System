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
// Format an amount with the cashier's currency symbol.
$money = static fn (float $amount): string => e($currencySymbol) . number_format($amount, 2);
// Map each payment status to the CSS class used for its display.
$statusClass = static fn (string $status): string => match (strtolower($status)) {
    'paid' => 'paid',
    'partial' => 'partial',
    'refunded' => 'refunded',
    default => 'pending',
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cashier Dashboard | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page" data-role="cashier">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS / New Sale</span></a>
                    <a class="nav-item" href="#recent-transactions"><span class="nav-icon">▧</span><span>Transactions</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>Customers</span></a>
                    <a class="nav-item" href="#recent-transactions"><span class="nav-icon">▤</span><span>Receipt</span></a>
                </div>
                <div class="nav-section" data-label="Quick Access">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Service</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=memberships"><span class="nav-icon">◌</span><span>Memberships</span></a>
                </div>
                <div class="nav-section" data-label="Settings">
                    <a class="nav-item" href="#cashier-profile"><span class="nav-icon">◎</span><span>My Profile</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>

        <main class="main-panel">
            <header class="topbar cashier-topbar">
                <div class="cashier-topbar-title">Welcome back, <?= e($userName) ?>!</div>
                <div class="topbar-actions">
                    <span class="cashier-topbar-date"><?= e(date('D, M j, Y')) ?></span>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span class="user-role">Cashier</span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-dashboard-content">
                <div class="cashier-dashboard-heading">
                    <div>
                        <span class="cashier-eyebrow">OVERVIEW</span>
                        <h1>Cashier Dashboard</h1>
                        <p>Here’s your sales and payment activity for today.</p>
                    </div>
                    <a class="cashier-new-sale" href="<?= e(APP_URL) ?>/?route=pos"><span aria-hidden="true">＋</span> New Sale</a>
                </div>

                <section class="cashier-stat-grid" aria-label="Cashier sales overview">
                    <article class="cashier-stat-card">
                        <span class="cashier-stat-icon sales">＄</span>
                        <span class="cashier-stat-label">Today's Sales</span>
                        <strong><?= $money($todaySales) ?></strong>
                        <small>Payments received today</small>
                    </article>
                    <article class="cashier-stat-card">
                        <span class="cashier-stat-icon transactions">▧</span>
                        <span class="cashier-stat-label">Transactions</span>
                        <strong><?= number_format($transactionCount) ?></strong>
                        <small>Payments processed today</small>
                    </article>
                    <article class="cashier-stat-card">
                        <span class="cashier-stat-icon average">◒</span>
                        <span class="cashier-stat-label">Average Order Value</span>
                        <strong><?= $money($averageOrderValue) ?></strong>
                        <small>Per transaction today</small>
                    </article>
                    <article class="cashier-stat-card">
                        <span class="cashier-stat-icon pending">◷</span>
                        <span class="cashier-stat-label">Pending Payments</span>
                        <strong><?= number_format($pendingPayments) ?></strong>
                        <small>Invoices awaiting payment</small>
                    </article>
                    <article class="cashier-stat-card">
                        <span class="cashier-stat-icon membership">◇</span>
                        <span class="cashier-stat-label">Membership Used</span>
                        <strong><?= number_format($membershipsUsed) ?></strong>
                        <small>Used in today's transactions</small>
                    </article>
                </section>

                <section class="cashier-transactions-panel" id="recent-transactions" aria-labelledby="cashier-transactions-title">
                    <div class="cashier-transactions-heading">
                        <div>
                            <h2 id="cashier-transactions-title">Recent Transactions</h2>
                            <p>Your latest recorded customer payments</p>
                        </div>
                        <span class="cashier-transaction-count"><?= number_format(count($recentTransactions)) ?> recent</span>
                    </div>
                    <div class="cashier-table-wrap">
                        <table class="cashier-transactions-table">
                            <thead>
                                <tr><th scope="col">Invoice</th><th scope="col">Customer</th><th scope="col">Service / Item</th><th scope="col">Amount</th><th scope="col">Method</th><th scope="col">Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($recentTransactions === []): ?>
                                    <tr><td class="cashier-empty-row" colspan="6">No transactions have been recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td class="cashier-invoice-number"><?= e((string) $transaction['invoice_no']) ?></td>
                                            <td><?= e((string) $transaction['customer_name']) ?></td>
                                            <td><?= e((string) ($transaction['service_name'] ?: '—')) ?></td>
                                            <td class="cashier-amount"><?= $money((float) $transaction['amount']) ?></td>
                                            <td><span class="cashier-payment-method"><?= e(ucwords(str_replace('_', ' ', (string) $transaction['payment_method']))) ?></span></td>
                                            <td><span class="cashier-status <?= e($statusClass((string) $transaction['status'])) ?>"><?= e(ucfirst((string) $transaction['status'])) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="cashier-profile-panel" id="cashier-profile" aria-labelledby="cashier-profile-title">
                    <div class="cashier-profile-heading">
                        <div>
                            <h2 id="cashier-profile-title">My Profile</h2>
                            <p>Your cashier account details</p>
                        </div>
                        <span class="cashier-profile-role">Cashier</span>
                    </div>
                    <div class="cashier-profile-details">
                        <div><span>Name</span><strong><?= e($userName) ?></strong></div>
                        <div><span>Username</span><strong><?= e((string) ($user['username'] ?? '—')) ?></strong></div>
                        <div><span>Email</span><strong><?= e((string) ($user['email'] ?? '—')) ?></strong></div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
