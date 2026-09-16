<?php
declare(strict_types=1);

if (empty($_SESSION['user'])) {
    redirect('login');
}

$categories = ['All', 'Massages', 'Facials', 'Body Care', 'Packages'];
$services = [
    ['name' => 'Full Body Massage', 'duration' => '60 mins', 'price' => '$40.00', 'category' => 'Massages', 'status' => 'Active', 'thumb' => 'massage', 'image' => APP_URL . '/public/uploads/fullbody-massage.png'],
    ['name' => 'Hot Stone Massage', 'duration' => '90 mins', 'price' => '$60.00', 'category' => 'Massages', 'status' => 'Active', 'thumb' => 'stone', 'image' => APP_URL . '/public/uploads/hotstone-message.png'],
    ['name' => 'Aromatherapy', 'duration' => '60 mins', 'price' => '$45.00', 'category' => 'Massages', 'status' => 'Active', 'thumb' => 'package', 'image' => APP_URL . '/public/uploads/aromatherapy.png'],
    ['name' => 'Foot Massage', 'duration' => '45 mins', 'price' => '$28.00', 'category' => 'Massages', 'status' => 'Active', 'thumb' => 'wrap', 'image' => APP_URL . '/public/uploads/foot-message.png'],
    ['name' => 'Facial Treatment', 'duration' => '60 mins', 'price' => '$35.00', 'category' => 'Facials', 'status' => 'Active', 'thumb' => 'facial', 'image' => APP_URL . '/public/uploads/facial-treatment.png'],
    ['name' => 'Body Scrub', 'duration' => '60 mins', 'price' => '$38.00', 'category' => 'Body Care', 'status' => 'Seasonal', 'thumb' => 'body', 'image' => APP_URL . '/public/uploads/body-scrub.png']
];
$defaultServiceImage = APP_URL . '/public/uploads/login-bg.jpg';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treatments | Relax Spa</title>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=customers"><span class="nav-icon">◉</span><span>Customers</span></a>
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
                    <div class="search-box"><span class="search-icon">⌕</span><span>Search</span></div>
                    <button class="icon-button" aria-label="Notifications">◔</button>
                    <div class="user-chip"><span class="user-name">SV</span><span class="user-role">Admin</span><span class="user-chevron">▾</span></div>
                </div>
            </header>

            <div class="content-wrap">
                <div class="page-heading">
                    <div>
                        <h1 class="page-title">TREATMENTS</h1>
                        <div class="page-date">Curated spa offerings</div>
                    </div>
                    <button class="primary-button" type="button">+ Add treatment</button>
                </div>

                <section class="module-panel">
                    <div class="toolbar-row">
                        <div class="toolbar-pills categories">
                            <?php foreach ($categories as $category): ?>
                                <button class="pill <?= $category === 'All' ? 'active' : '' ?>" type="button"><?= e($category) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="service-grid">
                        <?php foreach ($services as $service): ?>
                            <article class="service-card">
                                <div class="service-thumb thumb-<?= e($service['thumb']) ?>">
                                    <img class="service-image" src="<?= e($service['image'] ?? $defaultServiceImage) ?>" alt="<?= e($service['name']) ?>">
                                    <span class="service-badge service-<?= strtolower(str_replace(' ', '-', $service['status'])) ?>"><?= e($service['status']) ?></span>
                                </div>
                                <div class="service-body">
                                    <div class="service-label"><?= e($service['category']) ?></div>
                                    <h3><?= e($service['name']) ?></h3>
                                    <div class="service-meta">
                                        <span><?= e($service['duration']) ?></span>
                                        <span><?= e($service['price']) ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
