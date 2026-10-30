<?php
declare(strict_types=1);

$user = $_SESSION['user'] ?? [];
$userName = (string) ($user['full_name'] ?? $user['username'] ?? 'User');
$userInitials = strtoupper(substr($userName, 0, 1));
// Format an amount with the selected currency.
$money = static fn (float $amount): string => e($currency) . ' ' . number_format($amount, 2);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS &amp; Billing | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=calendar"><span class="nav-icon">▦</span><span>Calendar</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Services</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS &amp; Billing</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Inventory</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e($userName) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <div class="user-chip"><?= profile_avatar($user, 'user-name', $userInitials) ?><span class="user-role"><?= e(role_label((string) ($user['role'] ?? 'staff'))) ?></span></div>
                </div>
            </header>
            <div class="content-wrap pos-page">
                <div class="page-heading">
                    <div><h1 class="page-title">POS &amp; BILLING</h1><div class="page-date">Create an invoice and record a customer payment.</div></div>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="pos-alert error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <?php if ($formSuccess !== ''): ?>
                    <div class="pos-alert success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>

                <div class="pos-layout">
                    <section class="pos-catalog">
                        <div class="pos-section-heading">
                            <h2>Services</h2>
                            <span><?= count($catalog['services']) ?> available</span>
                        </div>
                        <div class="pos-item-grid">
                            <?php foreach ($catalog['services'] as $item): ?>
                                <?php $itemData = ['type' => 'service', 'id' => (int) $item['id'], 'name' => (string) $item['name'], 'price' => (float) $item['unit_price']]; ?>
                                <button class="pos-item-card" type="button" data-item="<?= e((string) json_encode($itemData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>">
                                    <span class="pos-item-type">SERVICE</span>
                                    <strong><?= e((string) $item['name']) ?></strong>
                                    <span><?= e($money((float) $item['unit_price'])) ?></span>
                                    <span class="pos-add-label">＋ Add</span>
                                </button>
                            <?php endforeach; ?>
                            <?php if ($catalog['services'] === []): ?><p class="pos-empty">No active services are available.</p><?php endif; ?>
                        </div>

                        <div class="pos-section-heading products-heading">
                            <h2>Retail products</h2>
                            <span><?= count($catalog['products']) ?> in stock</span>
                        </div>
                        <div class="pos-item-grid">
                            <?php foreach ($catalog['products'] as $item): ?>
                                <?php $itemData = ['type' => 'product', 'id' => (int) $item['id'], 'name' => (string) $item['name'], 'price' => (float) $item['unit_price'], 'stock' => (float) $item['stock_quantity'], 'unit' => (string) $item['unit']]; ?>
                                <button class="pos-item-card" type="button" data-item="<?= e((string) json_encode($itemData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>">
                                    <span class="pos-item-type">PRODUCT · <?= e((string) $item['stock_quantity']) ?> <?= e((string) $item['unit']) ?> left</span>
                                    <strong><?= e((string) $item['name']) ?></strong>
                                    <span><?= e($money((float) $item['unit_price'])) ?></span>
                                    <span class="pos-add-label">＋ Add</span>
                                </button>
                            <?php endforeach; ?>
                            <?php if ($catalog['products'] === []): ?><p class="pos-empty">No active products are currently in stock.</p><?php endif; ?>
                        </div>
                    </section>

                    <form class="pos-cart" method="post" action="<?= e(APP_URL) ?>/?route=pos" id="pos-checkout">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="items_json" id="pos-items-json" value="[]">
                        <div class="pos-cart-heading"><h2>Current order</h2><button class="pos-clear" type="button" id="pos-clear">Clear</button></div>
                        <label class="pos-field-label" for="pos-customer">Customer</label>
                        <select id="pos-customer" name="customer_id" required>
                            <option value="">Select a customer</option>
                            <?php foreach ($catalog['customers'] as $customer): ?>
                                <option value="<?= e((string) $customer['id']) ?>"><?= e((string) $customer['full_name']) ?><?= $customer['phone'] !== '' ? ' · ' . e((string) $customer['phone']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($catalog['customers'] === []): ?>
                            <p class="pos-customer-empty">There are no active customers yet. <a href="<?= e(APP_URL) ?>/?route=customers">Add a customer</a> before checkout.</p>
                        <?php endif; ?>

                        <div class="pos-cart-items" id="pos-cart-items" aria-live="polite">
                            <p class="pos-empty" id="pos-cart-empty">Your cart is empty. Add a service or product.</p>
                        </div>

                        <label class="pos-field-label" for="pos-discount">Discount (<?= e($currency) ?>)</label>
                        <input id="pos-discount" name="discount_amount" type="number" min="0" step="0.01" value="0">

                        <div class="pos-totals">
                            <div><span>Subtotal</span><strong id="pos-subtotal"><?= e($money(0)) ?></strong></div>
                            <div><span>Tax (<?= e(number_format($taxPercent, 2)) ?>%)</span><strong id="pos-tax"><?= e($money(0)) ?></strong></div>
                            <div class="pos-grand-total"><span>Total due</span><strong id="pos-total"><?= e($money(0)) ?></strong></div>
                        </div>

                        <label class="pos-field-label" for="pos-payment-method">Payment method</label>
                        <select id="pos-payment-method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="qr_payment">QR payment</option>
                        </select>
                        <button class="pos-checkout-button" type="submit">Complete payment</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        window.posConfig = {
            currency: <?= json_encode($currency, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>,
            taxPercent: <?= json_encode($taxPercent) ?>
        };
    </script>
    <script src="<?= e(APP_URL) ?>/public/js/pos.js?v=<?= e(asset_version('public/js/pos.js')) ?>"></script>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
