<?php
// actions/register_customer.php

session_start();

require_once __DIR__ . '/../controllers/customer_controller.php';

// 1. Read POST input safely
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

// Generate a simple name from email (since there is no name field)
$name = '';
if ($email !== '') {
    $localPart = strstr($email, '@', true) ?: $email;
    $localPart = str_replace(['.', '_', '-'], ' ', $localPart);
    $name      = ucwords($localPart);   // e.g. "abenaoduro1" → "Abenaoduro1"
}

// Default role
$role = 2;

// 2. Basic validation (only require email, password, confirm)
if ($email === '' || $password === '' || $confirm === '') {
    $_SESSION['register_error'] = "All fields are required.";
    header("Location: ../login/register.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Invalid email format.";
    header("Location: ../login/register.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['register_error'] = "Passwords do not match.";
    header("Location: ../login/register.php");
    exit;
}

// 3. Check if email already exists
$existing = get_customer_by_email_ctr($email);

if ($existing) {
    $_SESSION['register_error'] = "Email is already registered.";
    header("Location: ../login/register.php");
    exit;
}

// 4. Create user (controller hashes the password)
$user_id = register_customer_ctr($name, $email, $password, $role);

if (!$user_id) {
    $_SESSION['register_error'] = "Registration failed. Try again.";
    header("Location: ../login/register.php");
    exit;
}

// 5. (Optional) auto-login user
$_SESSION['user_id']    = $user_id;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = $role;

// 6. Redirect to login page
header("Location: ../login/login.php");
exit;
