<?php

require_once __DIR__ . '/../Models/User.php';

class LoginController
{
    private $pdo;

    public function construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function showLogin()
    {
        require __DIR__ . '/../Views/login.php';
    }

    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User($this->pdo);

        $user = $userModel->login($username, $password);

        if ($user) {

            session_start();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: /Relax%20%26%20Spa/public/index.php?action=dashboard");
            exit;

        } else {

            $error = "Invalid username or password";

            require __DIR__ . '/../Views/login.php';
        }
    }
}