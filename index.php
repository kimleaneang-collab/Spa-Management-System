<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$route = trim((string) ($_GET['route'] ?? 'login'), '/');

$auth = new AuthController();

switch ($route) {
    case '':
    case 'login':
        $auth->login();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'dashboard':
        if (empty($_SESSION['user'])) {
            redirect('login');
        }
        require __DIR__ . '/views/dashboard.php';
        break;

    default:
        http_response_code(404);
        echo '404 - Page not found';
}
