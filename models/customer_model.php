<?php
/**
 * Customer Model
 * 
 * This is where we actually talk to the database for user-related stuff.
 * It handles creating accounts, looking up users, and all that database magic.
 * 
 * Think of models as the database experts - they know exactly what SQL to write
 * and how to get the data we need. Everything else just asks them for help.
 */

require_once __DIR__ . '/../settings/db_class.php';

class CustomerModel extends db_connection
{
    /** @var mysqli The database connection object */
    private $conn;

    /**
     * Set up the model
     * 
     * When we create a new CustomerModel, we need to establish a connection
     * to the database. This grabs it from our parent class.
     */
    public function __construct()
    {
        // Get a mysqli connection from the db_connection class
        $this->conn = $this->db_conn();
    }

    /**
     * Create a new customer account
     * 
     * Inserts a new user into the database with their hashed password.
     * The password should already be hashed before calling this method.
     * 
     * @param string $name          Display name for the user
     * @param string $email         Email address (must be unique)
     * @param string $passwordHash  Already-hashed password from password_hash()
     * @param string $role          Either 'admin' or 'customer'
     * @return bool|int             Returns the new user's ID if successful, false if it fails
     */
    public function createCustomer(string $name, string $email, string $passwordHash, string $role = 'customer')
    {
        // Prepare the SQL query - the ? are placeholders that prevent SQL injection
        $sql = "INSERT INTO users (user_name, user_email, password_hash, user_role)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            // Something went wrong preparing the query
            return false;
        }

        // Bind our actual values to those ? placeholders
        // s = string, i = integer
        $stmt->bind_param('ssss', $name, $email, $passwordHash, $role);

        // Try to run the query
        if (!$stmt->execute()) {
            // Could be duplicate email or other database error
            return false;
        }

        // Success! Grab the ID that was auto-generated for this new user
        $insertId = $stmt->insert_id;
        $stmt->close();

        return $insertId;
    }

    /**
     * Find a user by their email address
     * 
     * This is used during login (to find their account) and during registration
     * (to check if the email is already taken).
     * 
     * @param string $email     The email to search for
     * @return array|false      Returns user data if found, false if no match
     */
    public function getCustomerByEmail(string $email)
    {
        // We need to include password_hash here so login can verify the password
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
        // fetch_assoc returns an array of the row, or null if no match - we convert null to false
        $row = $result->fetch_assoc() ?: false;

        $stmt->close();
        return $row;
    }

    /**
     * Get a user's info by their ID
     * 
     * Useful when we have the user_id from their session and need to load
     * their full profile information.
     * 
     * @param int $userId       The user's ID number
     * @return array|false      User data if found, false if not found
     */
    public function getCustomerById(int $userId)
    {
        // Notice we don't include password_hash here - we don't need it for profile viewing
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
