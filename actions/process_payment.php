<?php
/**
 * Process Payment Handler
 * 
 * This handles the direct payment processing flow (non-Paystack).
 * It creates donation records for each cart item, updates school funding
 * totals, and clears the user's cart after a successful payment.
 * 
 * Note: This is a simplified payment flow. In production, you'd want to
 * integrate with a real payment gateway before creating donation records.
 */

session_start();
header('Content-Type: application/json');

// User must be logged in to make a payment
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$user_id = $_SESSION['user_id'];

// Collect the billing/contact information from the form
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
        
        // Insert into donations table - using actual column names from the schema
        $insert_donation = "INSERT INTO donations 
                           (user_id, school_id, need_id, amount, donation_type, quantity, 
                            payment_method, payment_status, transaction_id) 
                           VALUES (?, ?, ?, ?, 'item', ?, ?, 'completed', ?)";
        
        // Get the connection and prepare statement
        $conn = $db->db_conn();
        $stmt = $conn->prepare($insert_donation);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare donation insert: ' . $conn->error);
        }
        
        // Generate a transaction ID for tracking
        $transaction_id = 'TXN_' . time() . '_' . $user_id . '_' . rand(1000, 9999);
        
        // Bind parameters: i=integer, d=double/decimal, s=string
        $stmt->bind_param('iiidiss', 
            $user_id,
            $item['school_id'],
            $item['need_id'],
            $donation_amount,
            $item['quantity'],
            $payment_method,
            $transaction_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute donation insert: ' . $stmt->error);
        }
        
        // Get the auto-generated donation_id
        $donation_ids[] = $stmt->insert_id;
        $stmt->close();
        
        // Update quantity_fulfilled in school_needs
        $update_fulfilled = "UPDATE school_needs 
                            SET quantity_fulfilled = COALESCE(quantity_fulfilled, 0) + ? 
                            WHERE need_id = ?";
        $stmt2 = $conn->prepare($update_fulfilled);
        $stmt2->bind_param('ii', $item['quantity'], $item['need_id']);
        $stmt2->execute();
        $stmt2->close();
        
        // Update amount_raised in schools table
        $update_school = "UPDATE schools 
                         SET amount_raised = amount_raised + ? 
                         WHERE school_id = ?";
        $stmt3 = $conn->prepare($update_school);
        $stmt3->bind_param('di', $donation_amount, $item['school_id']);
        $stmt3->execute();
        $stmt3->close();
    }
    
    // Clear the user's cart
    $clear_cart = "DELETE FROM cart WHERE user_id = ?";
    $stmt4 = $conn->prepare($clear_cart);
    $stmt4->bind_param('i', $user_id);
    $stmt4->execute();
    $stmt4->close();
    
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
