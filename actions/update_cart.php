<?php
/**
 * Cart Update Handler
 * 
 * Handles AJAX requests to modify cart items - either updating the quantity
 * or removing items entirely. Used by the cart page's +/- buttons and
 * the remove button.
 * 
 * Expected POST data:
 * - action: 'update_quantity' or 'remove'
 * - cart_id: Which cart item to modify
 * - change: (for update_quantity) How much to add/subtract (e.g., +1 or -1)
 */

session_start();
header('Content-Type: application/json');

// Must be logged in to modify cart
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$user_id = $_SESSION['user_id'];

// What are we doing? Update quantity or remove?
$action = isset($_POST['action']) ? $_POST['action'] : '';
$cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

// Security check: Make sure this cart item actually belongs to this user
// Prevents users from modifying other people's carts!
$verify_query = "SELECT * FROM cart WHERE cart_id = ? AND user_id = ?";
$cart_item = $db->db_fetch_one($verify_query, [$cart_id, $user_id]);

if (!$cart_item) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit();
}

switch ($action) {
    case 'update_quantity':
        $change = isset($_POST['change']) ? intval($_POST['change']) : 0;
        $new_quantity = $cart_item['quantity'] + $change;
        
        // Ensure quantity is at least 1
        if ($new_quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
            exit();
        }
        
        // Check if the new quantity exceeds available quantity
        $need_query = "SELECT quantity_needed, quantity_fulfilled FROM school_needs WHERE need_id = ?";
        $need = $db->db_fetch_one($need_query, [$cart_item['need_id']]);
        
        if ($need) {
            $available = $need['quantity_needed'] - ($need['quantity_fulfilled'] ?? 0);
            if ($new_quantity > $available) {
                echo json_encode(['success' => false, 'message' => "Only $available items available"]);
                exit();
            }
        }
        
        // Update quantity using prepared statement
        $conn = $db->db_conn();
        $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ii', $new_quantity, $cart_id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }
        break;
        
    case 'remove':
        // Delete the item from cart using prepared statement
        $conn = $db->db_conn();
        $delete_query = "DELETE FROM cart WHERE cart_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $cart_id);
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
