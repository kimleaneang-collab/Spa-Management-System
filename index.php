<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/LoginController.php';

$action = $_GET['action'] ?? 'login';
$loginController = new LoginController($pdo);

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginController->login();
        } else {
            $loginController->showLogin();
        }
        break;
    default:
        $loginController->showLogin();
        break;
}