<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$user_id = $_SESSION['user_id'];

// Get action type
$action = isset($_POST['action']) ? $_POST['action'] : '';
$cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit();
}

// Verify that this cart item belongs to the logged-in user
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
        
        // Update quantity
        $update_query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        $result = $db->db_query($update_query, [$new_quantity, $cart_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }
        break;
        
    case 'remove':
        // Delete the item from cart
        $delete_query = "DELETE FROM cart WHERE cart_id = ?";
        $result = $db->db_query($delete_query, [$cart_id]);
        
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
