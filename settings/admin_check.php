<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check if user has admin role (role = 'admin')
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to dashboard with error
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../dashboard.php");
    exit();
}

// Admin is authenticated and authorized
?>
