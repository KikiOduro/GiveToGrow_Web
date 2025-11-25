<?php
session_start();
header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in response
ini_set('log_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$user_id = $_SESSION['user_id'];
$need_id = isset($_POST['need_id']) ? intval($_POST['need_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Log the request
error_log("Add to cart request - User: $user_id, Need: $need_id, Quantity: $quantity");

if ($need_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item or quantity']);
    exit();
}

try {
    $db = new db_connection();

    // Check if item exists and is active
    $check_query = "SELECT * FROM school_needs WHERE need_id = ? AND status = 'active'";
    $need = $db->db_fetch_one($check_query, [$need_id]);

    if (!$need) {
        echo json_encode(['success' => false, 'message' => 'Item not found or no longer available']);
        exit();
    }

    // Check if item already in cart
    $cart_check = "SELECT * FROM cart WHERE user_id = ? AND need_id = ?";
    $existing = $db->db_fetch_one($cart_check, [$user_id, $need_id]);

    if ($existing) {
        // Update quantity
        $update_query = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND need_id = ?";
        $result = $db->db_query($update_query, [$quantity, $user_id, $need_id]);
        error_log("Updated cart - Cart ID: {$existing['cart_id']}, New total quantity");
    } else {
        // Insert new cart item
        $insert_query = "INSERT INTO cart (user_id, need_id, quantity) VALUES (?, ?, ?)";
        $result = $db->db_query($insert_query, [$user_id, $need_id, $quantity]);
        error_log("Inserted new cart item - User: $user_id, Need: $need_id");
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    } else {
        error_log("Database operation failed");
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
    
} catch (Exception $e) {
    error_log("Add to cart exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
