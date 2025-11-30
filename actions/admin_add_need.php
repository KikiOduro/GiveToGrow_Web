<?php
/**
 * Admin: Add School Need
 * 
 * Handles the form submission when an admin adds a new need/item for a school.
 * Each need represents something the school requires - like textbooks,
 * computers, or furniture - that donors can contribute towards.
 * 
 * Required fields: school_id, item_name, category, unit_price, quantity, image_url
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../settings/db_class.php';

// Only admins can add school needs
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../admin/dashboard.php");
    exit();
}

// This endpoint only accepts POST requests from the form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: ../admin/add_need.php");
    exit();
}

// Collect all the form data
$school_id = intval($_POST['school_id'] ?? 0);
$item_name = trim($_POST['item_name'] ?? '');
$item_description = trim($_POST['item_description'] ?? '');
$item_category = $_POST['item_category'] ?? '';
$unit_price = floatval($_POST['unit_price'] ?? 0);
$quantity_needed = intval($_POST['quantity_needed'] ?? 0);
$priority = $_POST['priority'] ?? 'medium';

// Get the image URL - this should be a direct link to the item image
$image_url = '';
if (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
    $image_url = trim($_POST['image_url']);
}

// Debug logging - helps track down issues during development
error_log("ADD NEED - Received image_url: '" . $image_url . "'");
error_log("ADD NEED - POST data: " . print_r($_POST, true));
error_log("ADD NEED - Raw POST image_url isset: " . (isset($_POST['image_url']) ? 'yes' : 'no'));
error_log("ADD NEED - Raw POST image_url value: " . (isset($_POST['image_url']) ? $_POST['image_url'] : 'NOT SET'));

// Make sure all required fields are filled in
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
    
    $stmt->bind_param('isssdiss', 
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
