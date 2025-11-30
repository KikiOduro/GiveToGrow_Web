<?php
/**
 * Delete School Action Handler
 * 
 * Removes a school and all its related data from the system. This is an
 * admin-only action that performs a cascading delete to keep the database
 * clean and consistent.
 * 
 * Why we need a transaction here:
 * A school has related records in multiple tables (donations, cart items,
 * needs). We delete them all in one transaction so if anything fails,
 * we don't end up with orphaned records pointing to a deleted school.
 * 
 * Delete order (important for foreign key constraints):
 * 1. Donations for this school
 * 2. Cart items for this school's needs
 * 3. The school's needs themselves
 * 4. Finally, the school record
 * 
 * Returns JSON response for AJAX handling.
 */

session_start();
header('Content-Type: application/json');

// Only admins can delete schools - this is destructive!
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
    
    // Use a transaction so everything succeeds or fails together
    $conn->begin_transaction();
    
    try {
        // Step 1: Remove donations - we're losing this history but that's what delete means
        $delete_donations = "DELETE FROM donations WHERE school_id = ?";
        $stmt = $conn->prepare($delete_donations);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Step 2: Clear cart items that reference this school's needs
        $delete_cart = "DELETE FROM cart WHERE need_id IN (SELECT need_id FROM school_needs WHERE school_id = ?)";
        $stmt = $conn->prepare($delete_cart);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Step 3: Remove all the school's needs (textbooks, supplies, etc.)
        $delete_needs = "DELETE FROM school_needs WHERE school_id = ?";
        $stmt = $conn->prepare($delete_needs);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Step 4: Finally, delete the school itself
        $delete_school = "DELETE FROM schools WHERE school_id = ?";
        $stmt = $conn->prepare($delete_school);
        $stmt->bind_param('i', $school_id);
        $stmt->execute();
        $stmt->close();
        
        // Everything worked - make it permanent
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'School deleted successfully']);
        
    } catch (Exception $e) {
        // Something went wrong - undo all the deletes
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Delete school error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
