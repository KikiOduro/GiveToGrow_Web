<?php
/**
 * Customer Controller
 * 
 * This is the middle layer between our action files and the database.
 * Think of it as a translator - the actions say "I need a user",
 * we figure out how to get it, and pass it back.
 * 
 * Why have this layer? It keeps our code organized and makes it easy
 * to change database logic without touching all our action files.
 */

require_once __DIR__ . '/../models/customer_model.php';

/**
 * Register a new customer
 * 
 * Takes the raw data from the registration form and creates a new user account.
 * The password gets hashed here for security before being stored in the database.
 * 
 * @param string $name      Display name for the user
 * @param string $email     Their email address (must be unique)
 * @param string $password  Plain-text password from the form
 * @param string $role      Either 'admin' or 'customer' (defaults to customer)
 * @return bool|int         Returns the new user_id on success, false if something went wrong
 */
function register_customer_ctr(string $name, string $email, string $password, string $role = 'customer')
{
    $customerModel = new CustomerModel();

    // Hash the password using PHP's built-in secure hashing
    // This means even if someone gets our database, they can't read the passwords
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    return $customerModel->createCustomer($name, $email, $passwordHash, $role);
}

/**
 * Look up a customer by their email
 * 
 * Used during registration to check if an email is already taken,
 * and during login to find the user's account.
 * 
 * @param string $email     The email address to search for
 * @return array|false      Returns user data if found, false if not found
 */
function get_customer_by_email_ctr(string $email)
{
    $model = new CustomerModel();
    return $model->getCustomerByEmail($email);
}

/**
 * Get customer data for login
 * 
 * This is specifically for the login process. It fetches the user's record
 * including their hashed password so we can verify they entered the right password.
 * 
 * Note: This doesn't actually verify the password - that happens in the action file.
 * We just grab the data here.
 * 
 * @param string $email     Email they're trying to log in with
 * @return array|false      User data including password_hash, or false if not found
 */
function login_customer_ctr(string $email)
{
    $customerModel = new CustomerModel();
    return $customerModel->getCustomerByEmail($email);
}

/**
 * Get customer data by their ID
 * 
 * Useful when we already know the user_id (like from their session)
 * and need to fetch their full profile information.
 * 
 * @param int $userId       The user's ID from the database
 * @return array|false      Their profile data, or false if not found
 */
function get_customer_by_id_ctr(int $userId)
{
    $customerModel = new CustomerModel();
    return $customerModel->getCustomerById($userId);
}
