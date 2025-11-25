<?php
// Test database connectivity and table existence
require_once __DIR__ . '/settings/db_class.php';

$db = new db_connection();

echo "<h2>Database Connection Test</h2>";

// Check if schools table exists
$test_query = "SHOW TABLES LIKE 'schools'";
$result = $db->db_fetch_one($test_query);
echo "<p>Schools table exists: " . ($result ? "✅ YES" : "❌ NO") . "</p>";

// Check if school_needs table exists
$test_query = "SHOW TABLES LIKE 'school_needs'";
$result = $db->db_fetch_one($test_query);
echo "<p>School_needs table exists: " . ($result ? "✅ YES" : "❌ NO") . "</p>";

// Check if cart table exists
$test_query = "SHOW TABLES LIKE 'cart'";
$result = $db->db_fetch_one($test_query);
echo "<p>Cart table exists: " . ($result ? "✅ YES" : "❌ NO") . "</p>";

// Check if donations table exists
$test_query = "SHOW TABLES LIKE 'donations'";
$result = $db->db_fetch_one($test_query);
echo "<p>Donations table exists: " . ($result ? "✅ YES" : "❌ NO") . "</p>";

// If cart exists, check its structure
$test_query = "SHOW TABLES LIKE 'cart'";
$cart_exists = $db->db_fetch_one($test_query);

if ($cart_exists) {
    echo "<h3>Cart Table Structure:</h3>";
    $columns_query = "DESCRIBE cart";
    $columns = $db->db_fetch_all($columns_query);
    if ($columns) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>
