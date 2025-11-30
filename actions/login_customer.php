<?php
/**
 * Login Action Handler
 * 
 * This file processes user login attempts. It validates credentials,
 * verifies passwords, and sets up the user session if everything checks out.
 * 
 * Flow:
 * 1. Grab email and password from the login form
 * 2. Check if both fields are actually filled in
 * 3. Look up the user in the database
 * 4. Verify the password matches what we have stored
 * 5. Create their session and send them to the dashboard
 */

// Show errors during development (helpful for debugging)
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Make sure we have a session running to store login info
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Bring in our controller functions that talk to the database
require_once __DIR__ . '/../controllers/customer_controller.php';

// Grab the form data - using null coalescing operator to avoid undefined index warnings
$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

// First things first - make sure they actually entered something
if (trim($email) === '' || trim($password) === '') {
    $_SESSION['login_error'] = 'Email and password are required.';
    header('Location: ../login/login.php');
    exit;
}

// Look up the user by email
// The controller returns false if user doesn't exist, or an array with their info if they do
$result = login_customer_ctr($email);

//  use password_verify() because passwords are hashed for security
if ($result && password_verify($password, $result['password_hash'])) {
    // Success! Let's set up their session so they stay logged in
    $_SESSION['user_id']    = $result['user_id'];
    $_SESSION['user_name']  = $result['user_name'];
    $_SESSION['user_email'] = $result['user_email'];
    $_SESSION['user_role']  = $result['user_role'] ?? 'customer'; // Default to customer if role not set
    $_SESSION['logged_in']  = true;

    // Send users to their dashboard
    header('Location: ../views/dashboard.php');
    exit;
}

// If it gets here, something went wrong ; either email doesn't exist or password was wrong
$_SESSION['login_error'] = 'Invalid login credentials.';
header('Location: ../login/login.php');
exit;
