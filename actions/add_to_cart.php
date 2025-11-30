<?php
/**
 * Add to Cart Handler
 * 
 * Handles AJAX requests to add school needs to a user's donation cart.
 * Returns JSON responses for frontend handling.
 * 
 * Expected POST data:
 * - need_id: The ID of the school need to add
 * - quantity: How many items to add (defaults to 1)
 */

session_start();
header('Content-Type: application/json');

// Enable error logging but hide errors from users (security best practice)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Make sure user is logged in before they can add to cart
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

// Grab and sanitize the input values
$user_id = $_SESSION['user_id'];
$need_id = isset($_POST['need_id']) ? intval($_POST['need_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

error_log("Add to cart request - User: $user_id, Need: $need_id, Quantity: $quantity");

// Basic validation - can't add nothing or negative amounts
if ($need_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item or quantity']);
    exit();
}

try {
    $db = new db_connection();

    // First, make sure this item actually exists and is still available
    $check_query = "SELECT * FROM school_needs WHERE need_id = ? AND status = 'active'";
    $need = $db->db_fetch_one($check_query, [$need_id]);

    if (!$need) {
        echo json_encode(['success' => false, 'message' => 'Item not found or no longer available']);
        exit();
    }

    // Check if this item is already in their cart
    // If so, we'll just bump up the quantity instead of adding a duplicate
    $cart_check = "SELECT * FROM cart WHERE user_id = ? AND need_id = ?";
    $existing = $db->db_fetch_one($cart_check, [$user_id, $need_id]);

    $conn = $db->db_conn();
    
    if ($existing) {
        // Item already in cart - just increase the quantity
        $new_quantity = $existing['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ii', $new_quantity, $existing['cart_id']);
        $result = $stmt->execute();
        $stmt->close();
        error_log("Updated cart - Cart ID: {$existing['cart_id']}, New quantity: $new_quantity");
    } else {
        // New item - add it to the cart
        $insert_query = "INSERT INTO cart (user_id, need_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database error']);
            exit();
        }
        
        $stmt->bind_param('iii', $user_id, $need_id, $quantity);
        $result = $stmt->execute();
        $cart_id = $stmt->insert_id;
        $stmt->close();
        error_log("Inserted new cart item - Cart ID: $cart_id, User: $user_id, Need: $need_id, Quantity: $quantity");
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    } else {
        error_log("Database operation failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
    
} catch (Exception $e) {
    error_log("Add to cart exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
