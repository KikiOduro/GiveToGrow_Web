<?php
/**
 * Checkout Page
 * 
 * The final step before payment - shows order summary and collects
 * payment details. Users can choose between card payment (via Paystack)
 * or mobile money.
 * 
 * If the cart is empty, we redirect them back to the cart page.
 */

session_start();

// Must be logged in to checkout - redirect guests to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Get user info from session
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';

// Fetch cart items with details
$cart_query = "SELECT c.cart_id, c.need_id, c.quantity, 
                      sn.item_name, sn.unit_price, sn.item_category,
                      s.school_id, s.school_name, s.country
               FROM cart c
               JOIN school_needs sn ON c.need_id = sn.need_id
               JOIN schools s ON sn.school_id = s.school_id
               WHERE c.user_id = ?
               ORDER BY c.added_at DESC";
$cart_items = $db->db_fetch_all($cart_query, [$user_id]);

// Check if cart is empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += ($item['unit_price'] * $item['quantity']);
}

$processing_fee = 0; // No processing fee in this design
$total = $subtotal;

// Get primary school name for display (first item's school)
$primary_school = $cart_items[0]['school_name'];
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>GiveToGrow - Complete Your Donation</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
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
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
        
        // // Theme toggle functionality
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     document.documentElement.classList.toggle('dark', theme === 'dark');
        // }
        
        // // Initialize theme on page load
        // initTheme();
    </script>
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#131514] dark:text-neutral-200">
<div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
    
    <!-- Header -->
    <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-neutral-200 dark:border-neutral-800 px-4 sm:px-10 py-3 fixed w-full top-0 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm z-50">
        <div class="flex items-center gap-3">
            <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
            <h1 class="text-[#131514] dark:text-neutral-100 text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h1>
        </div>
        <button class="flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-neutral-200 dark:bg-neutral-800 text-[#131514] dark:text-neutral-200">
            <span class="material-symbols-outlined text-xl">person</span>
        </button>
    </header>
    
    <main class="flex flex-1 justify-center py-5 px-4 sm:px-6 lg:px-8 pt-24">
        <div class="layout-content-container flex flex-col w-full max-w-[560px] flex-1">
            <div class="flex flex-col gap-8">
                
                <!-- Page Title -->
                <div class="flex min-w-72 flex-col gap-2 text-center">
                    <h2 class="text-[#131514] dark:text-neutral-100 text-3xl sm:text-4xl font-black leading-tight tracking-[-0.033em]">
                        Complete Your Donation
                    </h2>
                    <p class="text-[#6d786e] dark:text-neutral-400 text-base font-normal leading-normal">
                        Support <?php echo htmlspecialchars($primary_school); ?>
                        <?php if (count($cart_items) > 1): ?>
                        and <?php echo count($cart_items) - 1; ?> other <?php echo count($cart_items) > 2 ? 'schools' : 'school'; ?>
                        <?php endif; ?>
                    </p>
                </div>
                
                <form id="checkoutForm" class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800 p-6 sm:p-8 space-y-8">
                    
                    <!-- Billing Information -->
                    <section>
                        <h3 class="text-[#131514] dark:text-neutral-100 text-lg font-bold leading-tight tracking-[-0.015em] pb-4">
                            Billing Information
                        </h3>
                        <div class="space-y-4">
                            <label class="flex flex-col w-full">
                                <p class="text-[#131514] dark:text-neutral-200 text-sm font-medium leading-normal pb-2">Full Name</p>
                                <input name="full_name" required
                                       class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#131514] dark:text-neutral-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-neutral-300 dark:border-neutral-700 bg-background-light dark:bg-background-dark focus:border-primary h-12 placeholder:text-[#6d786e] dark:placeholder:text-neutral-500 px-4 text-base font-normal leading-normal" 
                                       placeholder="Enter your full name" 
                                       value="<?php echo $user_name; ?>"/>
                            </label>
                            <label class="flex flex-col w-full">
                                <p class="text-[#131514] dark:text-neutral-200 text-sm font-medium leading-normal pb-2">Email Address</p>
                                <input name="email" type="email" required
                                       class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#131514] dark:text-neutral-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-neutral-300 dark:border-neutral-700 bg-background-light dark:bg-background-dark focus:border-primary h-12 placeholder:text-[#6d786e] dark:placeholder:text-neutral-500 px-4 text-base font-normal leading-normal" 
                                       placeholder="Enter your email address" 
                                       value="<?php echo $user_email; ?>"/>
                            </label>
                        </div>
                    </section>
                    
                    <!-- Payment Method -->
                    <section>
                        <h3 class="text-[#131514] dark:text-neutral-100 text-lg font-bold leading-tight tracking-[-0.015em] pb-4">
                            Payment Method
                        </h3>
                        <div class="grid grid-cols-3 gap-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 p-1">
                            <button type="button" onclick="selectPaymentMethod('mobile_money')" 
                                    class="payment-method-btn flex items-center justify-center gap-2 rounded text-sm font-semibold py-2 px-3 transition-colors bg-white dark:bg-neutral-900 text-primary ring-2 ring-primary" 
                                    data-method="mobile_money">
                                Mobile Money
                            </button>
                            <button type="button" onclick="selectPaymentMethod('card')" 
                                    class="payment-method-btn flex items-center justify-center gap-2 rounded text-sm font-semibold py-2 px-3 transition-colors text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-700/60" 
                                    data-method="card">
                                Card
                            </button>
                            <button type="button" onclick="selectPaymentMethod('paypal')" 
                                    class="payment-method-btn flex items-center justify-center gap-2 rounded text-sm font-semibold py-2 px-3 transition-colors text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200/60 dark:hover:bg-neutral-700/60" 
                                    data-method="paypal">
                                PayPal
                            </button>
                        </div>
                        <input type="hidden" name="payment_method" id="payment_method" value="mobile_money"/>
                        
                        <!-- Mobile Money Fields -->
                        <div id="mobile_money_fields" class="mt-4 space-y-4">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="relative w-full sm:w-2/5">
                                    <select name="mobile_provider" 
                                            class="form-select appearance-none w-full rounded-lg text-[#131514] dark:text-neutral-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-neutral-300 dark:border-neutral-700 bg-background-light dark:bg-background-dark focus:border-primary h-12 px-4 text-base font-normal">
                                        <option value="mtn">MTN MoMo</option>
                                        <option value="vodafone">Vodafone Cash</option>
                                        <option value="airteltigo">AirtelTigo</option>
                                    </select>
                                    <span class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500">expand_more</span>
                                </div>
                                <label class="flex flex-col w-full sm:w-3/5">
                                    <input name="phone_number" type="tel" 
                                           class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#131514] dark:text-neutral-200 focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-neutral-300 dark:border-neutral-700 bg-background-light dark:bg-background-dark focus:border-primary h-12 placeholder:text-[#6d786e] dark:placeholder:text-neutral-500 px-4 text-base font-normal leading-normal" 
                                           placeholder="Phone Number"/>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Card Fields (Hidden by default) -->
                        <div id="card_fields" class="mt-4 space-y-4 hidden">
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Card payment integration coming soon...</p>
                        </div>
                        
                        <!-- PayPal Fields (Hidden by default) -->
                        <div id="paypal_fields" class="mt-4 space-y-4 hidden">
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">PayPal integration coming soon...</p>
                        </div>
                    </section>
                    
                    <!-- Donation Summary -->
                    <section class="space-y-4 rounded-lg bg-background-light dark:bg-background-dark p-4 border border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-[#131514] dark:text-neutral-100 text-lg font-bold leading-tight tracking-[-0.015em]">
                            Donation Summary
                        </h3>
                        <div class="space-y-2 text-sm">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400">
                                <span><?php echo htmlspecialchars($item['item_name']); ?> (×<?php echo $item['quantity']; ?>)</span>
                                <span>₵<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="border-t border-dashed border-neutral-300 dark:border-neutral-700 my-3"></div>
                        <div class="flex justify-between items-center text-base font-bold text-[#131514] dark:text-neutral-100">
                            <span>Total Donation</span>
                            <span>₵<?php echo number_format($total, 2); ?></span>
                        </div>
                    </section>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-14 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-colors">
                        Complete Donation - ₵<?php echo number_format($total, 2); ?>
                    </button>
                    
                    <!-- Trust Badges -->
                    <div class="flex items-center justify-center gap-6 pt-4 text-neutral-500 dark:text-neutral-400">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base">lock</span>
                            <span>Secure Connection</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base">encrypted</span>
                            <span>Encrypted Data</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-base">verified_user</span>
                            <span>Verified Schools</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</div>

<script>
let currentPaymentMethod = 'mobile_money';
const cartTotal = <?php echo $total; ?>;

function selectPaymentMethod(method) {
    currentPaymentMethod = method;
    document.getElementById('payment_method').value = method;
    
    // Update button styles
    document.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'dark:bg-neutral-900', 'text-primary', 'ring-2', 'ring-primary');
        btn.classList.add('text-neutral-600', 'dark:text-neutral-300');
    });
    
    const activeBtn = document.querySelector(`[data-method="${method}"]`);
    activeBtn.classList.remove('text-neutral-600', 'dark:text-neutral-300');
    activeBtn.classList.add('bg-white', 'dark:bg-neutral-900', 'text-primary', 'ring-2', 'ring-primary');
    
    // Show/hide payment fields
    document.getElementById('mobile_money_fields').classList.add('hidden');
    document.getElementById('card_fields').classList.add('hidden');
    document.getElementById('paypal_fields').classList.add('hidden');
    
    if (method === 'mobile_money') {
        document.getElementById('mobile_money_fields').classList.remove('hidden');
    } else if (method === 'card') {
        document.getElementById('card_fields').classList.remove('hidden');
    } else if (method === 'paypal') {
        document.getElementById('paypal_fields').classList.remove('hidden');
    }
}

// Handle form submission - Initialize Paystack Payment
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const email = formData.get('email');
    const fullName = formData.get('full_name');
    
    // Validate email
    if (!email || !email.includes('@')) {
        Swal.fire({
            title: 'Invalid Email',
            text: 'Please enter a valid email address',
            icon: 'warning',
            confirmButtonColor: '#A4B8A4'
        });
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '🔒 Initializing Secure Payment...';
    submitBtn.disabled = true;
    
    // Initialize Paystack transaction
    fetch('../actions/paystack_init_transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount: cartTotal,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Paystack init response:', data);
        
        if (data.status === 'success') {
            // Store reference for verification
            sessionStorage.setItem('paystack_ref', data.reference);
            sessionStorage.setItem('cart_total', cartTotal);
            
            // Show success message
            submitBtn.textContent = '✓ Redirecting to Paystack...';
            
            // Redirect to Paystack payment page after short delay
            setTimeout(() => {
                window.location.href = data.authorization_url;
            }, 1000);
        } else {
            // Show error
            Swal.fire({
                title: 'Payment Error',
                text: data.message || 'Failed to initialize payment. Please try again.',
                icon: 'error',
                confirmButtonColor: '#A4B8A4'
            });
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Connection Error',
            text: 'Please check your internet and try again.',
            icon: 'error',
            confirmButtonColor: '#A4B8A4'
        });
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Show toast notification (utility function)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg text-white font-medium z-50 animate-fade-in ${
        type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>

</body>
</html>
