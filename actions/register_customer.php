<?php
// actions/register_customer.php

session_start();

require_once __DIR__ . '/../controllers/customer_controller.php';

// 1. Read POST input safely
$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

// Default role
$role = 'customer';

// 2. Basic validation
if ($name === '' || $email === '' || $password === '' || $confirm === '') {
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

// 4. Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 5. Create user
$user_id = register_customer_ctr($name, $email, $passwordHash, $role);

if (!$user_id) {
    $_SESSION['register_error'] = "Registration failed. Try again.";
    header("Location: ../login/register.php");
    exit;
}

// 6. Auto-login user
$_SESSION['user_id']    = $user_id;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = $role;

// 7. Redirect to dashboard or home
header("Location: ../login/login.php");
exit;
