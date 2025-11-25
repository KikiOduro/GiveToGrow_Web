<?php
// settings/db_class.php
// Shared DB helper for GiveToGrow project

include_once __DIR__ . '/db_cred.php';

/**
 * @version 2.1 (MAMP-safe, port/socket aware, utf8mb4)
 */
if (!class_exists('db_connection')) {
    class db_connection
    {
        /** @var mysqli|null */
        public $db = null;

        /** @var mysqli_result|false|null */
        public $results = null;

        /**
         * Ensure we have a live mysqli connection.
         *
         * @return bool true if connected, false on failure
         */
        private function ensure_connected(): bool
        {
            // If already connected, verify it's alive
            // If already connected, verify it's alive
            if ($this->db instanceof mysqli) {
                $alive = @$this->db->query("SELECT 1");
                if ($alive !== false) {
                    return true;
                }
            }

            // Connection credentials from db_cred.php (or defaults)
            $host   = defined('SERVER')   ? SERVER   : '127.0.0.1';
            $user   = defined('USERNAME') ? USERNAME : 'root';
            $pass   = defined('PASSWD')   ? PASSWD   : '';
            $db     = defined('DATABASE') ? DATABASE : '';
            $port   = defined('PORT')     ? PORT     : 3306;
            $socket = (defined('SOCKET') && SOCKET) ? SOCKET : null;

            // Try TCP (host + port) first
            $link = @mysqli_connect($host, $user, $pass, $db, $port);

            // If that fails and a socket is defined (e.g., MAMP), try socket
            if (!$link && $socket) {
                $link = @mysqli_connect(null, $user, $pass, $db, null, $socket);
            }

            if (!$link) {
                // Log a useful error (check PHP error log)
                error_log('DB CONNECT ERROR: ' . mysqli_connect_error());
                $this->db = null;
                return false;
            }

            // Set UTF-8 charset
            if (!@mysqli_set_charset($link, 'utf8mb4')) {
                error_log('DB CHARSET ERROR: ' . mysqli_error($link));
            }

            $this->db = $link;
            return true;
        }

        /**
         * Backwards-compat: returns boolean like your original.
         * Use this when you just want "true/false" for connection.
         */
        public function db_connect()
        {
            return $this->ensure_connected();
        }

        /**
         * Get the mysqli connection (or false if connection fails).
         * This is what CustomerModel uses.
         *
         * @return mysqli|false
         */
        public function db_conn()
        {
            return $this->ensure_connected() ? $this->db : false;
        }

        /**
         * Run a SELECT (or read) query; sets $this->results.
         * Supports prepared statements with parameters.
         *
         * @param string $sqlQuery
         * @param array $params Optional parameters for prepared statement
         * @return bool
         */
        public function db_query($sqlQuery, $params = [])
        {
            if (!$this->ensure_connected()) return false;

            // If no params, use regular query
            if (empty($params)) {
                $this->results = @mysqli_query($this->db, $sqlQuery);
                if ($this->results === false) {
                    error_log('DB QUERY ERROR: ' . mysqli_error($this->db) . ' | SQL: ' . $sqlQuery);
                    return false;
                }
                return true;
            }

            // Use prepared statement
            $stmt = $this->db->prepare($sqlQuery);
            if (!$stmt) {
                error_log('DB PREPARE ERROR: ' . $this->db->error . ' | SQL: ' . $sqlQuery);
                return false;
            }

            // Bind parameters dynamically
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Default to string type
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                error_log('DB EXECUTE ERROR: ' . $stmt->error . ' | SQL: ' . $sqlQuery);
                $stmt->close();
                return false;
            }

            $this->results = $stmt->get_result();
            $stmt->close();
            return true;
        }

        /**
         * Run INSERT/UPDATE/DELETE.
         *
         * @param string $sqlQuery
         * @return bool
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
         * Fetch one row from a SELECT.
         * Supports prepared statements with parameters.
         *
         * @param string $sql
         * @param array $params Optional parameters for prepared statement
         * @return array|false
         */
        public function db_fetch_one($sql, $params = [])
        {
            if (!$this->db_query($sql, $params)) return false;
            return mysqli_fetch_assoc($this->results);
        }

        /**
         * Fetch all rows from a SELECT.
         * Supports prepared statements with parameters.
         *
         * @param string $sql
         * @param array $params Optional parameters for prepared statement
         * @return array[]|false
         */
        public function db_fetch_all($sql, $params = [])
        {
            if (!$this->db_query($sql, $params)) return false;
            return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
        }

        /**
         * Count rows from the last SELECT.
         *
         * @return int|false
         */
        public function db_count()
        {
            if ($this->results === null || $this->results === false) return false;
            return mysqli_num_rows($this->results);
        }

        /**
         * Last auto-increment id.
         *
         * @return int
         */
        public function last_insert_id()
        {
            return $this->db ? mysqli_insert_id($this->db) : 0;
        }
    }
}
