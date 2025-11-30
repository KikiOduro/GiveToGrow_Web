<?php
// Fetch data from database for landing page
require_once '../settings/db_class.php';
$db = new db_connection();

// Fetch impact statistics
$students_query = "SELECT SUM(total_students) as total_students FROM schools WHERE status = 'active'";
$students_result = $db->db_fetch_one($students_query);
$total_students = $students_result['total_students'] ?? 0;

$schools_query = "SELECT COUNT(*) as total_schools FROM schools WHERE status = 'active'";
$schools_result = $db->db_fetch_one($schools_query);
$total_schools = $schools_result['total_schools'] ?? 0;

$regions_query = "SELECT COUNT(DISTINCT country) as total_regions FROM schools WHERE status = 'active'";
$regions_result = $db->db_fetch_one($regions_query);
$total_regions = $regions_result['total_regions'] ?? 0;

// Fetch featured schools (3 most recent active schools)
$featured_query = "SELECT s.*, 
    (SELECT COUNT(*) FROM school_needs WHERE school_id = s.school_id AND status = 'active') as needs_count
    FROM schools s 
    WHERE s.status = 'active' 
    ORDER BY s.created_at DESC 
    LIMIT 3";
$featured_schools = $db->db_fetch_all($featured_query);

// Fetch hero school (first active school for hero section overlay)
$hero_query = "SELECT school_name, fundraising_goal, amount_raised, image_url FROM schools WHERE status = 'active' ORDER BY created_at DESC LIMIT 1";
$hero_school = $db->db_fetch_one($hero_query);
$hero_percentage = $hero_school ? getFundingPercentage($hero_school['amount_raised'], $hero_school['fundraising_goal']) : 75;

// Helper function to calculate funding percentage
function getFundingPercentage($raised, $goal) {
    if ($goal <= 0) return 0;
    $percentage = ($raised / $goal) * 100;
    // If there's any funding at all, show at least 1% for visual feedback
    if ($percentage > 0 && $percentage < 1) {
        return 1;
    }
    return min(100, round($percentage));
}

// Helper function to get priority badge
function getPriorityBadge($school_id, $db) {
    $priority_query = "SELECT priority FROM school_needs WHERE school_id = ? AND status = 'active' ORDER BY 
        CASE priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
        END 
        LIMIT 1";
    $priority_result = $db->db_fetch_one($priority_query, [$school_id]);
    return $priority_result['priority'] ?? null;
}
?>
<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>GiveToGrow – Education Impact Donation Platform</title>
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
                        "primary-hover": "#8FA68F",
                        "secondary": "#F57C00",
                        "accent": "#7CB342",
                        "background-light": "#FAFAFA",
                        "background-dark": "#1A1A1A",
                        "text-light": "#2D2D2D",
                        "text-dark": "#F5F5F5",
                        "card-light": "#FFFFFF",
                        "card-dark": "#262626",
                        "border-light": "#E5E5E5",
                        "border-dark": "#404040"
                    },
                    fontFamily: {
                        "display": ["Lexend", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.75rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
          font-variation-settings:
          'FILL' 0,
          'wght' 400,
          'GRAD' 0,
          'opsz' 24
        }
    </style>
<script>
        // Theme toggle functionality
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     if (theme === 'dark') {
        //         document.documentElement.classList.add('dark');
        //     }
        // }
        // function toggleTheme() {
        //     const html = document.documentElement;
        //     if (html.classList.contains('dark')) {
        //         html.classList.remove('dark');
        //         localStorage.setItem('theme', 'light');
        //     } else {
        //         html.classList.add('dark');
        //         localStorage.setItem('theme', 'dark');
        //     }
        // }
        // // Initialize theme on page load
        // initTheme();
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-light dark:text-text-dark">
<div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- TopNavBar -->
<header class="sticky top-0 z-50 w-full bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm border-b border-border-light dark:border-border-dark">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex items-center justify-between h-16">
<div class="flex items-center gap-4">
<div class="h-10 w-10">
<img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
</div>
<h2 class="text-xl font-bold">GiveToGrow</h2>
</div>
<div class="hidden md:flex items-center gap-8">
<a class="text-sm font-medium hover:text-primary" href="#">How it works</a>
<a class="text-sm font-medium hover:text-primary" href="#">Schools</a>
<a class="text-sm font-medium hover:text-primary" href="#">About</a>
<a class="text-sm font-medium hover:text-primary" href="#">Contact</a>
</div>
<div class="hidden md:flex items-center gap-2">
<a href="../login/login.php" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light dark:bg-card-dark text-sm font-bold border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-gray-700">
<span class="truncate">Log In</span>
</a>
<a href="../login/register.php" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
<span class="truncate">Create Account</span>
</a>
</div>
</div>
</div>
</header>
<main class="flex-1">
<!-- HeroSection -->
<section class="py-16 sm:py-24">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
<div class="flex flex-col gap-8 text-center lg:text-left">
<div class="flex flex-col gap-4">
<h1 class="text-4xl lg:text-5xl font-black tracking-tighter">Turn everyday generosity into better classrooms.</h1>
<p class="text-base lg:text-lg text-gray-600 dark:text-gray-400">Connect with under-resourced schools in Ghana and provide the tools they need to succeed. Your contribution makes a direct impact.</p>
</div>
<div class="flex flex-wrap gap-4 justify-center lg:justify-start">
<a href="../login/login.php" class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-primary text-white text-base font-bold hover:opacity-90">
<span class="truncate">Donate Now</span>
</a>
<a href="../login/login.php" class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-transparent text-sm font-bold border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-gray-800">
<span class="truncate">See how it works</span>
</a>
</div>
</div>
<div class="relative w-full aspect-[4/3] bg-center bg-no-repeat bg-cover rounded-xl shadow-lg" data-alt="<?php echo $hero_school ? htmlspecialchars($hero_school['school_name']) : 'Smiling Ghanaian students in a classroom'; ?>" style='background-image: url("<?php echo $hero_school && !empty($hero_school['image_url']) ? htmlspecialchars($hero_school['image_url']) : 'https://lh3.googleusercontent.com/aida-public/AB6AXuCi5OCWzAydhaRtSWfJ_6_A42t3KvWrWj6xNp0GhAZB3YQbeBIPHSKTpx2idI-XoigskdqDRhAJDZT0FqcRrErM7ILRESxdn6BP3nP2Lxa8SQqqxqHIKP8e9NeWvghZhDLtzMHGRrKcYSpnqs5z6v597NYcoBrzv3EdDVhD1xY5KeF_mEx_b4Prmm1U6_HWD3aGmZjN-tfdPeqyUQR0XJzfQWyqFXPErEEv8vniM0hbeOOZmSQDZfdxRdU4EeOg4XaqSih1efcw706C'; ?>");'>
<?php if ($hero_school): ?>
<div class="absolute -bottom-6 -right-6 bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-xl w-64 border border-border-light dark:border-border-dark">
<p class="text-sm font-bold mb-1"><?php echo htmlspecialchars($hero_school['school_name']); ?></p>
<p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Raised: ₵<?php echo number_format($hero_school['amount_raised'], 0); ?> / ₵<?php echo number_format($hero_school['fundraising_goal'], 0); ?></p>
<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
<div class="bg-primary h-2.5 rounded-full" style="width: <?php echo $hero_percentage; ?>%"></div>
</div>
<p class="text-right text-xs mt-1 font-medium"><?php echo $hero_percentage; ?>% Funded</p>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>
<!-- How It Works Section -->
<section class="py-16 sm:py-24 bg-card-light dark:bg-card-dark">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-12">
<h2 class="text-3xl font-bold tracking-tight">How GiveToGrow Works</h2>
<p class="mt-2 text-lg text-gray-600 dark:text-gray-400">A simple, transparent process to make a difference.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
<div class="flex flex-col items-center text-center p-6">
<div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/20 text-primary mb-4">
<span class="material-symbols-outlined !text-4xl">search</span>
</div>
<h3 class="text-lg font-bold">Explore Schools</h3>
<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Discover vetted schools and projects that need your help.</p>
</div>
<div class="flex flex-col items-center text-center p-6">
<div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/20 text-primary mb-4">
<span class="material-symbols-outlined !text-4xl">add_shopping_cart</span>
</div>
<h3 class="text-lg font-bold">Add to Cart</h3>
<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose specific items or contribute to a general fund.</p>
</div>
<div class="flex flex-col items-center text-center p-6">
<div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/20 text-primary mb-4">
<span class="material-symbols-outlined !text-4xl">lock</span>
</div>
<h3 class="text-lg font-bold">Donate Securely</h3>
<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Complete your donation with our secure payment system.</p>
</div>
<div class="flex flex-col items-center text-center p-6">
<div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary/20 text-primary mb-4">
<span class="material-symbols-outlined !text-4xl">trending_up</span>
</div>
<h3 class="text-lg font-bold">Track Impact</h3>
<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Receive updates and see the difference you've made.</p>
</div>
</div>
</div>
</section>
<!-- Featured Schools Section -->
<section class="py-16 sm:py-24">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-12">
<h2 class="text-3xl font-bold tracking-tight">Featured Schools</h2>
<p class="mt-2 text-lg text-gray-600 dark:text-gray-400">Your donation can directly support these classrooms.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<?php 
if (!empty($featured_schools)) {
    foreach ($featured_schools as $school) {
        $percentage = getFundingPercentage($school['amount_raised'], $school['fundraising_goal']);
        $priority = getPriorityBadge($school['school_id'], $db);
        
        // Priority badge configuration
        $priority_badges = [
            'urgent' => ['color' => 'bg-red-500', 'text' => 'Urgent Priority'],
            'high' => ['color' => 'bg-orange-500', 'text' => 'High Priority'],
            'medium' => ['color' => 'bg-yellow-500', 'text' => 'Medium Priority'],
            'low' => ['color' => 'bg-blue-500', 'text' => 'Low Priority']
        ];
        
        $badge = $priority && isset($priority_badges[$priority]) ? $priority_badges[$priority] : null;
?>
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-md overflow-hidden flex flex-col border border-border-light dark:border-border-dark">
    <div class="relative">
        <img class="h-48 w-full object-cover" alt="<?php echo htmlspecialchars($school['school_name']); ?>" src="<?php echo htmlspecialchars($school['image_url']); ?>"/>
        <?php if ($badge): ?>
        <div class="absolute top-2 left-2 <?php echo $badge['color']; ?> text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo $badge['text']; ?></div>
        <?php endif; ?>
    </div>
    <div class="p-6 flex-grow flex flex-col">
        <h3 class="text-lg font-bold"><?php echo htmlspecialchars($school['school_name']); ?></h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2"><?php echo htmlspecialchars($school['location']); ?>, <?php echo htmlspecialchars($school['country']); ?></p>
        <p class="text-sm text-gray-600 dark:text-gray-400 flex-grow mb-4"><?php echo htmlspecialchars(substr($school['description'], 0, 100)) . (strlen($school['description']) > 100 ? '...' : ''); ?></p>
        <div class="mb-4">
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                <span>₵<?php echo number_format($school['amount_raised'], 0); ?> raised</span>
                <span>₵<?php echo number_format($school['fundraising_goal'], 0); ?> goal</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full" style="width: <?php echo $percentage; ?>%;"></div>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-auto">
            <a href="../login/login.php" class="w-full flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
                <span>View School</span>
            </a>
            <a href="../login/login.php" class="flex-shrink-0 flex cursor-pointer items-center justify-center rounded-full h-10 w-10 bg-primary/20 text-primary hover:bg-primary/30">
                <span class="material-symbols-outlined">add_shopping_cart</span>
            </a>
        </div>
    </div>
</div>
<?php 
    }
} else {
    echo '<p class="col-span-3 text-center text-gray-500">No schools available at the moment. Check back soon!</p>';
}
?>
</div>
</div>
</section>
<!-- Impact Metrics Section -->
<section class="bg-primary/90 text-white py-12 sm:py-16">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
<div>
<p class="text-4xl lg:text-5xl font-black"><?php echo number_format($total_students); ?>+</p>
<p class="text-lg font-medium mt-2">Students Supported</p>
</div>
<div>
<p class="text-4xl lg:text-5xl font-black"><?php echo $total_schools; ?></p>
<p class="text-lg font-medium mt-2">Schools Partnered</p>
</div>
<div>
<p class="text-4xl lg:text-5xl font-black"><?php echo $total_regions; ?></p>
<p class="text-lg font-medium mt-2">Regions</p>
</div>
</div>
</div>
</section>
<!-- Final CTA Banner -->
<section class="py-16 sm:py-24">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
<h2 class="text-3xl lg:text-4xl font-bold tracking-tight">Ready to help a classroom grow?</h2>
<p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Your contribution, big or small, creates a world of opportunity. Join us in empowering the next generation of leaders.</p>
<div class="mt-8 flex justify-center gap-4">
<a href="../login/login.php" class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-primary text-white text-base font-bold hover:opacity-90">
<span class="truncate">Donate Now</span>
</a>
<a href="../login/login.php" class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-transparent border border-border-light dark:border-border-dark text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-800">
<span class="truncate">Browse Schools</span>
</a>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
<div class="col-span-1 md:col-span-2">
<div class="flex items-center gap-3">
<div class="h-8 w-8">
<img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
</div>
<h2 class="text-xl font-bold">GiveToGrow</h2>
</div>
<p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Connecting donors with under-resourced schools to create a lasting impact on education in Ghana.</p>
<div class="mt-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
<span class="material-symbols-outlined !text-xl">verified_user</span>
<span>Secure Payments by Paystack</span>
</div>
</div>
<div>
<h3 class="font-bold mb-4">Quick Links</h3>
<ul class="space-y-2 text-sm">
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">How it Works</a></li>
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">Schools</a></li>
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">About</a></li>
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">Contact</a></li>
</ul>
</div>
<div>
<h3 class="font-bold mb-4">Legal</h3>
<ul class="space-y-2 text-sm">
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">Privacy Policy</a></li>
<li><a class="text-gray-600 dark:text-gray-400 hover:text-primary" href="#">Terms of Service</a></li>
</ul>
</div>
</div>
<div class="mt-8 pt-8 border-t border-border-light dark:border-border-dark text-center text-sm text-gray-500 dark:text-gray-400">
<p>© 2024 GiveToGrow. All rights reserved.</p>
</div>
</div>
</footer>
</div>
</div>
</body></html>
