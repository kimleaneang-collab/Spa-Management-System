<?php

require_once __DIR__ . "/../models/User.php";

class AuthController
{
    private User $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Check empty fields
        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = "Please enter username and password.";
            header("Location: index.php");
            exit;
        }

        // Find user
        $user = $this->userModel->findByUsername($username);

        // Check username and password
        if ($user && password_verify($password, $user['password'])) {

            // Create login session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'Staff';

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        }

        // Wrong login
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: index.php");
        exit;
    }
}