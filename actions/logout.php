<?php
/**
 * Logout Handler
 * 
 * Ends the user's session and sends them back to the home page.
 * Simple but important - we clear all session data to ensure
 * they're fully logged out.
 */

session_start();

// Wipe out all session variables
session_unset();

// Destroy the session cookie too
session_destroy();

// Send them back to the landing page
header("Location: ../index.php");
exit();
