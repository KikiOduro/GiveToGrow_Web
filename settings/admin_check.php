<?php
/**
 * Admin Authentication Guard
 * 
 * Include this file at the top of any admin-only page to ensure
 * the user is logged in AND has admin privileges. If they don't,
 * they get redirected with an error message.
 * 
 * Usage: require_once __DIR__ . '/../settings/admin_check.php';
 */

// First check: Are they logged in at all?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Second check: Do they have admin role?
// Regular users (role = 'customer') can't access admin pages
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../dashboard.php");
    exit();
}

// If we get here, the user is a legit admin - let them through!
