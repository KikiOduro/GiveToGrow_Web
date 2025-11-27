<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get need_id from URL
$need_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($need_id <= 0) {
    header("Location: schools.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Fetch the specific need item with school information
$need_query = "SELECT sn.*, s.school_id, s.school_name, s.country, s.location 
               FROM school_needs sn 
               JOIN schools s ON sn.school_id = s.school_id 
               WHERE sn.need_id = ? AND sn.status = 'active' AND s.status = 'active'";
$need = $db->db_fetch_one($need_query, [$need_id]);

if (!$need) {
    header("Location: schools.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';

// Calculate progress
$quantity_fulfilled = $need['quantity_fulfilled'] ?? 0;
$progress = ($quantity_fulfilled / $need['quantity_needed']) * 100;
$progress = min(100, $progress);
$remaining = $need['quantity_needed'] - $quantity_fulfilled;
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>GiveToGrow - <?php echo htmlspecialchars($need['item_name']); ?> Donation</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
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
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px"},
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 20px;
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#333333] dark:text-gray-200">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
    
<!-- TopNavBar -->
<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-gray-200 dark:border-gray-700 bg-white dark:bg-background-dark px-4 sm:px-10 py-3 fixed top-0 left-0 right-0 z-10">
    <div class="flex items-center gap-3 text-primary">
        <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
        <h2 class="text-[#333333] dark:text-gray-200 text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h2>
    </div>
    <a href="cart.php" class="flex max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 gap-2 text-sm font-bold leading-normal tracking-[0.015em] min-w-10 px-2.5">
        <span class="material-symbols-outlined">shopping_cart</span>
    </a>
</header>

<main class="flex flex-1 justify-center py-5 pt-24 px-4 sm:px-6 lg:px-8">
    <div class="layout-content-container flex flex-col w-full max-w-2xl flex-1">
        
        <!-- Breadcrumbs / Back Link -->
        <div class="flex items-center gap-2 p-4">
            <span class="material-symbols-outlined text-sm text-[#6d786e] dark:text-gray-400">arrow_back_ios</span>
            <a class="text-[#6d786e] dark:text-gray-400 text-base font-medium leading-normal hover:text-primary transition-colors" 
               href="school_detail.php?id=<?php echo $need['school_id']; ?>">
                Back to <?php echo htmlspecialchars($need['school_name']); ?>
            </a>
        </div>
        
        <div class="bg-white dark:bg-gray-800/50 shadow-sm rounded-xl overflow-hidden">
            
            <!-- Header Image -->
            <div class="@container">
                <div class="@[480px]:p-4">
                    <div class="bg-cover bg-center flex flex-col justify-end overflow-hidden @[480px]:rounded-lg min-h-64 sm:min-h-80" 
                         style='background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0) 35%), url("<?php echo htmlspecialchars($need['image_url']); ?>");'>
                        <div class="flex justify-center gap-2 p-5">
                            <div class="size-2 rounded-full bg-white"></div>
                            <div class="size-2 rounded-full bg-white opacity-50"></div>
                            <div class="size-2 rounded-full bg-white opacity-50"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- Page Heading and Price -->
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div>
                        <h1 class="text-[#131514] dark:text-white text-3xl sm:text-4xl font-black leading-tight tracking-[-0.033em]">
                            <?php echo htmlspecialchars($need['item_name']); ?>
                        </h1>
                        <p class="text-[#6d786e] dark:text-gray-400 text-sm mt-1">
                            For <?php echo htmlspecialchars($need['school_name']); ?>, <?php echo htmlspecialchars($need['country']); ?>
                        </p>
                    </div>
                    <p class="text-primary text-2xl font-bold leading-tight pt-1">
                        $<?php echo number_format($need['unit_price'], 2); ?>
                    </p>
                </div>
                
                <!-- Progress Bar -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-[#6d786e] dark:text-gray-400">
                        <span><?php echo $quantity_fulfilled; ?> of <?php echo $need['quantity_needed']; ?> funded</span>
                        <span><?php echo round($progress); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <?php if ($remaining > 0): ?>
                    <p class="text-sm text-[#6d786e] dark:text-gray-400">
                        <?php echo $remaining; ?> more needed
                    </p>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <p class="text-[#6d786e] dark:text-gray-300 text-base font-normal leading-relaxed">
                    <?php echo htmlspecialchars($need['item_description']); ?>
                </p>
                
                <!-- Form Section -->
                <form id="donationForm" class="space-y-6 pt-4">
                    <input type="hidden" name="need_id" value="<?php echo $need_id; ?>">
                    
                    <!-- Quantity Selector -->
                    <div>
                        <label class="block text-sm font-medium text-[#333333] dark:text-gray-200 mb-2">Quantity</label>
                        <div class="flex items-center gap-4">
                            <button type="button" id="decreaseBtn" aria-label="Decrease quantity" 
                                    class="flex items-center justify-center size-10 rounded-full border border-gray-200 dark:border-gray-600 text-[#6d786e] dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-xl">remove</span>
                            </button>
                            <input id="quantityInput" name="quantity" 
                                   class="w-16 h-10 text-center font-bold text-lg bg-transparent border-none p-0 focus:ring-0 text-[#131514] dark:text-white" 
                                   readonly type="text" value="1"/>
                            <button type="button" id="increaseBtn" aria-label="Increase quantity" 
                                    class="flex items-center justify-center size-10 rounded-full border border-gray-200 dark:border-gray-600 text-[#6d786e] dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <span class="material-symbols-outlined text-xl">add</span>
                            </button>
                        </div>
                        <p class="text-xs text-[#6d786e] dark:text-gray-400 mt-2">
                            Max available: <?php echo $remaining; ?>
                        </p>
                    </div>
                    
                    <!-- Personal Message -->
                    <div>
                        <label class="block text-sm font-medium text-[#333333] dark:text-gray-200 mb-2" for="personal-message">
                            Personal Message (Optional)
                        </label>
                        <textarea name="message" 
                                  class="w-full rounded-lg border-gray-200 dark:border-gray-600 bg-background-light dark:bg-gray-700 focus:border-primary focus:ring-primary focus:ring-opacity-50 transition" 
                                  id="personal-message" 
                                  placeholder="Add an optional message for the school..." 
                                  rows="4"></textarea>
                    </div>
                    
                    <!-- User Information -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1" for="user-name">Name</label>
                            <input class="w-full rounded-lg border-0 bg-gray-100 dark:bg-gray-700/50 p-3 text-gray-700 dark:text-gray-300" 
                                   id="user-name" readonly type="text" value="<?php echo $user_name; ?>"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1" for="user-email">Email</label>
                            <input class="w-full rounded-lg border-0 bg-gray-100 dark:bg-gray-700/50 p-3 text-gray-700 dark:text-gray-300" 
                                   id="user-email" readonly type="email" value="<?php echo $user_email; ?>"/>
                        </div>
                    </div>
                    
                    <!-- CTA Button -->
                    <button type="submit" 
                            class="w-full flex items-center justify-center overflow-hidden rounded-lg h-14 bg-primary text-white gap-2 text-lg font-bold leading-normal tracking-[0.015em] hover:opacity-90 transition-opacity">
                        Add to Cart
                    </button>
                    
                    <!-- Social Proof/Trust Signal -->
                    <div class="flex items-center justify-center gap-2 pt-2">
                        <span class="material-symbols-outlined text-primary text-base">verified</span>
                        <p class="text-[#6d786e] dark:text-gray-400 text-sm">100% of your donation funds this item.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

</div>
</div>

<script>
// Quantity controls
const quantityInput = document.getElementById('quantityInput');
const decreaseBtn = document.getElementById('decreaseBtn');
const increaseBtn = document.getElementById('increaseBtn');
const maxQuantity = <?php echo $remaining; ?>;

decreaseBtn.addEventListener('click', function() {
    let current = parseInt(quantityInput.value);
    if (current > 1) {
        quantityInput.value = current - 1;
    }
});

increaseBtn.addEventListener('click', function() {
    let current = parseInt(quantityInput.value);
    if (current < maxQuantity) {
        quantityInput.value = current + 1;
    }
});

// Form submission
document.getElementById('donationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('../actions/add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Log the raw response for debugging
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                throw new Error('Server returned invalid JSON. Check browser console for details.');
            }
        });
    })
    .then(data => {
        console.log('Parsed data:', data);
        if (data.success) {
            // Show success modal
            showAddToCartModal();
        } else {
            alert(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error.message + '\n\nPlease check the browser console (F12) for details.');
    });
});

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
</script>

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

</body>
</html>
