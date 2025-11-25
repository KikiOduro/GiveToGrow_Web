<?php
/**
 * Paystack Payment Verification Handler
 * Handles payment verification and donation processing
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../settings/paystack_config.php';
require_once __DIR__ . '/../settings/db_class.php';

error_log("=== PAYSTACK PAYMENT VERIFICATION ===");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit();
}

// Get verification reference from POST data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment reference provided'
    ]);
    exit();
}

try {
    $db = new db_connection();
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'Donor';
    $user_email = $_SESSION['user_email'] ?? '';
    
    error_log("Verifying Paystack transaction - Reference: $reference, User: $user_id");
    
    // Verify transaction with Paystack
    $verification_response = paystack_verify_transaction($reference);
    
    if (!$verification_response) {
        throw new Exception("No response from Paystack verification API");
    }
    
    error_log("Paystack verification response: " . json_encode($verification_response));
    
    // Check if verification was successful
    if (!isset($verification_response['status']) || $verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        error_log("Payment verification failed: $error_msg");
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg,
            'verified' => false
        ]);
        exit();
    }
    
    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0; // Convert from cents
    $customer_email = $transaction_data['customer']['email'] ?? '';
    $authorization = $transaction_data['authorization'] ?? [];
    $authorization_code = $authorization['authorization_code'] ?? '';
    $payment_method = $authorization['channel'] ?? 'card';
    $currency = $transaction_data['currency'] ?? 'USD';
    
    error_log("Transaction status: $payment_status, Amount: $amount_paid $currency");
    
    // Validate payment status
    if ($payment_status !== 'success') {
        error_log("Payment status is not successful: $payment_status");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_status),
            'verified' => false,
            'payment_status' => $payment_status
        ]);
        exit();
    }
    
    // Fetch cart items
    $cart_query = "SELECT c.cart_id, c.need_id, c.quantity, 
                          sn.item_name, sn.unit_price, sn.school_id,
                          s.school_name
                   FROM cart c
                   JOIN school_needs sn ON c.need_id = sn.need_id
                   JOIN schools s ON sn.school_id = s.school_id
                   WHERE c.user_id = ?";
    $cart_items = $db->db_fetch_all($cart_query, [$user_id]);
    
    if (empty($cart_items)) {
        throw new Exception("Cart is empty");
    }
    
    // Calculate expected total
    $calculated_total = 0.00;
    foreach ($cart_items as $item) {
        $calculated_total += floatval($item['unit_price']) * intval($item['quantity']);
    }
    
    error_log("Expected cart total: $calculated_total");
    
    // Verify amount matches (with small tolerance)
    if (abs($amount_paid - $calculated_total) > 0.01) {
        error_log("Amount mismatch - Expected: $calculated_total, Paid: $amount_paid");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match cart total',
            'verified' => false,
            'expected' => number_format($calculated_total, 2),
            'paid' => number_format($amount_paid, 2)
        ]);
        exit();
    }
    
    // Payment is verified! Process donations
    $conn = $db->db_conn();
    mysqli_begin_transaction($conn);
    error_log("Database transaction started");
    
    try {
        $donation_ids = [];
        $transaction_date = date('Y-m-d H:i:s');
        
        // Create donation records for each cart item
        foreach ($cart_items as $item) {
            $donation_amount = $item['unit_price'] * $item['quantity'];
            
            $donation_query = "INSERT INTO donations 
                              (user_id, school_id, need_id, amount, quantity, payment_method, 
                               payment_status, donor_name, donor_email, transaction_date, is_anonymous) 
                              VALUES (?, ?, ?, ?, ?, ?, 'completed', ?, ?, ?, 0)";
            
            $result = $db->db_query($donation_query, [
                $user_id,
                $item['school_id'],
                $item['need_id'],
                $donation_amount,
                $item['quantity'],
                $payment_method,
                $user_name,
                $customer_email,
                $transaction_date
            ]);
            
            if ($result) {
                $donation_id = mysqli_insert_id($conn);
                $donation_ids[] = $donation_id;
                error_log("Donation created - ID: $donation_id, Need: {$item['need_id']}, Qty: {$item['quantity']}");
                
                // Update quantity_fulfilled in school_needs
                $update_need_query = "UPDATE school_needs 
                                     SET quantity_fulfilled = quantity_fulfilled + ? 
                                     WHERE need_id = ?";
                $db->db_query($update_need_query, [$item['quantity'], $item['need_id']]);
                
                // Update amount_raised in schools
                $update_school_query = "UPDATE schools 
                                       SET amount_raised = amount_raised + ? 
                                       WHERE school_id = ?";
                $db->db_query($update_school_query, [$donation_amount, $item['school_id']]);
            } else {
                throw new Exception("Failed to create donation for need: {$item['need_id']}");
            }
        }
        
        // Clear the cart
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $db->db_query($clear_cart_query, [$user_id]);
        error_log("Cart cleared for user: $user_id");
        
        // Commit transaction
        mysqli_commit($conn);
        error_log("Database transaction committed successfully");
        
        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_timestamp']);
        
        // Get primary donation ID for redirect
        $primary_donation_id = $donation_ids[0] ?? null;
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Payment successful! Your donation has been recorded.',
            'donation_id' => $primary_donation_id,
            'donation_count' => count($donation_ids),
            'total_amount' => number_format($amount_paid, 2),
            'currency' => $currency,
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_method),
            'transaction_date' => date('F j, Y', strtotime($transaction_date))
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        error_log("Database transaction rolled back: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in Paystack verification: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment processing error: ' . $e->getMessage()
    ]);
}
?>
