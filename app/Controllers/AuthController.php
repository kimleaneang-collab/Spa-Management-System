<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $userData = $user->login($email);

    if ($userData && password_verify($password, $userData['password'])) {
        // Set Session
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['full_name'] = $userData['full_name'];
        $_SESSION['role'] = $userData['role_name'];

        header("Location: ../views/dashboard.php");
        exit();
    } else {
        $error = "Invalid Email or Password!";
        header("Location: ../views/auth/login.php?error=" . urlencode($error));
        exit();
    }
}
?>