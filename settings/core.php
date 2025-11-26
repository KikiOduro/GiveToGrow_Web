<?php
/**
 * Core Helper Functions
 * 
 * This file contains utility functions that are used throughout the application.
 * These are the handy little helpers that make our code cleaner and easier to read.
 * 
 * Think of this as the toolbox - small functions that solve common problems
 * so we don't have to write the same code over and over.
 */

// Make sure we have a session running - needed for checking if users are logged in
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Check if someone is logged in
 * 
 * This is the quick way to see if we have an authenticated user.
 * Just checks if their user_id is stored in the session.
 * 
 * Usage:
 * if (isLoggedIn()) {
 *     // Show personalized content
 * } else {
 *     // Redirect to login
 * }
 * 
 * @return bool     True if user is logged in, false if not
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 * 
 * First checks if they're logged in at all, then verifies their role.
 * Admins have user_role = 'admin', regular users have 'customer'.
 * 
 * Use this to protect admin-only pages:
 * if (!isAdmin()) {
 *     header('Location: dashboard.php');
 *     exit;
 * }
 * 
 * @return bool     True if user is logged in AND is an admin
 */
function isAdmin(): bool
{
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
