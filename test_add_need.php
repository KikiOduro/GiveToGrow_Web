<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/settings/db_class.php';

$db = new db_connection();

echo "<h2>Test Database Connection and School Needs Table</h2>";

// Test connection
$conn = $db->db_conn();
if (!$conn) {
    echo "<p style='color:red'>Database connection FAILED</p>";
    exit;
}
echo "<p style='color:green'>Database connection OK</p>";

// Check if school_needs table exists
$table_check = $db->db_fetch_one("SHOW TABLES LIKE 'school_needs'");
if (!$table_check) {
    echo "<p style='color:red'>school_needs table does NOT exist!</p>";
    echo "<h3>Creating table...</h3>";
    
    // Create the table
    $create_sql = "
    CREATE TABLE IF NOT EXISTS school_needs (
        need_id INT AUTO_INCREMENT PRIMARY KEY,
        school_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        item_description TEXT,
        item_category ENUM('Books', 'Desks', 'Supplies', 'Technology', 'Water', 'Other') NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        quantity_needed INT NOT NULL,
        quantity_fulfilled INT DEFAULT 0,
        image_url VARCHAR(500),
        priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        status ENUM('active', 'fulfilled', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (school_id) REFERENCES schools(school_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $result = $db->db_write_query($create_sql);
    echo $result ? "<p style='color:green'>Table created successfully!</p>" : "<p style='color:red'>Failed to create table</p>";
} else {
    echo "<p style='color:green'>school_needs table exists</p>";
}

// Show table structure
echo "<h3>Table Structure:</h3>";
$columns = $db->db_fetch_all("DESCRIBE school_needs");
if ($columns) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for schools
echo "<h3>Available Schools:</h3>";
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools LIMIT 5");
if ($schools && count($schools) > 0) {
    echo "<ul>";
    foreach ($schools as $school) {
        echo "<li>ID: " . $school['school_id'] . " - " . htmlspecialchars($school['school_name']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>No schools found!</p>";
}

// Test insert with a school that exists
if ($schools && count($schools) > 0) {
    echo "<h3>Test Insert (dry run):</h3>";
    $test_school_id = $schools[0]['school_id'];
    
    $query = "INSERT INTO school_needs (school_id, item_name, item_description, item_category, unit_price, quantity_needed, image_url, priority) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        echo "<p style='color:red'>Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
    } else {
        echo "<p style='color:green'>Statement prepared successfully</p>";
        
        $item_name = "Test Item";
        $item_description = "Test description";
        $item_category = "Books";
        $unit_price = 10.00;
        $quantity_needed = 5;
        $image_url = "https://example.com/test.jpg";
        $priority = "medium";
        
        $stmt->bind_param('isssdiis', 
            $test_school_id,
            $item_name,
            $item_description,
            $item_category,
            $unit_price,
            $quantity_needed,
            $image_url,
            $priority
        );
        
        echo "<p>Binding parameters: school_id=$test_school_id, item_name=$item_name, category=$item_category, price=$unit_price, qty=$quantity_needed</p>";
        
        // Uncomment to actually test the insert
        // $result = $stmt->execute();
        // echo $result ? "<p style='color:green'>Insert successful!</p>" : "<p style='color:red'>Insert failed: " . $stmt->error . "</p>";
        
        $stmt->close();
        echo "<p>Test complete - uncomment the execute line to actually insert</p>";
    }
}
?>
