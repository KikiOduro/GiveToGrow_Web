<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../controllers/customer_controller.php';

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// Basic validation
if (trim($email) === '' || trim($password) === '') {
    $_SESSION['login_error'] = 'Email and password are required.';
    header('Location: ../login/login.php');
    exit;
}

// Call your controller function
// Expect: false on failure OR an array with user_id, user_name, user_email, user_role, password_hash
$result = login_customer_ctr($email);

if ($result && password_verify($password, $result['password_hash'])) {
    $_SESSION['user_id']    = $result['user_id'];
    $_SESSION['user_name']  = $result['user_name'];
    $_SESSION['user_email'] = $result['user_email'];
    $_SESSION['user_role']  = $result['user_role'] ?? 2;
    $_SESSION['logged_in']  = true;

    header('Location: ../dashboard.php');
    exit;
}

// Login failed
$_SESSION['login_error'] = 'Invalid login credentials.';
header('Location: ../login/login.php');
exit;
