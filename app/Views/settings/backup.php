<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Backup | Relax Spa</title>
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
                    <a class="nav-item" href="<?= e(APP_URL) ?>/?route=audit-logs"><span class="nav-icon">▤</span><span>Audit Logs</span></a>
                    <a class="nav-item active" href="<?= e(APP_URL) ?>/?route=backup"><span class="nav-icon">▤</span><span>Database Backup</span></a>
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
                    <div><h1 class="page-title">DATABASE BACKUP</h1><div class="page-date">Create and download a protected database snapshot.</div></div>
                    <form method="post" action="<?= e(APP_URL) ?>/?route=backup">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">
                        <button class="primary-button" type="submit">Create backup</button>
                    </form>
                </div>

                <?php if ($backupMessage !== ''): ?>
                    <div class="alert alert-success" role="status"><?= e($backupMessage) ?></div>
                <?php endif; ?>

                <section class="module-panel">
                    <h2>Recent backups</h2>
                    <p>Backup files are stored outside the public web directory and only Administrators can download them.</p>
                    <p><strong>Important:</strong> Database backups include customer and account data. Keep downloaded copies private and test restoration regularly.</p>
                    <?php if ($backups === []): ?>
                        <p>No backups have been created yet.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead><tr><th>File</th><th>Created</th><th>Size</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($backups as $backup): ?>
                                        <tr>
                                            <td><?= e($backup['filename']) ?></td>
                                            <td><?= e(date('Y-m-d H:i:s', (int) $backup['created_at'])) ?></td>
                                            <td><?= e(number_format((float) $backup['size'] / 1024, 1)) ?> KB</td>
                                            <td><a class="secondary-button" href="<?= e(APP_URL) ?>/?route=backup&amp;download=<?= rawurlencode((string) $backup['filename']) ?>">Download</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>
</body>
</html>