<?php
/**
 * Get Cart Count
 * 
 * Quick AJAX endpoint to get the number of items in the user's cart.
 * Used to update the cart badge/counter in the navigation bar without
 * reloading the whole page.
 * 
 * Returns JSON with 'count' (total quantity of all items)
 */

session_start();
header('Content-Type: application/json');

// If not logged in, cart is empty
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$user_id = $_SESSION['user_id'];

try {
    $db = new db_connection();
    
    // Sum up all item quantities in the cart
    $query = "SELECT COALESCE(SUM(quantity), 0) as total_items FROM cart WHERE user_id = ?";
    $result = $db->db_fetch_one($query, [$user_id]);
    
    $count = $result ? intval($result['total_items']) : 0;
    
    echo json_encode(['success' => true, 'count' => $count]);
    
} catch (Exception $e) {
    error_log("Get cart count exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0]);
}
?>
