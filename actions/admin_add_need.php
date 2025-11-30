<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../settings/db_class.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../admin/dashboard.php");
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: ../admin/add_need.php");
    exit();
}

// Get form data
$school_id = intval($_POST['school_id'] ?? 0);
$item_name = trim($_POST['item_name'] ?? '');
$item_description = trim($_POST['item_description'] ?? '');
$item_category = $_POST['item_category'] ?? '';
$unit_price = floatval($_POST['unit_price'] ?? 0);
$quantity_needed = intval($_POST['quantity_needed'] ?? 0);
$image_url = trim($_POST['image_url'] ?? '');
$priority = $_POST['priority'] ?? 'medium';

// Debug: Log what we received
error_log("ADD NEED - Received image_url: " . $image_url);
error_log("ADD NEED - POST data: " . print_r($_POST, true));

// Validate required fields
if ($school_id <= 0 || empty($item_name) || empty($item_category) || $unit_price <= 0 || $quantity_needed <= 0 || empty($image_url)) {
    $_SESSION['error_message'] = "All required fields must be filled out correctly.";
    header("Location: ../admin/add_need.php" . ($school_id > 0 ? "?school_id=" . $school_id : ""));
    exit();
}

// Insert school need into database
$db = new db_connection();

try {
    $conn = $db->db_conn();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $query = "INSERT INTO school_needs (school_id, item_name, item_description, item_category, unit_price, quantity_needed, image_url, priority) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param('isssdiis', 
        $school_id,
        $item_name,
        $item_description,
        $item_category,
        $unit_price,
        $quantity_needed,
        $image_url,
        $priority
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
    $_SESSION['success_message'] = "School need '$item_name' added successfully!";
    header("Location: ../admin/add_need.php?school_id=" . $school_id);
} catch (Exception $e) {
    error_log("Error adding school need: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while adding the school need: " . $e->getMessage();
    header("Location: ../admin/add_need.php?school_id=" . $school_id);
}

exit();
?>
