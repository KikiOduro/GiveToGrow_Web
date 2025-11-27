<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';

// Fetch cart items with full details
$cart_query = "SELECT c.cart_id, c.need_id, c.quantity, 
                      sn.item_name, sn.unit_price, sn.item_category, sn.image_url,
                      s.school_id, s.school_name, s.country
               FROM cart c
               JOIN school_needs sn ON c.need_id = sn.need_id
               JOIN schools s ON sn.school_id = s.school_id
               WHERE c.user_id = ?
               ORDER BY c.added_at DESC";
$cart_items = $db->db_fetch_all($cart_query, [$user_id]);

// Calculate totals
$subtotal = 0;
if ($cart_items) {
    foreach ($cart_items as $item) {
        $subtotal += ($item['unit_price'] * $item['quantity']);
    }
}

$processing_fee = $subtotal > 0 ? 5.00 : 0; // Fixed $5 processing fee
$total = $subtotal + $processing_fee;
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>GiveToGrow - Review Your Donation</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;700;800;900&amp;display=swap" rel="stylesheet"/>
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
        
        // Theme toggle functionality
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     document.documentElement.classList.toggle('dark', theme === 'dark');
        // }
        
        // // Initialize theme on page load
        // initTheme();
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 20px;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#333333] dark:text-neutral-200">
<div class="relative flex min-h-screen w-full flex-col group/design-root">
<div class="layout-container flex h-full grow flex-col">
<div class="flex flex-1 justify-center py-5">
<div class="layout-content-container flex flex-col w-full max-w-[960px] flex-1 px-4 md:px-10">
    
    <!-- Header -->
    <header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-[#e0e0e0] dark:border-neutral-700 px-0 md:px-10 py-4">
        <div class="flex items-center gap-3 text-primary">
            <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
            <h2 class="text-[#333333] dark:text-neutral-100 text-xl font-bold">GiveToGrow</h2>
        </div>
        <a href="schools.php" class="flex cursor-pointer items-center justify-center rounded-lg h-10 bg-neutral-100 dark:bg-neutral-800 text-[#333333] dark:text-neutral-200 gap-2 text-sm font-bold min-w-10 px-2.5 hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
            <span class="material-symbols-outlined text-[#333333] dark:text-neutral-200">arrow_back</span>
        </a>
    </header>
    
    <main class="flex-grow space-y-8 py-8">
        <!-- Page Title -->
        <div class="flex flex-wrap justify-between gap-3 px-0 md:px-4">
            <p class="text-[#333333] dark:text-neutral-100 text-4xl font-black tracking-tighter">Review Your Donation</p>
        </div>
        
        <!-- User Information -->
        <div class="space-y-4">
            <h3 class="text-[#333333] dark:text-neutral-100 text-lg font-bold px-0 md:px-4 pb-2 pt-4">Your Information</h3>
            <div class="px-0 md:px-4">
                <div class="flex flex-col items-stretch justify-start rounded-xl bg-white dark:bg-neutral-800 shadow-[0_2px_8px_rgba(0,0,0,0.05)] p-6">
                    <div class="flex w-full grow flex-col items-stretch justify-center gap-2">
                        <p class="text-[#333333] dark:text-neutral-100 text-lg font-bold"><?php echo $user_name; ?></p>
                        <div class="flex flex-col gap-1 text-sm text-neutral-500 dark:text-neutral-400">
                            <p><?php echo $user_email; ?></p>
                            <p>User ID: GTG-<?php echo str_pad($user_id, 5, '0', STR_PAD_LEFT); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Items -->
        <div class="space-y-4">
            <h3 class="text-[#333333] dark:text-neutral-100 text-lg font-bold px-0 md:px-4 pb-2 pt-4">Items for Donation</h3>
            <div class="flex flex-col gap-4 px-0 md:px-4">
                <?php if (empty($cart_items)): ?>
                    <!-- Empty Cart State -->
                    <div class="flex flex-col items-center justify-center rounded-xl bg-white dark:bg-neutral-800 p-12 shadow-[0_2px_8px_rgba(0,0,0,0.05)] text-center">
                        <span class="material-symbols-outlined text-6xl text-neutral-300 dark:text-neutral-600 mb-4">shopping_cart</span>
                        <p class="text-xl font-bold text-[#333333] dark:text-neutral-100 mb-2">Your cart is empty</p>
                        <p class="text-neutral-500 dark:text-neutral-400 mb-6">Start making a difference by adding items to your cart</p>
                        <a href="schools.php" class="flex cursor-pointer items-center justify-center rounded-lg h-12 px-6 bg-primary text-white text-base font-bold hover:opacity-90 transition-opacity">
                            Browse Schools
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                    <!-- Item Card -->
                    <div class="flex items-center justify-between rounded-xl bg-white dark:bg-neutral-800 p-4 shadow-[0_2px_8px_rgba(0,0,0,0.05)] hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4 flex-grow">
                            <img class="h-16 w-16 rounded-lg object-cover" 
                                 src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['item_name']); ?>"/>
                            <div class="flex flex-col flex-grow">
                                <p class="font-bold text-[#333333] dark:text-neutral-100">
                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                </p>
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    <?php echo htmlspecialchars($item['school_name']); ?>, <?php echo htmlspecialchars($item['country']); ?>
                                </p>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex items-center gap-2">
                                        <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)" 
                                                class="flex h-7 w-7 items-center justify-center rounded-full border border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">remove</span>
                                        </button>
                                        <span class="text-sm font-semibold text-[#333333] dark:text-neutral-100 min-w-8 text-center">
                                            <?php echo $item['quantity']; ?>
                                        </span>
                                        <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)" 
                                                class="flex h-7 w-7 items-center justify-center rounded-full border border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                        </button>
                                    </div>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">×</span>
                                    <span class="text-sm font-semibold text-[#333333] dark:text-neutral-100">
                                        $<?php echo number_format($item['unit_price'], 2); ?>
                                    </span>
                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">=</span>
                                    <span class="text-sm font-bold text-primary">
                                        $<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button onclick="removeFromCart(<?php echo $item['cart_id']; ?>)" 
                                class="flex h-10 w-10 items-center justify-center rounded-full text-neutral-500 dark:text-neutral-400 hover:bg-red-50 dark:hover:bg-red-900/50 hover:text-red-600 dark:hover:text-red-400 transition-colors ml-4">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($cart_items)): ?>
        <!-- Donation Summary -->
        <div class="space-y-4">
            <h3 class="text-[#333333] dark:text-neutral-100 text-lg font-bold px-0 md:px-4 pb-2 pt-4">Donation Summary</h3>
            <div class="px-0 md:px-4">
                <div class="rounded-xl bg-white dark:bg-neutral-800 p-6 shadow-[0_2px_8px_rgba(0,0,0,0.05)] space-y-4">
                    <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-300">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-300">
                        <span>Processing Fee</span>
                        <span>$<?php echo number_format($processing_fee, 2); ?></span>
                    </div>
                    <div class="border-t border-dashed border-[#e0e0e0] dark:border-neutral-700 my-4"></div>
                    <div class="flex justify-between items-center text-xl font-bold text-[#333333] dark:text-neutral-100">
                        <span>Total Donation Amount</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <?php if (!empty($cart_items)): ?>
    <!-- Footer with CTA -->
    <footer class="sticky bottom-0 bg-background-light dark:bg-background-dark py-6 px-0 md:px-4">
        <button onclick="proceedToPayment()" 
                class="w-full flex cursor-pointer items-center justify-center overflow-hidden rounded-xl h-14 bg-primary text-white text-lg font-bold tracking-wide hover:opacity-90 transition-opacity">
            Proceed to Payment
        </button>
    </footer>
    <?php endif; ?>
    
</div>
</div>
</div>
</div>

<script>
// Update item quantity
function updateQuantity(cartId, change) {
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('action', 'update_quantity');
    formData.append('change', change);
    
    fetch('actions/update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated quantities and totals
            location.reload();
        } else {
            alert(data.message || 'Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Remove item from cart
function removeFromCart(cartId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('action', 'remove');
    
    fetch('actions/update_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Proceed to payment
function proceedToPayment() {
    window.location.href = 'checkout.php';
}
</script>

</body>
</html>
