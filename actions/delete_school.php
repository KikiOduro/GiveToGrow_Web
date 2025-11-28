<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : 0;

if ($school_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid school ID']);
    exit();
}

try {
    $db = new db_connection();
    $conn = $db->db_conn();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete all donations associated with this school
        $delete_donations = "DELETE FROM donations WHERE school_id = ?";
        $stmt = $conn->prepare($delete_donations);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete all cart items associated with needs from this school
        $delete_cart = "DELETE FROM cart WHERE need_id IN (SELECT need_id FROM school_needs WHERE school_id = ?)";
        $stmt = $conn->prepare($delete_cart);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete all school needs
        $delete_needs = "DELETE FROM school_needs WHERE school_id = ?";
        $stmt = $conn->prepare($delete_needs);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Finally, delete the school
        $delete_school = "DELETE FROM schools WHERE school_id = ?";
        $stmt = $conn->prepare($delete_school);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'School deleted successfully']);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Delete school error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
