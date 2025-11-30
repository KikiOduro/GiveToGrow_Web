<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/db_class.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get form data
$need_id = intval($_POST['need_id'] ?? 0);
$item_name = trim($_POST['item_name'] ?? '');
$item_description = trim($_POST['item_description'] ?? '');
$item_category = $_POST['item_category'] ?? '';
$unit_price = floatval($_POST['unit_price'] ?? 0);
$quantity_needed = intval($_POST['quantity_needed'] ?? 0);
$quantity_fulfilled = intval($_POST['quantity_fulfilled'] ?? 0);
$priority = $_POST['priority'] ?? 'medium';
$status = $_POST['status'] ?? 'active';
$image_url = trim($_POST['image_url'] ?? '');

// Validate required fields
if ($need_id <= 0 || empty($item_name) || empty($item_category) || $unit_price <= 0 || $quantity_needed <= 0) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled out correctly.']);
    exit();
}

// Validate priority
if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid priority value.']);
    exit();
}

// Validate status
if (!in_array($status, ['active', 'fulfilled', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit();
}

$db = new db_connection();

try {
    $conn = $db->db_conn();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Check if need exists
    $check_query = "SELECT need_id FROM school_needs WHERE need_id = ?";
    $existing = $db->db_fetch_one($check_query, [$need_id]);
    
    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'School need not found.']);
        exit();
    }
    
    // Update query
    $query = "UPDATE school_needs SET 
              item_name = ?, 
              item_description = ?, 
              item_category = ?, 
              unit_price = ?, 
              quantity_needed = ?, 
              quantity_fulfilled = ?,
              priority = ?, 
              status = ?,
              image_url = ?,
              updated_at = NOW()
              WHERE need_id = ?";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param('sssdiisssi', 
        $item_name,
        $item_description,
        $item_category,
        $unit_price,
        $quantity_needed,
        $quantity_fulfilled,
        $priority,
        $status,
        $image_url,
        $need_id
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'School need updated successfully!']);
    
} catch (Exception $e) {
    error_log("Error updating school need: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
