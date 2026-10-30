<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Logs | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=services"><span class="nav-icon">✦</span><span>Treatments</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=therapists"><span class="nav-icon">◎</span><span>Therapists</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=rooms"><span class="nav-icon">◫</span><span>Rooms</span></a>
                </div>
                <div class="nav-section">
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=users-and-roles"><span class="nav-icon">⚙</span><span>Users &amp; Roles</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=audit-logs"><span class="nav-icon">▤</span><span>Audit Logs</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=backup"><span class="nav-icon">▤</span><span>Database Backup</span></a>
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=settings"><span class="nav-icon">⚙</span><span>Settings</span></a>
                </div>
            </nav>
            <?= sidebar_role_footer() ?>
        </aside>
        <main class="main-panel">
            <header class="topbar">
                <div class="topbar-left"><div class="welcome-text">Welcome back, <?= e((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Admin')) ?>!</div></div>
                <div class="topbar-actions">
                    <div class="user-chip"><?= profile_avatar($_SESSION['user'] ?? [], 'user-name', strtoupper(substr((string) ($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'A'), 0, 1))) ?><span class="user-role"><?= e(role_label((string) ($_SESSION['user']['role'] ?? 'admin'))) ?></span></div>
                </div>
            </header>
            <div class="content-wrap">
                <div class="page-heading">
                    <div><h1 class="page-title">AUDIT LOGS</h1><div class="page-date">Latest 200 recorded security and business events.</div></div>
                </div>
                <section class="module-panel">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Date and time</th><th>User</th><th>Action</th><th>Record</th><th>IP address</th></tr></thead>
                            <tbody>
                                <?php if ($auditEvents === []): ?>
                                    <tr><td colspan="5">No audit events have been recorded.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($auditEvents as $event): ?>
                                        <tr>
                                            <td><?= e((string) $event['created_at']) ?></td>
                                            <td><?= e((string) ($event['username'] ?? 'Deleted user / system')) ?></td>
                                            <td><?= e((string) $event['action']) ?></td>
                                            <td><?= e((string) ($event['table_name'] ?? '')) ?><?= $event['record_id'] !== null ? ' #' . e((string) $event['record_id']) : '' ?></td>
                                            <td><?= e((string) ($event['ip_address'] ?? '')) ?></td>
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
</body>
</html>
