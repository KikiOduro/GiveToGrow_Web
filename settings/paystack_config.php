<?php
/**
 * Paystack Configuration for GiveToGrow
 * Secure payment gateway settings
 */

// Paystack API Keys
define('PAYSTACK_SECRET_KEY', 'sk_test_12af6ed3d10227f1df73ab146a6f12805004ff2f');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_06b09b657a6299074b013e4b1d58e036618933fe');

// Paystack URLs
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INIT_ENDPOINT', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_ENDPOINT', PAYSTACK_API_URL . '/transaction/verify/');

// Application Configuration
define('APP_ENVIRONMENT', 'test'); // Change to 'live' in production
define('APP_BASE_URL', 'http://localhost:8888/GiveToGrow_Web'); // Update with your domain
define('PAYSTACK_CALLBACK_URL', APP_BASE_URL . '/views/paystack_callback.php'); // Callback after payment

/**
 * Initialize a Paystack transaction
 * 
 * @param float $amount Amount in USD (will be converted to pesewas/cents)
 * @param string $email Customer email
 * @param string $reference Optional reference
 * @return array Response with 'status' and 'data' containing authorization_url
 */
function paystack_initialize_transaction($amount, $email, $reference = null) {
    $reference = $reference ?? 'GTG-' . uniqid();
    
    // Convert USD to cents (1 USD = 100 cents)
    // For Ghana (GHS): 1 GHS = 100 pesewas
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
 * Verify a Paystack transaction
 * 
 * @param string $reference Transaction reference
 * @return array Response with transaction details
 */
function paystack_verify_transaction($reference) {
    $response = paystack_api_request('GET', PAYSTACK_VERIFY_ENDPOINT . $reference);
    
    return $response;
}

/**
 * Make a request to Paystack API
 * 
 * @param string $method HTTP method (GET, POST, etc)
 * @param string $url Full API endpoint URL
 * @param array $data Optional data to send
 * @return array API response decoded as array
 */
function paystack_api_request($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Set headers
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
