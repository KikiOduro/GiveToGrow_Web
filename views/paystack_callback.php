<?php
/**
 * Paystack Payment Callback Handler
 * This page is called after Paystack payment process
 * User is redirected here by Paystack after payment
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit();
}

// Get reference from URL
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;

if (!$reference) {
    // Payment cancelled or reference missing
    header('Location: cart.php?error=cancelled');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Donor';
error_log("PAYSTACK CALLBACK PAGE");
error_log("Reference from URL: $reference, User: {$_SESSION['user_id']}");
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Processing Payment - GiveToGrow</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#A4B8A4",
                        "background-light": "#f7f7f7",
                        "background-dark": "#1e293b",
                    },
                    fontFamily: {
                        "display": ["Lexend", "sans-serif"]
                    },
                },
            },
        }
        
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     document.documentElement.classList.toggle('dark', theme === 'dark');
        // }
        // initTheme();
    </script>
    <style>
        .spinner {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #A4B8A4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 text-center fade-in">
            <div class="spinner mb-6 mx-auto" id="spinner"></div>
            
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Verifying Payment</h1>
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                Please wait while we verify your payment with Paystack...
            </p>
            
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Payment Reference</div>
                <div class="font-mono text-sm text-gray-800 dark:text-gray-200 break-all">
                    <?php echo htmlspecialchars($reference); ?>
                </div>
            </div>
            
            <div class="hidden" id="errorBox">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <strong class="font-semibold text-red-800 dark:text-red-300">Error:</strong>
                            <span class="text-red-700 dark:text-red-400" id="errorMessage"></span>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Redirecting to cart page...</p>
            </div>
            
            <div class="hidden" id="successBox">
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <strong class="font-semibold text-green-800 dark:text-green-300">Success!</strong>
                    </div>
                    <p class="text-green-700 dark:text-green-400 mt-2">Your donation has been verified. Redirecting...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Verify payment with backend
         */
        async function verifyPayment() {
            const reference = '<?php echo htmlspecialchars($reference); ?>';
            
            try {
                const response = await fetch('../actions/paystack_verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reference: reference
                    })
                });
                
                const data = await response.json();
                console.log('Verification response:', data);
                
                // Hide spinner
                document.getElementById('spinner').style.display = 'none';
                
                if (data.status === 'success' && data.verified) {
                    // Payment verified successfully
                    document.getElementById('successBox').classList.remove('hidden');
                    
                    // Redirect to success page
                    setTimeout(() => {
                        window.location.replace(`donation_success.php?donation_id=${data.donation_id}`);
                    }, 1500);
                    
                } else {
                    // Payment verification failed
                    const errorMsg = data.message || 'Payment verification failed';
                    showError(errorMsg);
                    
                    // Redirect to cart after 5 seconds
                    setTimeout(() => {
                        window.location.href = 'cart.php?error=verification_failed';
                    }, 5000);
                }
                
            } catch (error) {
                console.error('Verification error:', error);
                showError('Connection error. Please try again or contact support.');
                
                // Redirect to cart after 5 seconds
                setTimeout(() => {
                    window.location.href = 'cart.php?error=connection_error';
                }, 5000);
            }
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            document.getElementById('errorBox').classList.remove('hidden');
            document.getElementById('errorMessage').textContent = ' ' + message;
        }
        
        // Start verification when page loads
        window.addEventListener('load', verifyPayment);
    </script>
</body>
</html>
