<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$products = [
    ['name' => 'Massage Oil 100ml', 'price' => '$15.00', 'status' => 'Low Stock', 'status_class' => 'status-cancelled', 'image' => APP_URL . '/public/uploads/massage.png'],
    ['name' => 'Body Lotion 200ml', 'price' => '$12.00', 'status' => 'In Stock', 'status_class' => 'status-confirmed', 'image' => APP_URL . '/public/uploads/body-scrub.png'],
    ['name' => 'Essential Oil 10ml', 'price' => '$10.00', 'status' => 'In Stock', 'status_class' => 'status-confirmed', 'image' => APP_URL . '/public/uploads/Essentail-oil.png'],
    ['name' => 'Gift Voucher', 'price' => 'From $10.00', 'status' => 'Unlimited', 'status_class' => 'status-in-progress', 'image' => APP_URL . '/public/uploads/gift.png'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Products | Relax Spa</title>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=products"><span class="nav-icon">▣</span><span>Products</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=stock-management"><span class="nav-icon">▤</span><span>Stock Management</span></a>
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
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, Admin!</div></div>
                <div class="topbar-actions">
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><span class="user-name">SV</span><span class="user-role">Admin</span><span class="user-chevron">▾</span></div>
                </div>
            </header>
            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">PRODUCTS</h1>
                        <div class="page-date">Inventory catalog</div>
                    </div>
                    <button class="primary-button" type="button">+ Add product</button>
                </div>
                <section class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
                            <div class="product-card-body">
                                <h3><?= e($product['name']) ?></h3>
                                <p><?= e($product['price']) ?></p>
                                <span class="status-pill <?= e($product['status_class']) ?>"><?= e($product['status']) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
