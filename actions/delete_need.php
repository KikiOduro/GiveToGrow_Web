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

// Get need ID
$need_id = intval($_POST['need_id'] ?? 0);

if ($need_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid need ID.']);
    exit();
}

$db = new db_connection();

try {
    $conn = $db->db_conn();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Check if need exists
    $check_query = "SELECT need_id, item_name FROM school_needs WHERE need_id = ?";
    $existing = $db->db_fetch_one($check_query, [$need_id]);
    
    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'School need not found.']);
        exit();
    }
    
    // remove any cart items referencing this need
    $delete_cart = "DELETE FROM cart WHERE need_id = ?";
    $stmt = $conn->prepare($delete_cart);
    if ($stmt) {
        $stmt->bind_param('i', $need_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete the need
    $delete_query = "DELETE FROM school_needs WHERE need_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param('i', $need_id);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'School need deleted successfully!']);
    
} catch (Exception $e) {
    error_log("Error deleting school need: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
