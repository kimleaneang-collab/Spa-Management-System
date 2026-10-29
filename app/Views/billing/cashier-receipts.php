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
    <title>Receipts | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-receipts-page" data-role="cashier">
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=receipts"><span class="nav-icon">▤</span><span>Receipt</span></a>
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
                <div class="topbar-actions cashier-receipts-topbar">
                    <div class="search-box">
                        <span class="search-icon" aria-hidden="true">⌕</span>
                        <input type="search" placeholder="Search customer, appointments, services..." aria-label="Search receipts" data-cashier-receipt-search>
                    </div>
                    <div class="cashier-receipts-tools" aria-hidden="true"><span>♧</span><span>▣</span></div>
                    <div class="user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span class="cashier-receipts-user"><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="content-wrap cashier-receipts-content">
                <div class="cashier-receipts-heading">
                    <h1 class="page-title">Receipt</h1>
                    <p>Printed and digital receipts issued to guests.</p>
                </div>

                <section class="cashier-receipts-panel" aria-label="Customer receipts">
                    <div class="table-wrap">
                        <table class="cashier-receipts-table">
                            <thead>
                                <tr data-cashier-receipt-row>
                                    <th scope="col">Receipt No.</th>
                                    <th scope="col">Customers</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Sent Via</th>
                                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($receipts === []): ?>
                                    <tr><td class="cashier-receipts-empty" colspan="6">No receipts have been issued yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($receipts as $receipt): ?>
                                        <?php
                                            $receiptTimestamp = strtotime((string) $receipt['receipt_date']);
                                            $receiptDate = $receiptTimestamp === false
                                                ? (string) $receipt['receipt_date']
                                                : date('M j, g:i A', $receiptTimestamp);
                                        ?>
                                        <tr>
                                            <td><?= e((string) $receipt['invoice_no']) ?></td>
                                            <td><?= e((string) $receipt['customer_name']) ?></td>
                                            <td><?= e($receiptDate) ?></td>
                                            <td><?= e($currencySymbol) . e(number_format((float) $receipt['amount'], 2)) ?></td>
                                            <td><span class="cashier-receipt-channel">Print</span></td>
                                            <td>
                                                <button class="cashier-receipt-view" type="button"
                                                    data-open-modal="cashier-receipt-modal"
                                                    data-receipt-no="<?= e((string) $receipt['invoice_no']) ?>"
                                                    data-customer="<?= e((string) $receipt['customer_name']) ?>"
                                                    data-date="<?= e($receiptDate) ?>"
                                                    data-amount="<?= e(number_format((float) $receipt['amount'], 2)) ?>"
                                                    data-currency="<?= e($currencySymbol) ?>"
                                                    data-method="<?= e(ucwords(str_replace('_', ' ', (string) $receipt['payment_method']))) ?>"
                                                    data-items="<?= e((string) ($receipt['items'] ?: 'Spa purchase')) ?>">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr data-cashier-receipt-no-results hidden><td class="cashier-receipts-empty" colspan="6">No receipts match your search.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <div class="modal-backdrop hidden" id="cashier-receipt-modal" aria-hidden="true">
        <section class="modal-card cashier-receipt-detail" role="dialog" aria-modal="true" aria-labelledby="cashier-receipt-title">
            <div class="modal-header">
                <div><p class="eyebrow">Receipt details</p><h2 id="cashier-receipt-title" data-receipt-detail="receiptNo"></h2></div>
                <button class="modal-close" type="button" aria-label="Close" data-close-modal="cashier-receipt-modal">×</button>
            </div>
            <dl class="cashier-receipt-details">
                <div><dt>Customer</dt><dd data-receipt-detail="customer"></dd></div>
                <div><dt>Date</dt><dd data-receipt-detail="date"></dd></div>
                <div><dt>Items</dt><dd data-receipt-detail="items"></dd></div>
                <div><dt>Payment method</dt><dd data-receipt-detail="method"></dd></div>
                <div><dt>Total paid</dt><dd><span data-receipt-detail="currency"></span><span data-receipt-detail="amount"></span></dd></div>
            </dl>
            <div class="modal-actions">
                <button class="secondary-button" type="button" data-close-modal="cashier-receipt-modal">Close</button>
                <button class="primary-button" type="button" data-print-cashier-receipt>Print receipt</button>
            </div>
        </section>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
