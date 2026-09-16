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
    <title>Memberships | Relax Spa</title>
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
                        <h1 class="page-title">MEMBERSHIPS</h1>
                        <div class="page-date">Member plan tiers</div>
                    </div>
                </div>
                <section class="membership-layout">
                    <div class="service-grid">
                        <article class="service-card">
                            <div class="service-body">
                                <div class="service-label">Silver</div>
                                <h3>$25<small>/mo</small></h3>
                                <p>5% auto-discount</p>
                            </div>
                        </article>
                        <article class="service-card">
                            <div class="service-body">
                                <div class="service-label">Gold</div>
                                <h3>$45<small>/mo</small></h3>
                                <p>10% auto-discount</p>
                            </div>
                        </article>
                        <article class="service-card">
                            <div class="service-body">
                                <div class="service-label">Platinum</div>
                                <h3>$75<small>/mo</small></h3>
                                <p>15% auto-discount + priority booking</p>
                            </div>
                        </article>
                    </div>
                    <div class="module-panel expiring-panel">
                        <strong>Expiring soon</strong>
                        <table class="data-table">
                            <thead><tr><th>Customer</th><th>Plan</th><th>Expired</th></tr></thead>
                            <tbody>
                                <tr><td>Dara Kim</td><td><span class="status-pill status-pending">Gold</span></td><td>May 25, 2026</td></tr>
                                <tr><td>Chan Den</td><td><span class="status-pill status-in-progress">Silver</span></td><td>Jul 15, 2026</td></tr>
                                <tr><td>Koy Sak</td><td><span class="status-pill status-pending">Gold</span></td><td>Jun 22, 2026</td></tr>
                                <tr><td>Thida Pen</td><td><span class="status-pill status-in-progress">Silver</span></td><td>Sep 03, 2026</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
