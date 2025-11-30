<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';

// Fetch featured schools from database
require_once __DIR__ . '/../settings/db_class.php';
$db = new db_connection();

// Fetch impact statistics
$students_query = "SELECT SUM(total_students) as total_students FROM schools WHERE status = 'active'";
$students_result = $db->db_fetch_one($students_query);
$total_students = $students_result['total_students'] ?? 0;

$schools_query_count = "SELECT COUNT(*) as total_schools FROM schools WHERE status = 'active'";
$schools_result = $db->db_fetch_one($schools_query_count);
$total_schools = $schools_result['total_schools'] ?? 0;

$regions_query = "SELECT COUNT(DISTINCT country) as total_regions FROM schools WHERE status = 'active'";
$regions_result = $db->db_fetch_one($regions_query);
$total_regions = $regions_result['total_regions'] ?? 0;

// Fetch a recent donation for the hero section (optional)
$recent_donation_query = "
    SELECT d.donation_id, d.amount, d.created_at, d.is_anonymous,
           u.user_name, s.school_name, sn.item_name
    FROM donations d
    LEFT JOIN users u ON d.user_id = u.user_id
    LEFT JOIN schools s ON d.school_id = s.school_id
    LEFT JOIN school_needs sn ON d.need_id = sn.need_id
    WHERE d.payment_status = 'completed'
    ORDER BY d.created_at DESC
    LIMIT 1
";
$recent_donation = $db->db_fetch_one($recent_donation_query);

// Query to get 3 featured schools with their primary need
$schools_query = "
    SELECT 
        s.school_id,
        s.school_name,
        s.location,
        s.country,
        s.description,
        s.image_url,
        s.fundraising_goal,
        s.amount_raised,
        sn.item_category,
        sn.item_name,
        sn.item_description
    FROM schools s
    LEFT JOIN (
        SELECT school_id, item_category, item_name, item_description,
               ROW_NUMBER() OVER (PARTITION BY school_id ORDER BY 
                   CASE priority 
                       WHEN 'urgent' THEN 1 
                       WHEN 'high' THEN 2 
                       WHEN 'medium' THEN 3 
                       WHEN 'low' THEN 4 
                       ELSE 5 
                   END, created_at DESC
               ) as rn
        FROM school_needs
        WHERE status = 'active'
    ) sn ON s.school_id = sn.school_id AND sn.rn = 1
    WHERE s.status = 'active'
    ORDER BY s.created_at DESC
    LIMIT 3
";

$featured_schools = $db->db_fetch_all($schools_query);
if ($featured_schools === false) {
    $featured_schools = [];
}

// Fetch recent updates for schools the user has donated to (with error handling)
$user_updates = [];
try {
    // Check if school_updates table exists
    $table_check = $db->db_fetch_one("SHOW TABLES LIKE 'school_updates'");
    
    if ($table_check) {
        $user_updates_query = "
            SELECT su.*, s.school_name, s.image_url as school_image,
                   un.is_read
            FROM school_updates su
            JOIN schools s ON su.school_id = s.school_id
            LEFT JOIN update_notifications un ON su.update_id = un.update_id AND un.user_id = ?
            WHERE su.school_id IN (
                SELECT DISTINCT school_id 
                FROM donations 
                WHERE user_id = ? AND payment_status = 'completed'
            )
            AND su.is_published = 1
            ORDER BY su.created_at DESC
            LIMIT 5
        ";
        $user_updates = $db->db_fetch_all($user_updates_query, [$_SESSION['user_id'], $_SESSION['user_id']]);
        if ($user_updates === false) {
            $user_updates = [];
        }
    }
} catch (Exception $e) {
    // Silently fail if impact tracking tables don't exist yet
    $user_updates = [];
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard – GiveToGrow</title>
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
    <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
    <h2 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h2>
</div>
<div class="hidden lg:flex flex-1 justify-end gap-8">
                <nav class="flex items-center gap-9">
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="#how-it-works">How it works</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="schools.php">Schools</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="about.php">About</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="#contact">Contact</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a class="text-primary text-sm font-bold leading-normal border border-primary rounded-full px-4 py-2 hover:bg-primary/10" href="../admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
            </nav>
    <div class="flex gap-2 items-center">
        <span class="text-sm text-[#131514]">Welcome, <strong><?php echo $user_name; ?></strong></span>
        <a href="../actions/logout.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light text-[#131514] text-sm font-bold leading-normal tracking-[0.015em] border border-primary/20 hover:bg-primary/10">
            <span class="truncate">Log Out</span>
        </a>
    </div>
</div>
<button class="lg:hidden text-[#131514] dark:text-background-light">
    <span class="material-symbols-outlined text-3xl">menu</span>
</button>
</div>
</header>
<main class="flex-1">
<!-- Hero Section -->
<section class="flex justify-center py-10 sm:py-20 px-4">
<div class="w-full max-w-6xl">
<div class="relative @container">
<div class="flex min-h-[520px] flex-col gap-6 bg-cover bg-center bg-no-repeat rounded-xl items-start justify-end px-6 pb-10 sm:px-10" data-alt="Happy Ghanaian students smiling in a bright, clean classroom." style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.5) 100%), url("https://i0.wp.com/asaaseradio.com/wp-content/uploads/2021/10/SCHOOLS-UNDER-TREES.jpg?fit=632%2C421&ssl=1");'>
<div class="flex flex-col gap-2 text-left max-w-2xl">
    <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em] @[480px]:text-5xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em]">
        Turn everyday generosity into better classrooms.
    </h1>
    <h2 class="text-white/90 text-sm font-normal leading-normal @[480px]:text-base @[480px]:font-normal @[480px]:leading-normal">
        Connect directly with under-resourced schools in Ghana and see the tangible impact of your contribution. Every gift helps build a brighter future.
    </h2>
</div>
<div class="flex-wrap gap-3 flex">
    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
    <!-- Check if user has donated -->
    <?php
    $has_donated_query = "SELECT COUNT(*) as donation_count FROM donations WHERE user_id = ? AND payment_status = 'completed'";
    $has_donated_result = $db->db_fetch_one($has_donated_query, [$_SESSION['user_id']]);
    $has_donated = $has_donated_result && $has_donated_result['donation_count'] > 0;
    ?>
    
    <?php if ($has_donated): ?>
    <a href= "my_impact.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 gap-2">
        <span class="material-symbols-outlined">insights</span>
        <span class="truncate">View My Impact</span>
    </a>
    <?php else: ?>
    <a href="schools.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Donate Now</span>
    </a>
    <?php endif; ?>
    
    <a href="schools.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Explore Schools</span>
    </a>
    <?php else: ?>
    <a href="schools.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Browse Schools</span>
    </a>
    <?php endif; ?>
</div>
<?php if ($recent_donation): ?>
<div class="absolute bottom-6 right-6 hidden md:block bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 rounded-lg w-64 shadow-lg">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-primary/30 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary text-2xl">volunteer_activism</span>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">A recent donation from</p>
            <p class="font-bold text-sm text-[#131514] dark:text-background-light">
                <?php 
                if ($recent_donation['is_anonymous']) {
                    echo 'Anonymous';
                } else {
                    $name_parts = explode(' ', $recent_donation['user_name']);
                    echo htmlspecialchars($name_parts[0]) . ' ' . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) . '.' : '');
                }
                ?>
            </p>
        </div>
    </div>
    <p class="mt-2 text-sm text-[#131514] dark:text-background-light">
        Funded <span class="font-bold text-primary"><?php echo htmlspecialchars($recent_donation['item_name'] ?? 'a project'); ?></span> for <?php echo htmlspecialchars($recent_donation['school_name']); ?>.
    </p>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="flex justify-center py-10 sm:py-16 px-4">
<div class="w-full max-w-6xl">
<h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] px-4 pb-8 text-center">How GiveToGrow Works</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="flex flex-1 gap-4 rounded-xl border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark p-6 flex-col text-center items-center">
        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary">
            <span class="material-symbols-outlined text-3xl">search</span>
        </div>
        <div class="flex flex-col gap-1">
            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight">1. Explore Projects</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm font-normal leading-normal">Browse vetted schools and specific classroom needs, from textbooks to new desks.</p>
        </div>
    </div>
    <div class="flex flex-1 gap-4 rounded-xl border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark p-6 flex-col text-center items-center">
        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary">
            <span class="material-symbols-outlined text-3xl">credit_card</span>
        </div>
        <div class="flex flex-col gap-1">
            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight">2. Donate Securely</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm font-normal leading-normal">Choose what to fund and make a secure donation with our trusted payment partners.</p>
        </div>
    </div>
    <div class="flex flex-1 gap-4 rounded-xl border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark p-6 flex-col text-center items-center">
        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary">
            <span class="material-symbols-outlined text-3xl">trending_up</span>
        </div>
        <div class="flex flex-col gap-1">
            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight">3. Track Your Impact</h3>
            <p class="text-gray-600 dark:text-gray-300 text-sm font-normal leading-normal">Receive updates and see photos of how your generosity is making a difference.</p>
        </div>
    </div>
</div>
</div>
</section>

<!-- Recent Updates Section (if user has donated) -->
<?php if (!empty($user_updates)): ?>
<section class="flex justify-center py-10 px-4 bg-primary/5 dark:bg-primary/10">
<div class="w-full max-w-6xl">
    <div class="flex justify-between items-center mb-6 px-4">
        <h2 class="text-[#131514] dark:text-background-light text-2xl font-bold leading-tight">Your Impact Updates</h2>
        <a href="my_updates.php" class="text-primary font-medium hover:underline flex items-center gap-1">
            View all
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 px-4">
        <?php foreach ($user_updates as $update): 
            $update_types = [
                'general' => ['icon' => 'campaign', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                'milestone' => ['icon' => 'emoji_events', 'color' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
                'progress' => ['icon' => 'trending_up', 'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                'completion' => ['icon' => 'check_circle', 'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                'thank_you' => ['icon' => 'favorite', 'color' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400']
            ];
            $type_info = $update_types[$update['update_type']] ?? $update_types['general'];
        ?>
        <a href="school_updates.php?school_id=<?php echo $update['school_id']; ?>" 
           class="flex flex-col bg-white dark:bg-neutral-800 rounded-lg overflow-hidden border border-neutral-200 dark:border-neutral-700 hover:shadow-lg transition-shadow">
            <?php if ($update['image_url']): ?>
            <div class="h-40 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($update['image_url']); ?>');"></div>
            <?php endif; ?>
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <span class="<?php echo $type_info['color']; ?> px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 14px;"><?php echo $type_info['icon']; ?></span>
                        <?php echo ucfirst(str_replace('_', ' ', $update['update_type'])); ?>
                    </span>
                    <?php if (!$update['is_read']): ?>
                    <span class="w-2 h-2 bg-primary rounded-full"></span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?php echo htmlspecialchars($update['school_name']); ?></p>
                <h3 class="font-bold text-neutral-800 dark:text-neutral-100 mb-2"><?php echo htmlspecialchars($update['update_title']); ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                    <?php echo htmlspecialchars(substr($update['update_description'], 0, 100)); ?>...
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    <?php 
                    $time_ago = time() - strtotime($update['created_at']);
                    if ($time_ago < 3600) {
                        echo floor($time_ago / 60) . ' minutes ago';
                    } elseif ($time_ago < 86400) {
                        echo floor($time_ago / 3600) . ' hours ago';
                    } else {
                        echo floor($time_ago / 86400) . ' days ago';
                    }
                    ?>
                </p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
</section>
<?php endif; ?>

<!-- Featured Schools Section -->
<section id="schools" class="flex justify-center py-10 sm:py-16 px-4">
<div class="w-full max-w-6xl">
<h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] px-4 pb-8 text-center">Featured Schools</h2>
<?php if (empty($featured_schools)): ?>
    <div class="text-center py-12">
        <p class="text-gray-600 dark:text-gray-300 text-lg">No schools available at the moment. Check back soon!</p>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="../admin/add_school.php" class="inline-block mt-4 px-6 py-2 bg-primary text-white rounded-full font-bold hover:bg-opacity-90">Add Your First School</a>
        <?php endif; ?>
    </div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($featured_schools as $school): 
        // Calculate progress percentage
        $progress = 0;
        if ($school['fundraising_goal'] > 0) {
            $progress = ($school['amount_raised'] / $school['fundraising_goal']) * 100;
            $progress = min($progress, 100); // Cap at 100%
        }
        
        // Format amounts
        $raised = number_format($school['amount_raised'], 0);
        $goal = number_format($school['fundraising_goal'], 0);
        
        // Determine description text
        $description = '';
        if (!empty($school['item_description'])) {
            $description = htmlspecialchars($school['item_description']);
        } elseif (!empty($school['description'])) {
            // Use school description if no need description
            $description = htmlspecialchars(substr($school['description'], 0, 100));
            if (strlen($school['description']) > 100) {
                $description .= '...';
            }
        }
        
        // Default image if none provided
        $image_url = !empty($school['image_url']) ? htmlspecialchars($school['image_url']) : 'https://via.placeholder.com/400x300?text=School+Image';
    ?>
    <div class="flex flex-col rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark shadow-sm">
        <img class="h-48 w-full object-cover" alt="<?php echo htmlspecialchars($school['school_name']); ?>" src="<?php echo $image_url; ?>"/>
        <div class="p-6 flex flex-col flex-1">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-[#131514] dark:text-background-light"><?php echo htmlspecialchars($school['school_name']); ?></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($school['country']); ?></p>
                </div>
                <?php if (!empty($school['item_category'])): ?>
                    <span class="text-xs font-bold bg-primary/20 text-primary px-2 py-1 rounded-full"><?php echo htmlspecialchars($school['item_category']); ?></span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 flex-1"><?php echo $description; ?></p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                <div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                <span>$<?php echo $raised; ?> Raised</span>
                <span>$<?php echo $goal; ?> Goal</span>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="schools.php#school-<?php echo $school['school_id']; ?>" class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">View School</a>
                <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                <button class="flex-shrink-0 flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
                    <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>

<!-- Impact Metrics Section -->
<section id="about" class="flex justify-center py-10 sm:py-20 px-4">
<div class="w-full max-w-6xl">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
    <div>
        <p class="text-5xl font-black text-primary"><?php echo number_format($total_students); ?>+</p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Students Supported</p>
    </div>
    <div>
        <p class="text-5xl font-black text-primary"><?php echo $total_schools; ?></p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Schools Partnered</p>
    </div>
    <div>
        <p class="text-5xl font-black text-primary"><?php echo $total_regions; ?></p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Regions Reached</p>
    </div>
</div>
</div>
</section>

<!-- Final CTA -->
<section id="contact" class="flex justify-center py-10 sm:py-20 px-4">
<div class="w-full max-w-4xl bg-primary/20 dark:bg-primary/30 text-center p-10 sm:p-16 rounded-lg sm:rounded-xl">
<h2 class="text-3xl sm:text-4xl font-bold text-[#131514] dark:text-background-light">Ready to help a classroom grow?</h2>
<p class="mt-4 max-w-2xl mx-auto text-gray-700 dark:text-gray-200">Your contribution, big or small, creates a world of opportunity. Join us in transforming education for children who need it most.</p>
<div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
    <a href="schools.php" class="flex min-w-[84px] max-w-sm mx-auto sm:mx-0 cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Donate Now</span>
    </a>
    <?php endif; ?>
    <a href="schools.php" class="flex min-w-[84px] max-w-sm mx-auto sm:mx-0 cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Browse Schools</span>
    </a>
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
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#how-it-works">How it Works</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#schools">Our Schools</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="about.php">About Us</a></li>
        </ul>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Support</h3>
        <ul class="mt-4 space-y-4">
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#contact">Contact</a></li>
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
</body>
</html>
