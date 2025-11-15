<?php
// models/customer_class.php

require_once __DIR__ . '/../settings/db_class.php';

class CustomerModel extends db_connection
{
    /** @var mysqli */
    private $conn;

    public function __construct()
    {
        // Get a mysqli connection from your db_connection class
        $this->conn = $this->db_conn();
    }

    /**
     * Create a new customer (user) record.
     *
     * @param string $name
     * @param string $email
     * @param string $passwordHash  password_hash() output
     * @param string $role          'customer' or 'admin'
     * @return bool|int             false on failure, inserted id on success
     */
    public function createCustomer(string $name, string $email, string $passwordHash, string $role = 'customer')
    {
        $sql = "INSERT INTO users (user_name, user_email, password_hash, user_role)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);

        if (!$stmt->execute()) {
            return false;
        }

        $insertId = $stmt->insert_id;
        $stmt->close();

        return $insertId;
    }

    /**
     * Get a single customer by email.
     *
     * Used for login (we fetch the user, then verify password in login action).
     *
     * @param string $email
     * @return array|false
     */
    public function getCustomerByEmail(string $email)
    {
        $sql = "SELECT user_id, user_name, user_email, password_hash, user_role, is_active
                FROM users
                WHERE user_email = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: false;

        $stmt->close();
        return $row;
    }

    /**
     * Get customer by user ID.
     *
     * @param int $userId
     * @return array|false
     */
    public function getCustomerById(int $userId)
    {
        $sql = "SELECT user_id, user_name, user_email, user_role, is_active, created_at, updated_at
                FROM users
                WHERE user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc() ?: false;

        $stmt->close();
        return $row;
    }
}
