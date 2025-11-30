<?php
session_start();

// Guest browsing allowed - no login required to view schools
// Check if user is logged in (for personalized features)
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? htmlspecialchars($_SESSION['user_name']) : 'Guest';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';

// Fetch schools from database
require_once __DIR__ . '/../settings/db_class.php';
$db = new db_connection();

// Get filter and sort parameters
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

// Determine sort order based on selection
$order_clause = 'ORDER BY s.created_at DESC'; // Default: Most Recent
switch ($sort_by) {
    case 'recent':
        // Most Recent - based on when school was added
        $order_clause = 'ORDER BY s.created_at DESC';
        break;
    case 'goal_closest':
        // Closest to Goal (highest percentage funded)
        $order_clause = 'ORDER BY (s.amount_raised / NULLIF(s.fundraising_goal, 0)) DESC';
        break;
    case 'goal_highest':
        // Highest Fundraising Goal
        $order_clause = 'ORDER BY s.fundraising_goal DESC';
        break;
    case 'priority':
        // Most Urgent - based on priority of school needs (urgent > high > medium > low)
        $order_clause = 'ORDER BY 
            CASE 
                WHEN MAX(sn.priority) = "urgent" THEN 1
                WHEN MAX(sn.priority) = "high" THEN 2
                WHEN MAX(sn.priority) = "medium" THEN 3
                WHEN MAX(sn.priority) = "low" THEN 4
                ELSE 5
            END ASC, s.created_at DESC';
        break;
    default:
        $order_clause = 'ORDER BY s.created_at DESC';
}

// Build query based on filter
if ($category_filter && $category_filter != 'all') {
    $query = "SELECT DISTINCT s.*, 
              MAX(CASE 
                WHEN sn.priority = 'urgent' THEN 1
                WHEN sn.priority = 'high' THEN 2
                WHEN sn.priority = 'medium' THEN 3
                WHEN sn.priority = 'low' THEN 4
                ELSE 5
              END) as priority_rank
              FROM schools s 
              JOIN school_needs sn ON s.school_id = sn.school_id 
              WHERE s.status = 'active' AND sn.item_category = ? 
              GROUP BY s.school_id
              {$order_clause}";
    $schools = $db->db_fetch_all($query, [$category_filter]);
} else {
    if ($sort_by === 'priority') {
        $query = "SELECT s.*, 
                  MAX(CASE 
                    WHEN sn.priority = 'urgent' THEN 1
                    WHEN sn.priority = 'high' THEN 2
                    WHEN sn.priority = 'medium' THEN 3
                    WHEN sn.priority = 'low' THEN 4
                    ELSE 5
                  END) as priority_rank
                  FROM schools s 
                  LEFT JOIN school_needs sn ON s.school_id = sn.school_id
                  WHERE s.status = 'active'
                  GROUP BY s.school_id
                  {$order_clause}";
    } else {
        $query = "SELECT s.* FROM schools s WHERE s.status = 'active' {$order_clause}";
    }
    $schools = $db->db_fetch_all($query);
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Browse Schools – GiveToGrow</title>
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
                    borderRadius: {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#333333] dark:text-background-light font-display">
<div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<header class="sticky top-0 z-50 flex justify-center border-b border-solid border-black/10 dark:border-white/10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm">
<div class="flex items-center justify-between whitespace-nowrap px-4 sm:px-10 py-3 w-full max-w-6xl">
<div class="flex items-center gap-4 text-[#131514] dark:text-background-light">
    <a href="dashboard.php" class="flex items-center gap-4">
        <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
        <h2 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h2>
    </a>
</div>
<div class="hidden lg:flex flex-1 justify-end gap-8">
    <nav class="flex items-center gap-9">
        <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="dashboard.php">Back Home</a>
        <a class="text-primary text-sm font-bold leading-normal" href="schools.php">Schools</a>
        <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="about.php">About</a>
        <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="dashboard.php#contact">Contact</a>
    </nav>
    <div class="flex gap-2 items-center">
        <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
        <?php if ($is_logged_in): ?>
        <a href="cart.php" class="relative flex items-center justify-center h-10 w-10 rounded-full hover:bg-primary/10 transition-colors">
            <span class="material-symbols-outlined text-[#131514] dark:text-background-light">shopping_cart</span>
            <span id="cart-count" class="absolute -top-1 -right-1 bg-primary text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">0</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ($is_logged_in): ?>
        <span class="text-sm text-[#131514] dark:text-background-light">Welcome, <strong><?php echo $user_name; ?></strong></span>
        <a href="../actions/logout.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] border border-primary/20 hover:bg-primary/10">
            <span class="truncate">Log Out</span>
        </a>
        <?php else: ?>
        <a href="../login/login.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] border border-primary/20 hover:bg-primary/10">
            <span class="truncate">Log In</span>
        </a>
        <a href="../login/register.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
            <span class="truncate">Sign Up</span>
        </a>
        <?php endif; ?>
    </div>
</div>
<button class="lg:hidden text-[#131514] dark:text-background-light">
    <span class="material-symbols-outlined text-3xl">menu</span>
</button>
</div>
</header>

<main class="flex-1">
<!-- Hero Section -->
<section class="flex justify-center py-12 sm:py-16 px-4 bg-primary/10 dark:bg-primary/5">
<div class="w-full max-w-6xl">
    <div class="text-center">
        <h1 class="text-[#131514] dark:text-background-light text-4xl sm:text-5xl font-black leading-tight tracking-[-0.033em]">
            Browse Schools
        </h1>
        <p class="mt-4 text-gray-600 dark:text-gray-300 text-lg font-normal leading-normal max-w-2xl mx-auto">
            Discover schools across Ghana where your contribution can make a real difference. Each project is vetted and transparent.
        </p>
    </div>
    
    <!-- Filter Section -->
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
        <a href="schools.php" class="px-6 py-2 rounded-full <?php echo empty($category_filter) || $category_filter == 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light border border-gray-300 dark:border-gray-600'; ?> font-medium hover:bg-opacity-90">
            All Schools
        </a>
        <a href="schools.php?category=Books" class="px-6 py-2 rounded-full <?php echo $category_filter == 'Books' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light border border-gray-300 dark:border-gray-600'; ?> font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
            Books
        </a>
        <a href="schools.php?category=Desks" class="px-6 py-2 rounded-full <?php echo $category_filter == 'Desks' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light border border-gray-300 dark:border-gray-600'; ?> font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
            Desks
        </a>
        <a href="schools.php?category=Supplies" class="px-6 py-2 rounded-full <?php echo $category_filter == 'Supplies' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light border border-gray-300 dark:border-gray-600'; ?> font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
            Supplies
        </a>
        <a href="schools.php?category=Technology" class="px-6 py-2 rounded-full <?php echo $category_filter == 'Technology' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light border border-gray-300 dark:border-gray-600'; ?> font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
            Technology
        </a>
    </div>
</div>
</section>

<!-- Schools Grid Section -->
<section class="flex justify-center py-10 sm:py-16 px-4">
<div class="w-full max-w-6xl">
    <div class="flex justify-between items-center mb-8">
        <p class="text-[#131514] dark:text-background-light text-lg font-medium">
            Showing <span class="font-bold"><?php echo count($schools); ?> schools</span>
        </p>
        <div class="flex items-center gap-2">
            <label for="sort" class="text-sm text-gray-600 dark:text-gray-400">Sort by:</label>
            <select id="sort" onchange="sortSchools(this.value)" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light px-4 py-2 text-sm">
                <option value="recent" <?php echo $sort_by === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                <option value="priority" <?php echo $sort_by === 'priority' ? 'selected' : ''; ?>>Most Urgent (Priority)</option>
                <option value="goal_highest" <?php echo $sort_by === 'goal_highest' ? 'selected' : ''; ?>>Highest Goal</option>
                <option value="goal_closest" <?php echo $sort_by === 'goal_closest' ? 'selected' : ''; ?>>Closest to Goal</option>
            </select>
        </div>
        <script>
            function sortSchools(sortValue) {
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sortValue);
                window.location.href = url.toString();
            }
        </script>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($schools)): ?>
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No schools found matching your filter.</p>
                <a href="schools.php" class="mt-4 inline-block text-primary hover:underline">View all schools</a>
            </div>
        <?php else: ?>
            <?php foreach ($schools as $school): 
                $progress = ($school['amount_raised'] / $school['fundraising_goal']) * 100;
                $progress = min(100, $progress);
                
                // Get primary category for this school
                $cat_query = "SELECT item_category FROM school_needs WHERE school_id = ? AND status = 'active' LIMIT 1";
                $category_result = $db->db_fetch_one($cat_query, [$school['school_id']]);
                $category = $category_result ? $category_result['item_category'] : 'General';
            ?>
        <!-- School Card -->
        <div class="flex flex-col rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark shadow-sm hover:shadow-md transition-shadow">
            <img class="h-48 w-full object-cover" alt="<?php echo htmlspecialchars($school['school_name']); ?>" src="<?php echo htmlspecialchars($school['image_url']); ?>"/>
            <div class="p-6 flex flex-col flex-1">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg text-[#131514] dark:text-background-light"><?php echo htmlspecialchars($school['school_name']); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($school['country']); ?></p>
                    </div>
                    <span class="text-xs font-bold bg-primary/20 text-primary px-2 py-1 rounded-full"><?php echo htmlspecialchars($category); ?></span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 flex-1"><?php echo htmlspecialchars(substr($school['description'], 0, 100)) . '...'; ?></p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                    <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                </div>
                <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                    <span>₵<?php echo number_format($school['amount_raised'], 0); ?> Raised</span>
                    <span>₵<?php echo number_format($school['fundraising_goal'], 0); ?> Goal</span>
                </div>
                <div class="flex gap-2 mt-auto">
                    <a href="school_detail.php?id=<?php echo $school['school_id']; ?>" class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">View School</a>
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                    <button class="flex-shrink-0 flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
                        <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</section>
</main>

<footer class="bg-gray-100 dark:bg-gray-900">
<div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-2 md:grid-cols-4 gap-8">
    <div class="col-span-2 md:col-span-1">
        <div class="flex items-center gap-2 text-[#131514] dark:text-background-light">
            <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-6 w-auto"/>
            <h2 class="text-lg font-bold">GiveToGrow</h2>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Connecting generosity with classrooms in need.</p>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Quick Links</h3>
        <ul class="mt-4 space-y-4">
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="dashboard.php#how-it-works">How it Works</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="schools.php">Our Schools</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="about.php">About Us</a></li>
        </ul>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Support</h3>
        <ul class="mt-4 space-y-4">
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="dashboard.php#contact">Contact</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#">FAQs</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#">Privacy Policy</a></li>
        </ul>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Connect</h3>
        <ul class="mt-4 space-y-3">
            <li class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span class="material-symbols-outlined text-lg">language</span>
                <a href="https://givetogrow.com" class="hover:text-primary">Givetogrow.com</a>
            </li>
            <li class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span class="material-symbols-outlined text-lg">phone</span>
                <a href="tel:+233557663220" class="hover:text-primary">055 766 3220</a>
            </li>
        </ul>
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Payments are securely processed by Paystack.</p>
    </div>
</div>
<div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8 text-center">
    <p class="text-base text-gray-400">© 2024 GiveToGrow. All rights reserved.</p>
</div>
</div>
</footer>
</div>
</div>

<script>
// Fetch cart count on page load
function updateCartCount() {
    fetch('../actions/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                    cartCount.style.display = data.count > 0 ? 'flex' : 'none';
                }
            }
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

// Update cart count on page load
updateCartCount();
</script>

</body>
</html>
