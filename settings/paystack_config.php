<?php
/**
 * Paystack Payment Gateway Configuration
 * 
 * This file handles all the setup for Paystack, our payment processor.
 * Paystack is popular in Africa and supports mobile money, cards, and bank transfers.
 * 
 * IMPORTANT: These are TEST keys. Before going live, you need to:
 * 1. Replace with your LIVE keys from the Paystack dashboard
 * 2. Change APP_ENVIRONMENT to 'live'
 * 3. Update APP_BASE_URL to your production domain
 * 
 * Get your API keys at: https://dashboard.paystack.com/#/settings/developers
 */

// API Keys - these are TEST keys, safe for development
// In production, store these in environment variables for security
define('PAYSTACK_SECRET_KEY', 'sk_test_12af6ed3d10227f1df73ab146a6f12805004ff2f');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_06b09b657a6299074b013e4b1d58e036618933fe');

// Paystack API endpoints
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INIT_ENDPOINT', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_ENDPOINT', PAYSTACK_API_URL . '/transaction/verify/');

// App settings - update these for production
define('APP_ENVIRONMENT', 'test'); // Change to 'live' when ready for real payments
define('APP_BASE_URL', 'http://169.239.251.102:442/~akua.oduro');
define('PAYSTACK_CALLBACK_URL', APP_BASE_URL . '/views/paystack_callback.php');

/**
 * Start a new payment transaction with Paystack
 * 
 * This sends the donation amount to Paystack and gets back a URL where
 * the user can complete their payment (enter card details, mobile money, etc.)
 * 
 * @param float $amount The donation amount in GHS (Ghana Cedis)
 * @param string $email The donor's email - Paystack uses this for receipts
 * @param string $reference Optional unique ID for this transaction
 * @return array Response containing authorization_url for redirect
 */
function paystack_initialize_transaction($amount, $email, $reference = null) {
    // Generate a unique reference if none provided
    $reference = $reference ?? 'GTG-' . uniqid();
    
    // Paystack expects amount in pesewas (1 GHS = 100 pesewas)
    // Similar to how Stripe uses cents
    $amount_in_cents = round($amount * 100);
    
    $data = [
        'amount' => $amount_in_cents,
        'email' => $email,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'currency' => 'GHS', // Ghana Cedis
        'metadata' => [
            'app' => 'GiveToGrow',
            'environment' => APP_ENVIRONMENT,
            'custom_fields' => [
                [
                    'display_name' => 'Donation Type',
                    'variable_name' => 'donation_type',
                    'value' => 'School Support'
                ]
            ]
        ]
    ];
    
    $response = paystack_api_request('POST', PAYSTACK_INIT_ENDPOINT, $data);
    
    return $response;
}

/**
 * Verify that a payment was actually successful
 * 
 * After a user completes payment, Paystack redirects them back to our site.
 * We MUST verify the transaction with Paystack's API to confirm it went through.
 * Never trust the redirect alone - always verify!
 * 
 * @param string $reference The transaction reference we created earlier
 * @return array Full transaction details including amount and status
 */
function paystack_verify_transaction($reference) {
    $response = paystack_api_request('GET', PAYSTACK_VERIFY_ENDPOINT . $reference);
    
    return $response;
}

/**
 * Make a request to Paystack's API
 * 
 * This is the workhorse function that actually talks to Paystack.
 * It handles authentication, JSON encoding, and error handling.
 * 
 * @param string $method HTTP method - GET for verify, POST for initialize
 * @param string $url The full Paystack API endpoint
 * @param array $data Optional data payload for POST requests
 * @return array Decoded JSON response from Paystack
 */
function paystack_api_request($method, $url, $data = null) {
    $ch = curl_init();
    
    // Set up the request
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);  // Don't wait forever
    
    // Authenticate with our secret key
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Send data for POST/PUT requests
    if ($method !== 'GET' && $data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    // Handle curl errors
    if ($curl_error) {
        error_log("Paystack API CURL Error: $curl_error");
        return [
            'status' => false,
            'message' => 'Connection error: ' . $curl_error
        ];
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Log for debugging
    error_log("Paystack API Response (HTTP $http_code): " . json_encode($result));
    
    return $result;
}

/**
 * Get currency symbol for display
 */
function get_currency_symbol($currency = 'GHS') {
    $symbols = [
        'USD' => '$',
        'GHS' => '₵',
        'EUR' => '€',
        'NGN' => '₦',
        'KES' => 'KSh',
        'ZAR' => 'R'
    ];
    
    return $symbols[$currency] ?? $currency;
}
?>
