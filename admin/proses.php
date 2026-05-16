<?php

require_once __DIR__ . '/../includes/admin/init.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../function/admin/auth.php';

// Logout
if (isset($_GET['logout'])) {
    logout_admin();
    header('Location: ' . LOGIN_URL);
    exit;
}

// Login
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (login_admin($conn, $email, $password)) {
    header('Location: ' . admin_module_url('dashboard'));
    exit;
}

$_SESSION['login_error'] = 'Email atau kata sandi salah.';
header('Location: ' . LOGIN_URL);
exit;
