<?php
/**
 * Database Connection Class
 * 
 * This is our bridge to the MySQL database. It handles all the connection stuff
 * and gives us simple methods to run queries without worrying about the details.
 * 
 * Why use a class? It keeps all our database logic in one place. If we need to
 * change how we connect or add error handling, we just update it here.
 * 
 * Features:
 * - Auto-connects when needed (lazy connection)
 * - Works with MAMP, XAMPP, or regular MySQL
 * - Supports prepared statements (protection against SQL injection)
 * - UTF-8 support for international characters
 * - Helpful error logging for debugging
 * 
 * @version 2.1
 */

include_once __DIR__ . '/db_cred.php';

/**
 * Main database connection class
 * All our models extend this to get database access
 */
if (!class_exists('db_connection')) {
    class db_connection
    {
        /** @var mysqli|null The actual database connection */
        public $db = null;

        /** @var mysqli_result|false|null Results from the last query */
        public $results = null;

        /**
         * Make sure we have an active database connection
         * 
         * This is called internally before every query. It checks if we're
         * already connected, and if not, it creates a new connection.
         * 
         * This also works around MAMP/local development quirks with sockets vs ports.
         * 
         * @return bool     True if we're connected, false if connection failed
         */
        private function ensure_connected(): bool
        {
            // If we already have a connection, make sure it's still alive
            if ($this->db instanceof mysqli) {
                // Quick test query to check if connection is still good
                $alive = @$this->db->query("SELECT 1");
                if ($alive !== false) {
                    return true; // All good, connection is working
                }
            }

            // Need to establish a new connection
            // These come from db_cred.php (or we use sensible defaults)
            $host   = defined('SERVER')   ? SERVER   : '127.0.0.1';
            $user   = defined('USERNAME') ? USERNAME : 'root';
            $pass   = defined('PASSWD')   ? PASSWD   : '';
            $db     = defined('DATABASE') ? DATABASE : '';
            $port   = defined('PORT')     ? PORT     : 3306;
            $socket = (defined('SOCKET') && SOCKET) ? SOCKET : null;

            // Try connecting via TCP first (normal way)
            $link = @mysqli_connect($host, $user, $pass, $db, $port);

            // If that didn't work and we have a socket path (MAMP usually needs this),
            // try connecting through the socket instead
            if (!$link && $socket) {
                $link = @mysqli_connect(null, $user, $pass, $db, null, $socket);
            }

            if (!$link) {
                // Connection failed - log it so we can debug
                error_log('DB CONNECT ERROR: ' . mysqli_connect_error());
                $this->db = null;
                return false;
            }

            // Set character encoding to UTF-8 (supports emojis and international characters)
            if (!@mysqli_set_charset($link, 'utf8mb4')) {
                error_log('DB CHARSET ERROR: ' . mysqli_error($link));
            }

            $this->db = $link;
            return true;
        }

        /**
         * Check if we can connect to the database
         * 
         * Legacy method that returns true/false for connection status.
         * Kept for backwards compatibility with older code.
         * 
         * @return bool     True if connected successfully
         */
        public function db_connect()
        {
            return $this->ensure_connected();
        }

        /**
         * Get the mysqli connection object
         * 
         * This is what our models use to run prepared statements.
         * Returns the actual mysqli object so models can do their thing.
         * 
         * @return mysqli|false     The connection object, or false if connection failed
         */
        public function db_conn()
        {
            return $this->ensure_connected() ? $this->db : false;
        }

        /**
         * Run a SELECT query and store the results
         * 
         * Use this for SELECT queries that read data. The results get stored
         * in $this->results for you to fetch afterwards.
         * 
         * Supports prepared statements - just pass parameters as the second argument
         * to protect against SQL injection.
         * 
         * @param string $sqlQuery      The SQL query to run
         * @param array  $params        Optional parameters for prepared statement
         * @return bool                 True if query ran successfully
         */
        public function db_query($sqlQuery, $params = [])
        {
            if (!$this->ensure_connected()) return false;

            // If no parameters, just run the query directly (faster)
            if (empty($params)) {
                $this->results = @mysqli_query($this->db, $sqlQuery);
                if ($this->results === false) {
                    error_log('DB QUERY ERROR: ' . mysqli_error($this->db) . ' | SQL: ' . $sqlQuery);
                    return false;
                }
                return true;
            }

            // Got parameters, so use prepared statements for safety
            $stmt = $this->db->prepare($sqlQuery);
            if (!$stmt) {
                error_log('DB PREPARE ERROR: ' . $this->db->error . ' | SQL: ' . $sqlQuery);
                return false;
            }

            // Bind all the parameters (we treat everything as strings by default)
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // 's' = string type
                $stmt->bind_param($types, ...$params);
            }

            // Execute the prepared statement
            if (!$stmt->execute()) {
                error_log('DB EXECUTE ERROR: ' . $stmt->error . ' | SQL: ' . $sqlQuery);
                $stmt->close();
                return false;
            }

            // Get the results and clean up
            $this->results = $stmt->get_result();
            $stmt->close();
            return true;
        }

        /**
         * Run an INSERT, UPDATE, or DELETE query
         * 
         * Use this for queries that modify data. Returns true if successful.
         * 
         * @param string $sqlQuery      The SQL query to run
         * @return bool                 True if query ran successfully
         */
        public function db_write_query($sqlQuery)
        {
            if (!$this->ensure_connected()) return false;

            $result = @mysqli_query($this->db, $sqlQuery);
            if ($result === false) {
                error_log('DB WRITE ERROR: ' . mysqli_error($this->db) . ' | SQL: ' . $sqlQuery);
                return false;
            }
            return true;
        }

        /**
         * Fetch a single row from a SELECT query
         * 
         * Quick way to get one row. Perfect for "get user by ID" type queries.
         * 
         * Example:
         * $user = $db->db_fetch_one("SELECT * FROM users WHERE user_id = ?", [$userId]);
         * 
         * @param string $sql       The SQL query
         * @param array  $params    Optional parameters for prepared statement
         * @return array|false      The row data as an associative array, or false if not found
         */
        public function db_fetch_one($sql, $params = [])
        {
            if (!$this->db_query($sql, $params)) return false;
            return mysqli_fetch_assoc($this->results);
        }

        /**
         * Fetch all rows from a SELECT query
         * 
         * Gets every row that matches your query. Returns an array of rows.
         * 
         * Example:
         * $schools = $db->db_fetch_all("SELECT * FROM schools WHERE country = ?", ['Ghana']);
         * 
         * @param string $sql       The SQL query
         * @param array  $params    Optional parameters for prepared statement
         * @return array[]|false    Array of rows (each row is an associative array), or false on error
         */
        public function db_fetch_all($sql, $params = [])
        {
            if (!$this->db_query($sql, $params)) return false;
            return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
        }

        /**
         * Count how many rows were returned
         * 
         * Useful after a SELECT to see how many results you got.
         * 
         * @return int|false        Number of rows, or false if no query was run
         */
        public function db_count()
        {
            if ($this->results === null || $this->results === false) return false;
            return mysqli_num_rows($this->results);
        }

        /**
         * Get the ID of the last inserted row
         * 
         * After an INSERT, this tells you the auto-generated ID that was created.
         * Super useful for getting the ID of a user you just created.
         * 
         * @return int              The last inserted ID, or 0 if none
         */
        public function last_insert_id()
        {
            return $this->db ? mysqli_insert_id($this->db) : 0;
        }
    }
}
