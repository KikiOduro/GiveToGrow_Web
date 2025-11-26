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

// Get form data
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'mobile_money';
$mobile_provider = isset($_POST['mobile_provider']) ? $_POST['mobile_provider'] : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';

// Validate required fields
if (empty($full_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

if ($payment_method === 'mobile_money' && empty($phone_number)) {
    echo json_encode(['success' => false, 'message' => 'Please enter your phone number']);
    exit();
}

// Fetch all cart items for this user
$cart_query = "SELECT c.cart_id, c.need_id, c.quantity, 
                      sn.item_name, sn.unit_price, sn.school_id
               FROM cart c
               JOIN school_needs sn ON c.need_id = sn.need_id
               WHERE c.user_id = ?";
$cart_items = $db->db_fetch_all($cart_query, [$user_id]);

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit();
}

// Calculate total
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += ($item['unit_price'] * $item['quantity']);
}

// TODO: Integrate with actual payment gateway (Paystack, PayPal, Mobile Money API)
// For now, we'll simulate a successful payment

// Begin transaction
$db->db_query("START TRANSACTION");

try {
    // Create donation records for each cart item
    $donation_ids = [];
    
    foreach ($cart_items as $item) {
        $donation_amount = $item['unit_price'] * $item['quantity'];
        
        // Insert into donations table
        $insert_donation = "INSERT INTO donations (user_id, school_id, need_id, amount, quantity, payment_method, payment_status, donor_name, donor_email, transaction_date) 
                           VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, ?, NOW())";
        
        $result = $db->db_query($insert_donation, [
            $user_id,
            $item['school_id'],
            $item['need_id'],
            $donation_amount,
            $item['quantity'],
            $payment_method,
            $full_name,
            $email
        ]);
        
        if (!$result) {
            throw new Exception('Failed to create donation record');
        }
        
        // Get the last inserted donation_id
        $donation_id_query = "SELECT LAST_INSERT_ID() as donation_id";
        $donation_result = $db->db_fetch_one($donation_id_query);
        $donation_ids[] = $donation_result['donation_id'];
        
        // Update quantity_fulfilled in school_needs
        $update_fulfilled = "UPDATE school_needs 
                            SET quantity_fulfilled = COALESCE(quantity_fulfilled, 0) + ? 
                            WHERE need_id = ?";
        $db->db_query($update_fulfilled, [$item['quantity'], $item['need_id']]);
        
        // Update amount_raised in schools table
        $update_school = "UPDATE schools 
                         SET amount_raised = amount_raised + ? 
                         WHERE school_id = ?";
        $db->db_query($update_school, [$donation_amount, $item['school_id']]);
    }
    
    // Clear the user's cart
    $clear_cart = "DELETE FROM cart WHERE user_id = ?";
    $db->db_query($clear_cart, [$user_id]);
    
    // Commit transaction
    $db->db_query("COMMIT");
    
    // Return success with the first donation ID (for receipt)
    echo json_encode([
        'success' => true, 
        'message' => 'Payment successful!',
        'donation_id' => $donation_ids[0],
        'total_amount' => $total_amount
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $db->db_query("ROLLBACK");
    error_log('Payment processing error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Payment processing failed. Please try again.']);
}
?>
