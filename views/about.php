<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';

// Fetch impact statistics from database
require_once 'settings/db_class.php';
$db = new db_connection();

// Total students reached
$students_query = "SELECT SUM(total_students) as total_students FROM schools WHERE status = 'active'";
$students_result = $db->db_fetch_one($students_query);
$total_students = $students_result['total_students'] ?? 0;

// Total partner schools
$schools_query = "SELECT COUNT(*) as total_schools FROM schools WHERE status = 'active'";
$schools_result = $db->db_fetch_one($schools_query);
$total_schools = $schools_result['total_schools'] ?? 0;

// Total regions (count distinct regions from country field)
$regions_query = "SELECT COUNT(DISTINCT country) as total_regions FROM schools WHERE status = 'active'";
$regions_result = $db->db_fetch_one($regions_query);
$total_regions = $regions_result['total_regions'] ?? 0;

// Total amount donated (sum of amount_raised from all schools)
$donated_query = "SELECT SUM(amount_raised) as total_donated FROM schools";
$donated_result = $db->db_fetch_one($donated_query);
$total_donated = $donated_result['total_donated'] ?? 0;

// Format the donation amount
if ($total_donated >= 1000) {
    $donated_display = '$' . number_format($total_donated / 1000, 0) . 'K+';
} else {
    $donated_display = '$' . number_format($total_donated, 0) . '+';
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>About Us – GiveToGrow</title>
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
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#333333] dark:text-background-light font-display">
<div class="relative flex min-h-screen w-full flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">

<!-- Header -->
<header class="sticky top-0 z-50 flex justify-center border-b border-solid border-black/10 dark:border-white/10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm">
    <div class="flex items-center justify-between whitespace-nowrap px-4 sm:px-10 py-3 w-full max-w-6xl">
        <div class="flex items-center gap-4 text-[#131514] dark:text-background-light">
            <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
            <h2 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h2>
        </div>
        <div class="hidden lg:flex flex-1 justify-end gap-8">
            <nav class="flex items-center gap-9">
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="dashboard.php">Home</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="schools.php">Schools</a>
                <a class="text-primary text-sm font-bold leading-normal" href="about.php">About</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="#contact">Contact</a>
            </nav>
            <div class="flex gap-2 items-center">
                <span class="text-sm text-[#131514]\">Welcome, <strong><?php echo $user_name; ?></strong></span>
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
    <section class="flex justify-center py-16 sm:py-24 px-4 bg-gradient-to-b from-primary/10 to-transparent dark:from-primary/20">
        <div class="w-full max-w-4xl text-center">
            <div class="mb-8">
                <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-20 w-auto mx-auto mb-6"/>
            </div>
            <h1 class="text-[#131514] dark:text-background-light text-4xl sm:text-5xl font-black leading-tight tracking-[-0.033em] mb-6">
                About GiveToGrow
            </h1>
            <p class="text-gray-600 dark:text-gray-300 text-lg sm:text-xl leading-relaxed max-w-3xl mx-auto">
                We're on a mission to bridge the education gap in Ghana by connecting generous donors directly with schools that need resources the most.
            </p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="flex justify-center py-16 px-4">
        <div class="w-full max-w-6xl">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] mb-6">
                        Our Story
                    </h2>
                    <div class="space-y-4 text-gray-600 dark:text-gray-300">
                        <p>
                            GiveToGrow was founded in 2023 with a simple yet powerful vision: to ensure that every child in Ghana has access to quality education, regardless of their economic circumstances.
                        </p>
                        <p>
                            We recognized that many schools across the continent lack basic resources – from textbooks and desks to clean water and technology. While these needs are critical, connecting donors with the right schools remained a challenge.
                        </p>
                        <p>
                            That's where GiveToGrow comes in. We've created a transparent platform that allows you to see exactly what schools need, choose what to fund, and track the impact of your contribution in real-time.
                        </p>
                    </div>
                </div>
                <div class="rounded-xl overflow-hidden shadow-lg">
                    <img src="https://www.happyghana.com/wp-content/uploads/2025/06/school-children.jpg" 
                         alt="Ghanaian students in classroom" 
                         class="w-full h-full object-cover rounded-2xl shadow-xl"/>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Mission Section -->
    <section class="flex justify-center py-16 px-4 bg-primary/5 dark:bg-primary/10">
        <div class="w-full max-w-6xl">
            <h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] mb-12 text-center">
                Our Mission & Values
            </h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary mb-6 mx-auto">
                        <span class="material-symbols-outlined text-3xl">visibility</span>
                    </div>
                    <h3 class="text-[#131514] dark:text-background-light text-xl font-bold mb-4 text-center">Transparency</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-center">
                        Every donation is tracked, and you can see exactly how your contribution is being used to transform classrooms.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary mb-6 mx-auto">
                        <span class="material-symbols-outlined text-3xl">handshake</span>
                    </div>
                    <h3 class="text-[#131514] dark:text-background-light text-xl font-bold mb-4 text-center">Direct Impact</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-center">
                        We connect you directly with schools, eliminating middlemen and ensuring maximum impact from your generosity.
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-center justify-center h-14 w-14 rounded-full bg-primary/20 text-primary mb-6 mx-auto">
                        <span class="material-symbols-outlined text-3xl">verified</span>
                    </div>
                    <h3 class="text-[#131514] dark:text-background-light text-xl font-bold mb-4 text-center">Accountability</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-center">
                        All schools are thoroughly vetted, and we regularly verify that funds are used exactly as promised.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Stats Section -->
    <section class="flex justify-center py-16 px-4">
        <div class="w-full max-w-6xl">
            <h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] mb-12 text-center">
                Our Impact So Far
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-5xl font-black text-primary mb-3"><?php echo number_format($total_students); ?>+</div>
                    <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Students Reached</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-primary mb-3"><?php echo $total_schools; ?></div>
                    <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Partner Schools</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-primary mb-3"><?php echo $total_regions; ?></div>
                    <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Regions</div>
                </div>
                <div class="text-center">
                    <div class="text-5xl font-black text-primary mb-3"><?php echo $donated_display; ?></div>
                    <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Donated</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How We Work Section -->
    <section class="flex justify-center py-16 px-4 bg-primary/5 dark:bg-primary/10">
        <div class="w-full max-w-6xl">
            <h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] mb-12 text-center">
                How We Work
            </h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">1</div>
                        <div>
                            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold mb-2">School Vetting</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                We carefully assess each school to verify their needs, ensure they're legitimate, and confirm they have the capacity to manage resources effectively.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">2</div>
                        <div>
                            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold mb-2">Project Listing</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                Schools submit specific needs with detailed descriptions, costs, and expected impact. These are published on our platform for donors to review.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">3</div>
                        <div>
                            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold mb-2">Secure Donations</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                Donors choose projects that resonate with them and contribute securely through our platform using trusted payment processors like Paystack.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-black/10 dark:border-white/10">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">4</div>
                        <div>
                            <h3 class="text-[#131514] dark:text-background-light text-lg font-bold mb-2">Impact Tracking</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                Once funded, schools receive resources and provide photo updates. Donors receive regular reports showing the tangible impact of their contribution.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section (Optional) -->
    <section class="flex justify-center py-16 px-4">
        <div class="w-full max-w-6xl">
            <h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] mb-6 text-center">
                Why Choose GiveToGrow?
            </h2>
            <p class="text-gray-600 dark:text-gray-300 text-center max-w-3xl mx-auto mb-12">
                Unlike traditional charity models, we provide complete transparency and direct connection between donors and schools. You don't just give money – you become part of a school's transformation story.
            </p>
            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0">check_circle</span>
                    <div>
                        <h3 class="text-[#131514] dark:text-background-light font-bold mb-1">100% Transparent</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">See exactly where your money goes and the impact it creates</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0">check_circle</span>
                    <div>
                        <h3 class="text-[#131514] dark:text-background-light font-bold mb-1">Verified Schools</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">All partner schools are thoroughly vetted and monitored</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0">check_circle</span>
                    <div>
                        <h3 class="text-[#131514] dark:text-background-light font-bold mb-1">Secure Payments</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">Bank-level security with trusted payment partners</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0">check_circle</span>
                    <div>
                        <h3 class="text-[#131514] dark:text-background-light font-bold mb-1">Regular Updates</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">Receive photos and reports showing your donation's impact</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="contact" class="flex justify-center py-16 px-4 bg-gradient-to-b from-transparent to-primary/10 dark:to-primary/20">
        <div class="w-full max-w-4xl text-center">
            <h2 class="text-[#131514] dark:text-background-light text-3xl sm:text-4xl font-bold leading-tight tracking-[-0.015em] mb-6">
                Join Us in Transforming Education
            </h2>
            <p class="text-gray-600 dark:text-gray-300 text-lg mb-8 max-w-2xl mx-auto">
                Whether you want to donate, partner with us, or learn more about our work, we'd love to hear from you.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="schools.php" class="flex min-w-[160px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90 transition-all">
                    Browse Schools
                </a>
                <a href="dashboard.php#contact" class="flex min-w-[160px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-white dark:bg-gray-800 text-[#131514] dark:text-background-light text-base font-bold leading-normal tracking-[0.015em] border-2 border-primary hover:bg-primary/10 dark:hover:bg-primary/20 transition-all">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<footer class="bg-gray-100 dark:bg-gray-900">
    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center gap-2 text-[#131514] dark:text-background-light mb-4">
                    <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-6 w-auto"/>
                    <h2 class="text-lg font-bold">GiveToGrow</h2>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Connecting generosity with classrooms in need.</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase mb-4">Quick Links</h3>
                <ul class="space-y-3">
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="dashboard.php">Home</a></li>
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="schools.php">Schools</a></li>
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="about.php">About</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase mb-4">Support</h3>
                <ul class="space-y-3">
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="mailto:info@givetogrow.org">Contact</a></li>
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="#">FAQs</a></li>
                    <li><a class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary" href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase mb-4">Secure Payments</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Powered by Paystack</p>
            </div>
        </div>
        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8 text-center">
            <p class="text-sm text-gray-400">© 2024 GiveToGrow. All rights reserved.</p>
        </div>
    </div>
</footer>

</div>
</div>
</body>
</html>
