<?php
/**
 * User Registration Action Handler
 * 
 * This file handles new user signups. It validates their information,
 * checks if the email is already taken, and creates their account.
 * 
 * Flow:
 * 1. Get email, password, and password confirmation from form
 * 2. Generate a display name from their email address
 * 3. Run validation checks (all fields filled, email format, passwords match)
 * 4. Make sure this email isn't already registered
 * 5. Create the account with a securely hashed password
 * 6. Send them to login page to sign in with their new account
 */

session_start();

// Bring in our controller functions
require_once __DIR__ . '/../controllers/customer_controller.php';

// Grab and clean up the form data
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

// Since we don't have a separate name field, let's make one from their email
// This turns "john.doe@example.com" into "John Doe" - not perfect but works!
$name = '';
if ($email !== '') {
    $localPart = strstr($email, '@', true) ?: $email; // Get the part before @
    $localPart = str_replace(['.', '_', '-'], ' ', $localPart); // Replace separators with spaces
    $name      = ucwords($localPart); // Capitalize each word
}

// Everyone starts as a regular customer (admins are created manually in the database)
$role = 'customer';

// Time to validate everything they entered

// First check - did they actually fill everything out?
if ($email === '' || $password === '' || $confirm === '') {
    $_SESSION['register_error'] = "All fields are required.";
    header("Location: ../login/register.php");
    exit;
}

// Is this actually a valid email format?
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Invalid email format.";
    header("Location: ../login/register.php");
    exit;
}

// Do both password fields match? (catching typos here)
if ($password !== $confirm) {
    $_SESSION['register_error'] = "Passwords do not match.";
    header("Location: ../login/register.php");
    exit;
}

// Check if someone already registered with this email
$existing = get_customer_by_email_ctr($email);

if ($existing) {
    $_SESSION['register_error'] = "Email is already registered.";
    header("Location: ../login/register.php");
    exit;
}

// All good! Let's create their account
// Note: The controller will hash the password for security
$user_id = register_customer_ctr($name, $email, $password, $role);

if (!$user_id) {
    // Something went wrong on the database side
    $_SESSION['register_error'] = "Registration failed. Try again.";
    header("Location: ../login/register.php");
    exit;
}

// Success! Set up their session so they're logged in
$_SESSION['user_id']    = $user_id;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $email;
$_SESSION['user_role']  = $role;

// Send them to the login page (or we could send them straight to dashboard since they're logged in)
header("Location: ../login/login.php");
exit;
