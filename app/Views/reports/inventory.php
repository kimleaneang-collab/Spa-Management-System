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
    <title>Reports | Relax Spa</title>
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
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=reports"><span class="nav-icon">▥</span><span>Reports &amp; Analytics</span></a>
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
                        <h1 class="page-title">REPORTS &amp; ANALYTICS</h1>
                        <div class="page-date">May 1-16, 2026</div>
                    </div>
                    <button class="secondary-button" type="button">Export Report</button>
                </div>
                <section class="report-stats">
                    <article><span>Revenue</span><strong>$142,300</strong><b>▲ 14.2%</b></article>
                    <article><span>Appointments</span><strong>612</strong><b>▲ 8.2%</b></article>
                    <article><span>New customers</span><strong>86</strong><b>▲ 5.1%</b></article>
                    <article><span>Avg. spend</span><strong>$62.40</strong><b class="negative">▼ 2.3%</b></article>
                </section>
                <section class="report-panels">
                    <article class="module-panel report-panel">
                        <h2>Revenue by service</h2>
                        <div class="report-bar"><span>Massages</span><i style="width: 62%"></i><b>62%</b></div>
                        <div class="report-bar"><span>Facials</span><i style="width: 20%"></i><b>20%</b></div>
                        <div class="report-bar"><span>Body care</span><i style="width: 12%"></i><b>12%</b></div>
                        <div class="report-bar"><span>Products</span><i style="width: 5%"></i><b>5%</b></div>
                    </article>
                    <article class="module-panel report-panel">
                        <h2>Therapist Performance</h2>
                        <div class="report-bar"><span>Sreyneang Sok</span><i style="width: 96%"></i><b>96</b></div>
                        <div class="report-bar"><span>Sokha Nhem</span><i style="width: 80%"></i><b>80</b></div>
                        <div class="report-bar"><span>Pichny Keo</span><i style="width: 64%"></i><b>64</b></div>
                    </article>
                </section>
            </div>
        </main>
    </div>
    <script src="<?= e(APP_URL) ?>/public/js/main.js"></script>
</body>
</html>
