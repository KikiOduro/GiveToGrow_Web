<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get school ID from URL
$school_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($school_id <= 0) {
    header("Location: schools.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Fetch school details
$school_query = "SELECT * FROM schools WHERE school_id = ? AND status = 'active'";
$school = $db->db_fetch_one($school_query, [$school_id]);

if (!$school) {
    header("Location: schools.php");
    exit();
}

// Calculate progress percentage
$progress = ($school['amount_raised'] / $school['fundraising_goal']) * 100;
$progress = min(100, $progress); // Cap at 100%

// Fetch school needs
$needs_query = "SELECT * FROM school_needs WHERE school_id = ? AND status = 'active' ORDER BY priority DESC, created_at ASC";
$needs = $db->db_fetch_all($needs_query, [$school_id]);

// Get user information
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';

// Get cart count for this user
$cart_count = 0;
$cart_total = 0;

// Try to get cart data, handle gracefully if table doesn't exist or is empty
try {
    $cart_query = "SELECT COUNT(*) as total_items, 
                          COALESCE(SUM(cart.quantity * school_needs.unit_price), 0) as total_amount 
                   FROM cart 
                   LEFT JOIN school_needs ON cart.need_id = school_needs.need_id 
                   WHERE cart.user_id = ?";
    $cart_data = $db->db_fetch_one($cart_query, [$_SESSION['user_id']]);
    if ($cart_data) {
        $cart_count = $cart_data['total_items'] ?? 0;
        $cart_total = $cart_data['total_amount'] ?? 0;
    }
} catch (Exception $e) {
    // Cart table doesn't exist yet or query failed, use defaults
    error_log("Cart query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>GiveToGrow - <?php echo htmlspecialchars($school['school_name']); ?></title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px"},
                },
            },
        }
    </script>
    <script>
        // Theme toggle functionality
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        }
        
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        }
        
        // Initialize theme on page load
        initTheme();

        // Add to cart function
        function addToCart(needId) {
            fetch('../actions/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'need_id=' + needId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal with options
                    showAddToCartModal();
                } else {
                    alert(data.message || 'Failed to add item to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Show modal after adding to cart
        function showAddToCartModal() {
            const modal = document.getElementById('addToCartModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        // Close modal
        function closeModal() {
            const modal = document.getElementById('addToCartModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Quick donate function
        function quickDonate(needId) {
            addToCart(needId);
            setTimeout(() => {
                window.location.href = 'cart.php';
            }, 500);
        }
    </script>
    <style>
        body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<main class="flex-1 pb-32">
<div class="flex flex-1 justify-center">
<div class="layout-content-container flex flex-col w-full max-w-2xl">
    <!-- ToolBar -->
    <div class="flex justify-between items-center px-4 py-3 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm sticky top-0 z-10">
        <a href="schools.php" class="p-2 text-neutral-800 dark:text-neutral-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div class="flex items-center gap-2">
            <?php if ($school['is_verified']): ?>
            <span class="material-symbols-outlined text-green-700 dark:text-green-500 text-base">verified</span>
            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Verified School</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- HeaderImage -->
    <div class="@container">
        <div class="px-4 py-3">
            <div class="bg-cover bg-center flex flex-col justify-end overflow-hidden rounded-xl min-h-[320px]" 
                 style='background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0) 40%), url("<?php echo htmlspecialchars($school['image_url']); ?>");'>
                <div class="flex p-6">
                    <p class="text-white tracking-tight text-4xl font-bold leading-tight">
                        <?php echo htmlspecialchars($school['school_name']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MetaText -->
    <p class="text-neutral-500 dark:text-neutral-400 text-base font-normal leading-normal pb-2 pt-1 px-8">
        <?php echo htmlspecialchars($school['location'] . ', ' . $school['country']); ?>
    </p>
    
    <!-- BodyText -->
    <p class="text-neutral-800 dark:text-neutral-200 text-base font-normal leading-relaxed pb-3 px-8">
        <?php echo nl2br(htmlspecialchars($school['description'])); ?>
    </p>
    
    <!-- ProgressBar -->
    <div class="flex flex-col gap-3 p-4 mx-4 my-4 bg-white dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="flex gap-6 justify-between items-center">
            <p class="text-neutral-800 dark:text-neutral-200 text-base font-medium leading-normal">Fundraising Goal</p>
            <p class="text-primary dark:text-primary font-bold text-lg leading-normal"><?php echo round($progress); ?>%</p>
        </div>
        <div class="rounded-full bg-neutral-200 dark:bg-neutral-700 h-2.5">
            <div class="h-2.5 rounded-full bg-primary" style="width: <?php echo $progress; ?>%;"></div>
        </div>
        <p class="text-neutral-500 dark:text-neutral-400 text-sm font-normal leading-normal">
            <span class="font-bold text-neutral-800 dark:text-neutral-200">
                $<?php echo number_format($school['amount_raised'], 2); ?> raised
            </span> 
            of $<?php echo number_format($school['fundraising_goal'], 2); ?>
        </p>
    </div>
    
    <!-- View Updates Button -->
    <div class="px-8 py-3">
        <a href="school_updates.php?id=<?php echo $school_id; ?>" 
           class="flex items-center justify-center gap-2 bg-primary/10 dark:bg-primary/20 text-primary dark:text-primary border-2 border-primary/30 rounded-lg px-6 py-3 font-bold hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
            <span class="material-symbols-outlined">insights</span>
            View Updates & Impact
        </a>
    </div>
    
    <!-- Verified Needs Section -->
    <div class="px-4 py-3">
        <h2 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 px-4 pb-4 pt-2">Help Fulfill Their Needs</h2>
        
        <?php if (empty($needs)): ?>
            <div class="text-center py-8">
                <p class="text-neutral-500 dark:text-neutral-400">No active needs at the moment. Check back soon!</p>
            </div>
        <?php else: ?>
            <?php foreach ($needs as $need): ?>
            <!-- Item Card -->
            <div class="flex items-center gap-4 bg-white dark:bg-neutral-800/50 p-4 rounded-lg mb-4 border border-neutral-200 dark:border-neutral-700">
                <img class="h-24 w-24 rounded-md object-cover flex-shrink-0" 
                     alt="<?php echo htmlspecialchars($need['item_name']); ?>" 
                     src="<?php echo htmlspecialchars($need['image_url']); ?>"/>
                <div class="flex-grow">
                    <p class="font-bold text-neutral-800 dark:text-neutral-100">
                        <?php echo htmlspecialchars($need['item_name']); ?>
                    </p>
                    <p class="text-neutral-500 dark:text-neutral-400 text-sm">
                        $<?php echo number_format($need['unit_price'], 2); ?> 
                        <?php if ($need['quantity_needed'] > 1): ?>
                            per item (<?php echo $need['quantity_needed']; ?> needed)
                        <?php else: ?>
                            total
                        <?php endif; ?>
                    </p>
                    <?php if ($need['item_description']): ?>
                    <p class="text-neutral-600 dark:text-neutral-400 text-xs mt-1">
                        <?php echo htmlspecialchars($need['item_description']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                    <div class="flex items-center gap-2 mt-3">
                        <a href="donate_item.php?id=<?php echo $need['need_id']; ?>" 
                           class="flex-grow px-4 py-2 text-sm font-bold text-white bg-primary rounded-full hover:bg-primary/90 transition-colors text-center">
                            Donate Now
                        </a>
                        <button onclick="addToCart(<?php echo $need['need_id']; ?>)" 
                                class="p-2 text-primary dark:text-primary bg-primary/10 dark:bg-primary/20 rounded-full hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="mt-3 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">Admin view - Donation disabled</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>
</main>
</div>

<!-- Persistent Footer -->
<?php if ($cart_count > 0 && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')): ?>
<footer class="fixed bottom-0 left-0 right-0 z-20 bg-background-light dark:bg-neutral-900 border-t border-neutral-200 dark:border-neutral-700/50 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] dark:shadow-[0_-4px_12px_rgba(0,0,0,0.2)]">
    <div class="max-w-2xl mx-auto px-6 py-4 flex justify-between items-center">
        <div>
            <p class="font-bold text-lg text-neutral-800 dark:text-neutral-100">
                $<?php echo number_format($cart_total, 2); ?>
            </p>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                <?php echo $cart_count; ?> <?php echo $cart_count == 1 ? 'item' : 'items'; ?> in cart
            </p>
        </div>
        <a href="cart.php" class="bg-primary text-white font-bold py-3 px-8 rounded-full text-base hover:bg-primary/90 transition-transform active:scale-95">
            Proceed to Cart
        </a>
    </div>
</footer>
<?php endif; ?>

<!-- Add to Cart Success Modal -->
<div id="addToCartModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 transform transition-all">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-3xl">check_circle</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Added to Cart!</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Item has been added to your cart successfully.</p>
            <div class="flex flex-col gap-3">
                <a href="cart.php" class="w-full bg-primary text-white font-bold py-3 px-6 rounded-full hover:bg-primary/90 transition-colors">
                    View Cart
                </a>
                <a href="schools.php" class="w-full bg-gray-200 dark:bg-neutral-700 text-gray-800 dark:text-white font-semibold py-3 px-6 rounded-full hover:bg-gray-300 dark:hover:bg-neutral-600 transition-colors">
                    Continue Browsing
                </a>
            </div>
        </div>
    </div>
</div>

</div>
</body>
</html>
