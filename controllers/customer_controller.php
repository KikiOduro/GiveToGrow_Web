<?php
// controllers/customer_controller.php

require_once __DIR__ . '/../models/customer_class.php';

/**
 * Register a new customer.
 *
 * @param string $name
 * @param string $email
 * @param string $password  Plain-text password from form
 * @param string $role      Optional, defaults to 'customer'
 * @return bool|int         false on failure, new user_id on success
 */
function register_customer_ctr(string $name, string $email, string $password, string $role = 'customer')
{
    $customerModel = new CustomerModel();

    // Hash the password before storing
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    return $customerModel->createCustomer($name, $email, $passwordHash, $role);
}

/**
 * Get a customer by email for login.
 *
 * Used by actions/login_customer.php.
 * This DOES NOT verify the password, it just fetches the row.
 *
 * @param string $email
 * @return array|false
 */
function login_customer_ctr(string $email)
{
    $customerModel = new CustomerModel();
    return $customerModel->getCustomerByEmail($email);
}

/**
 * Get a logged-in customer by their ID.
 *
 * Useful for profile/dashboard pages.
 *
 * @param int $userId
 * @return array|false
 */
function get_customer_by_id_ctr(int $userId)
{
    $customerModel = new CustomerModel();
    return $customerModel->getCustomerById($userId);
}
