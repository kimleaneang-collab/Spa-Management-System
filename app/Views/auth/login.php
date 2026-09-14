<?php
session_start();
// បើ Login រួចហើយ ឱ្យ Redirect ទៅ Dashboard ភ្លាម
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relax Spa - Login</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <h1 class="brand-logo">Relax <span>spa</span></h1>
        </div>

        <!-- Form Section -->
        <div class="login-form-wrapper">
            <h2>Welcome</h2>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-msg"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <form action="../../controllers/AuthController.php" method="POST">
                <div class="input-group">
                    <label for="email">Username or Email</label>
                    <input type="text" id="email" name="email" required autocomplete="off">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" name="login_btn" class="btn-login">Log in</button>
            </form>
        </div>
    </div>
</body>
</html>