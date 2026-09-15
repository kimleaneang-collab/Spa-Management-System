<?php

session_start();

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/controllers/AuthController.php";

$authController = new AuthController($pdo);

$action = $_GET['action'] ?? 'login';


switch ($action) {

    case 'login':

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $authController->login();

        } else {

            require __DIR__ . "/views/auth/login.php";

        }

        break;


    default:

        require __DIR__ . "/views/auth/login.php";

        break;
}