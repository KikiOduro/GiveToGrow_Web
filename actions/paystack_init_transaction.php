<?php
/**
 * Paystack Payment Initialization
 * 
 * This script kicks off the payment process with Paystack.
 * It creates a transaction reference and returns an authorization URL
 * where the user will complete their payment.
 * 
 * Expected JSON input:
 * - amount: The donation amount in GHS
 * - email: The donor's email address
 * 
 * Returns JSON with authorization_url on success, or error message on failure.
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../settings/paystack_config.php';

error_log("=== PAYSTACK INITIALIZE TRANSACTION ===");

// User must be logged in to make a payment
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to complete payment'
    ]);
    exit();
}

// Parse the JSON request body
$input = json_decode(file_get_contents('php://input'), true);
$amount = isset($input['amount']) ? floatval($input['amount']) : 0;
$customer_email = isset($input['email']) ? trim($input['email']) : '';
$platform_fee = isset($input['platform_fee']) ? floatval($input['platform_fee']) : 0;
$donation_amount = isset($input['donation_amount']) ? floatval($input['donation_amount']) : $amount;

if (!$amount || !$customer_email) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid amount or email'
    ]);
    exit();
}

// Validate amount
if ($amount <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Amount must be greater than 0'
    ]);
    exit();
}

// Validate email
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email address'
    ]);
    exit();
}

try {
    // Generate unique reference
    $user_id = $_SESSION['user_id'];
    $reference = 'GTG-' . $user_id . '-' . time();
    
    error_log("Initializing transaction - User: $user_id, Amount: $amount USD, Email: $customer_email");
    
    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($amount, $customer_email, $reference);
    
    if (!$paystack_response) {
        throw new Exception("No response from Paystack API");
    }
    
    if (isset($paystack_response['status']) && $paystack_response['status'] === true) {
        // Store transaction reference in session for verification later
        $_SESSION['paystack_ref'] = $reference;
        $_SESSION['paystack_amount'] = $amount;
        $_SESSION['paystack_platform_fee'] = $platform_fee;
        $_SESSION['paystack_donation_amount'] = $donation_amount;
        $_SESSION['paystack_timestamp'] = time();
        
        error_log("Paystack transaction initialized successfully - Reference: $reference");
        
        echo json_encode([
            'status' => 'success',
            'authorization_url' => $paystack_response['data']['authorization_url'],
            'reference' => $reference,
            'access_code' => $paystack_response['data']['access_code'],
            'message' => 'Redirecting to payment gateway...'
        ]);
    } else {
        error_log("Paystack API error: " . json_encode($paystack_response));
        
        $error_message = $paystack_response['message'] ?? 'Payment gateway error';
        throw new Exception($error_message);
    }
    
} catch (Exception $e) {
    error_log("Error initializing Paystack transaction: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to initialize payment: ' . $e->getMessage()
    ]);
}
?>
