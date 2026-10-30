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
$money = static fn (float $amount): string => e($currencySymbol) . number_format($amount, 2);
$categories = [];
foreach ($catalog['services'] as $service) {
    $category = trim((string) ($service['category'] ?? ''));
    if ($category !== '') {
        $categories[strtolower($category)] = $category;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS / New Sale | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="dashboard-page cashier-dashboard-page cashier-pos-page" data-role="cashier">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
            </div>
            <nav class="sidebar-menu">
                <div class="nav-section" data-label="Main">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard"><span class="nav-icon">⌂</span><span>Dashboard</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=pos"><span class="nav-icon">▤</span><span>POS / New Sale</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard#recent-transactions"><span class="nav-icon">▧</span><span>Transactions</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">♙</span><span>Customers</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=dashboard#recent-transactions"><span class="nav-icon">▤</span><span>Receipt</span></a>
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

        <main class="main-panel cashier-pos-main">
            <header class="cashier-pos-topbar">
                <label class="cashier-pos-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" placeholder="Search customer, appointments, services..." aria-label="Search services and products" data-pos-search>
                </label>
                <div class="cashier-pos-user">
                    <span aria-hidden="true" class="cashier-pos-notification">♧</span>
                    <a class="cashier-pos-calendar" href="<?= e(APP_URL) ?>/?route=dashboard" aria-label="Open dashboard">▣</a>
                    <div class="cashier-pos-user-chip">
                        <?= profile_avatar($user, 'user-name', $initials) ?>
                        <span><strong><?= e($userName) ?></strong><small>Cashier</small></span>
                    </div>
                </div>
            </header>

            <div class="cashier-pos-content">
                <div class="cashier-pos-heading">
                    <div>
                        <h1>POS / NEW SALE</h1>
                        <p>Select services and products to build the order.</p>
                    </div>
                </div>

                <?php if ($formError !== ''): ?>
                    <div class="pos-alert error" role="alert"><?= e($formError) ?></div>
                <?php endif; ?>
                <?php if ($formSuccess !== ''): ?>
                    <div class="pos-alert success" role="status"><?= e($formSuccess) ?></div>
                <?php endif; ?>

                <div class="cashier-pos-layout">
                    <section class="cashier-pos-catalog" aria-label="Available services and products">
                        <div class="cashier-pos-filters" role="group" aria-label="Filter catalog">
                            <button type="button" class="cashier-pos-filter active" data-pos-category-filter="all" aria-pressed="true">All</button>
                            <?php foreach ($categories as $categoryKey => $categoryName): ?>
                                <button type="button" class="cashier-pos-filter" data-pos-category-filter="<?= e($categoryKey) ?>" aria-pressed="false"><?= e($categoryName) ?></button>
                            <?php endforeach; ?>
                            <?php if ($catalog['products'] !== []): ?>
                                <button type="button" class="cashier-pos-filter" data-pos-category-filter="products" aria-pressed="false">Products</button>
                            <?php endif; ?>
                        </div>

                        <div class="cashier-pos-item-grid" data-pos-item-grid>
                            <?php foreach ($catalog['services'] as $item): ?>
                                <?php
                                    $category = trim((string) ($item['category'] ?? ''));
                                    $imagePath = trim((string) ($item['image_path'] ?? ''));
                                    $imageUrl = $imagePath !== '' ? APP_URL . '/' . ltrim($imagePath, '/') : '';
                                    $itemData = [
                                        'type' => 'service',
                                        'id' => (int) $item['id'],
                                        'name' => (string) $item['name'],
                                        'price' => (float) $item['unit_price'],
                                    ];
                                ?>
                                <button
                                    class="pos-item-card cashier-pos-item-card"
                                    type="button"
                                    data-item="<?= e((string) json_encode($itemData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>"
                                    data-pos-category="<?= e(strtolower($category)) ?>"
                                    data-pos-search-value="<?= e(strtolower((string) $item['name'] . ' ' . $category)) ?>"
                                    aria-label="Add <?= e((string) $item['name']) ?>, <?= e($money((float) $item['unit_price'])) ?>"
                                >
                                    <span class="cashier-pos-item-image" aria-hidden="true">
                                        <?php if ($imageUrl !== ''): ?>
                                            <img src="<?= e($imageUrl) ?>" alt="" loading="lazy" data-pos-image>
                                            <span class="cashier-pos-image-fallback">✦</span>
                                        <?php else: ?>
                                            <span class="cashier-pos-image-fallback">✦</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="cashier-pos-item-copy">
                                        <strong><?= e((string) $item['name']) ?></strong>
                                        <small><?= e((string) $item['duration_minutes']) ?> mins</small>
                                        <span><?= $money((float) $item['unit_price']) ?></span>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                            <?php foreach ($catalog['products'] as $item): ?>
                                <?php
                                    $imagePath = trim((string) ($item['image_path'] ?? ''));
                                    $imageUrl = $imagePath !== '' ? APP_URL . '/' . ltrim($imagePath, '/') : '';
                                    $itemData = [
                                        'type' => 'product',
                                        'id' => (int) $item['id'],
                                        'name' => (string) $item['name'],
                                        'price' => (float) $item['unit_price'],
                                        'stock' => (float) $item['stock_quantity'],
                                        'unit' => (string) $item['unit'],
                                    ];
                                ?>
                                <button
                                    class="pos-item-card cashier-pos-item-card"
                                    type="button"
                                    data-item="<?= e((string) json_encode($itemData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)) ?>"
                                    data-pos-category="products"
                                    data-pos-search-value="<?= e(strtolower((string) $item['name'] . ' ' . (string) ($item['category'] ?? ''))) ?>"
                                    aria-label="Add <?= e((string) $item['name']) ?>, <?= e($money((float) $item['unit_price'])) ?>"
                                >
                                    <span class="cashier-pos-item-image product" aria-hidden="true">
                                        <?php if ($imageUrl !== ''): ?>
                                            <img src="<?= e($imageUrl) ?>" alt="" loading="lazy" data-pos-image>
                                            <span class="cashier-pos-image-fallback">▣</span>
                                        <?php else: ?>
                                            <span class="cashier-pos-image-fallback">▣</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="cashier-pos-item-copy">
                                        <strong><?= e((string) $item['name']) ?></strong>
                                        <small><?= e(rtrim(rtrim(number_format((float) $item['stock_quantity'], 3, '.', ''), '0'), '.') . ' ' . (string) $item['unit']) ?> in stock</small>
                                        <span><?= $money((float) $item['unit_price']) ?></span>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                            <?php if ($catalog['services'] === [] && $catalog['products'] === []): ?>
                                <p class="cashier-pos-empty">No active services or in-stock products are available.</p>
                            <?php endif; ?>
                        </div>
                        <p class="cashier-pos-empty cashier-pos-no-results" data-pos-no-results hidden>No catalog items match your search.</p>
                    </section>

                    <form class="cashier-pos-cart" method="post" action="<?= e(APP_URL) ?>/?route=pos" id="pos-checkout">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="items_json" id="pos-items-json" value="[]">
                        <input type="hidden" name="payment_method" id="pos-payment-method" value="qr_payment">
                        <input type="hidden" name="discount_amount" id="pos-discount" value="0">
                        <div class="cashier-pos-cart-heading">
                            <h2>Cart / Invoice <span data-pos-cart-count>(0)</span></h2>
                            <button class="pos-clear" type="button" id="pos-clear">Clear cart</button>
                        </div>
                        <label class="cashier-pos-customer-label" for="pos-customer">Customer</label>
                        <select id="pos-customer" name="customer_id" required>
                            <option value="">Select customer</option>
                            <?php foreach ($catalog['customers'] as $customer): ?>
                                <option value="<?= e((string) $customer['id']) ?>"><?= e((string) $customer['full_name']) ?><?= $customer['phone'] !== '' ? ' · ' . e((string) $customer['phone']) : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($catalog['customers'] === []): ?>
                            <p class="pos-customer-empty">There are no active customers yet. <a href="<?= e(APP_URL) ?>/?route=customers">Add a customer</a> before checkout.</p>
                        <?php endif; ?>
                        <div class="cashier-pos-cart-items" id="pos-cart-items" aria-live="polite">
                            <p class="cashier-pos-cart-empty" id="pos-cart-empty">Your cart is empty. Select a service or product.</p>
                        </div>
                        <div class="cashier-pos-totals">
                            <div><span>Subtotal</span><strong id="pos-subtotal"><?= e($money(0)) ?></strong></div>
                            <label><span>Discount</span><input id="pos-discount-input" type="number" min="0" step="0.01" value="0" aria-label="Discount amount"></label>
                            <div><span>VAT (<?= e(number_format($taxPercent, 0)) ?>%)</span><strong id="pos-tax"><?= e($money(0)) ?></strong></div>
                            <div class="cashier-pos-grand-total"><span>Total</span><strong id="pos-total"><?= e($money(0)) ?></strong></div>
                        </div>
                        <div class="cashier-pos-payment">
                            <span class="cashier-pos-payment-label">Payment method</span>
                            <div class="cashier-pos-payment-options" role="group" aria-label="Payment method">
                                <button type="button" class="active" data-payment-method="qr_payment" aria-pressed="true">KHQR</button>
                                <button type="button" data-payment-method="card" aria-pressed="false">Card</button>
                                <button type="button" data-payment-method="cash" aria-pressed="false">Cash</button>
                                <button type="button" data-payment-method="bank_transfer" aria-pressed="false">E-wallet</button>
                            </div>
                        </div>
                        <button class="cashier-pos-charge" type="submit">Charge <span id="cashier-pos-charge-total"><?= e($money(0)) ?></span></button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        window.posConfig = {
            currency: <?= json_encode($currency, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>,
            currencySymbol: <?= json_encode($currencySymbol, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) ?>,
            taxPercent: <?= json_encode($taxPercent) ?>
        };
    </script>
    <script src="<?= e(APP_URL) ?>/public/js/pos.js?v=<?= e(asset_version('public/js/pos.js')) ?>"></script>
    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
