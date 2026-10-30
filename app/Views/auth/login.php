<?php
declare(strict_types=1);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['login_error'] ?? '';
$loginUsername = $_SESSION['login_username'] ?? '';
unset($_SESSION['login_error']);
unset($_SESSION['login_username']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Relax Spa Management System login">
    <title>Welcome | Relax Spa</title>
    <link rel="stylesheet" href="<?= e(APP_URL) ?>/public/css/style.css?v=<?= e(asset_version('public/css/style.css')) ?>">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-brand" aria-label="Relax Spa">
            <div class="brand-mark"><img class="brand-logo" src="<?= e(APP_URL) ?>/public/images/relax-spa-logo-v2.png" alt="Relax Spa"></div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <h1 class="login-title">Welcome</h1>
                <p class="login-description">Sign in to manage your spa</p>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-error" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post"
                      action="<?= e(APP_URL) ?>/?route=login"
                      autocomplete="on"
                      novalidate>
                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']) ?>">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            inputmode="text"
                            minlength="3"
                            maxlength="100"
                            pattern="[A-Za-z0-9@_.-]{3,100}"
                            autocomplete="username"
                            value="<?= e($loginUsername) ?>"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrap">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                minlength="1"
                                maxlength="255"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                class="password-toggle"
                                type="button"
                                aria-label="Show password"
                                aria-pressed="false"
                                data-password-toggle
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-password-visible>
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" data-password-hidden hidden>
                                    <path d="m3 3 18 18"></path>
                                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                    <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3.1 3.8"></path>
                                    <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4-.8"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button class="login-button" type="submit">Log in</button>
                </form>

                <p class="secure-note">Secure staff access</p>
            </div>
        </section>
    </main>

    <script src="<?= e(APP_URL) ?>/public/js/main.js?v=<?= e(asset_version('public/js/main.js')) ?>"></script>
</body>
</html>
