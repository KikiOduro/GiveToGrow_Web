<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
$user_email = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '';
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
                        "background-dark": "#181a18",
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
    <img src="assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
    <h2 class="text-[#131514] dark:text-background-light text-lg font-bold leading-tight tracking-[-0.015em]">GiveToGrow</h2>
</div>
<div class="hidden lg:flex flex-1 justify-end gap-8">
                <nav class="flex items-center gap-9">
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="#how-it-works">How it works</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="schools.php">Schools</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="about.php">About</a>
                <a class="text-[#131514] dark:text-background-light text-sm font-medium leading-normal hover:text-primary dark:hover:text-primary" href="#contact">Contact</a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a class="text-primary text-sm font-bold leading-normal border border-primary rounded-full px-4 py-2 hover:bg-primary/10" href="admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
            </nav>
    <div class="flex gap-2 items-center">
        <span class="text-sm text-[#131514] dark:text-background-light">Welcome, <strong><?php echo $user_name; ?></strong></span>
        <button onclick="toggleTheme()" class="flex items-center justify-center h-10 w-10 rounded-full bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light hover:bg-primary/30 dark:hover:bg-primary/40">
            <span class="material-symbols-outlined dark:hidden">dark_mode</span>
            <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
        </button>
        <a href="actions/logout.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light dark:bg-primary/20 text-[#131514] dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] border border-primary/20 dark:border-primary/50 hover:bg-primary/10 dark:hover:bg-primary/30">
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
<div class="flex min-h-[520px] flex-col gap-6 bg-cover bg-center bg-no-repeat rounded-xl items-start justify-end px-6 pb-10 sm:px-10" data-alt="Happy African students smiling in a bright, clean classroom." style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.5) 100%), url("https://lh3.googleusercontent.com/aida-public/AB6AXuBVFzqJcKl-TbWfa1_ZZeK6tYZYBJc6thvqIwgfsTRLq-byXkWQITBj5OIqaN8h01AMG4L89RKTBzG12B5tiN1SonTClS6IMvNZrEcL1AH7UjUIhYyCXIbchdlX-nJqrNwKjcT7EgTZKjLxMZU420nPzJe6jHKPffmWfdKK_4E_mwPELmLM72f-CsS8zQU9rppDXh8x1naWB5QuKEdwnYbC8inI330ucAJJ8IC3Q6GXg12g0yvA7DIrlLjqY0uVmnrzrSYWra3cREUt");'>
<div class="flex flex-col gap-2 text-left max-w-2xl">
    <h1 class="text-white text-4xl font-black leading-tight tracking-[-0.033em] @[480px]:text-5xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em]">
        Turn everyday generosity into better classrooms.
    </h1>
    <h2 class="text-white/90 text-sm font-normal leading-normal @[480px]:text-base @[480px]:font-normal @[480px]:leading-normal">
        Connect directly with under-resourced schools in Africa and see the tangible impact of your contribution. Every gift helps build a brighter future.
    </h2>
</div>
<div class="flex-wrap gap-3 flex">
    <button class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Donate Now</span>
    </button>
    <a href="#how-it-works" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">See how it works</span>
    </a>
</div>
<div class="absolute bottom-6 right-6 hidden md:block bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-sm p-4 rounded-lg w-64 shadow-lg">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-cover bg-center" data-alt="Portrait of a smiling young African student." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBH7oj1u2Jsw05DbtSujq7KDqct51cQVTTsche9wce2fwUBqcy6TLHF4DYaWynQUz2ceDUvdj_PTOwtjKdo3K7KTKVc-SPH7MgiBax2hHC4j5lEvV2A7_hLYzZZ80g9s4-At9IoomYcYJs0W7FIGvj9zvQ1p3TQr9whxBH10eaXY4UTyKP0aUy96G8tq8SxE1MWOOdEYfjdKeTtCilxHQlh7MB9cMZjOtAMUJlh4gQrTMmPVeb4YbFfrdbHy-Q6I_kvpd8-miIvDhxL');"></div>
        <div>
            <p class="text-xs font-medium text-gray-600 dark:text-gray-300">A recent donation from</p>
            <p class="font-bold text-sm text-[#131514] dark:text-background-light">Anna T.</p>
        </div>
    </div>
    <p class="mt-2 text-sm text-[#131514] dark:text-background-light">Funded <span class="font-bold text-primary">new science textbooks</span> for Sunrise Academy.</p>
</div>
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

<!-- Featured Schools Section -->
<section id="schools" class="flex justify-center py-10 sm:py-16 px-4">
<div class="w-full max-w-6xl">
<h2 class="text-[#131514] dark:text-background-light text-3xl font-bold leading-tight tracking-[-0.015em] px-4 pb-8 text-center">Featured Schools</h2>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- School Card 1 -->
    <div class="flex flex-col rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark shadow-sm">
        <img class="h-48 w-full object-cover" alt="A small, well-kept school building in a rural African village." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDmldrHaMVXufNKcfMS4ElyMeiIv4dIJ6sEuZXvNoGzYZSrEiytt2c7p8ojS6J3GW6v9rl6GeNHjM25uBtggyyoOtvhP7OXrMCRVo7agGk9BiUbCt2dsXK8MButOyu0FB3Y_EEPbVy64M9ad8NQONPyVZJWbHyJ3crkp6pGs9PEzrr1hh-6o-cwEMLbhgp-8kkc1gZw7ftpHeg3_P6sl8akNDONdBATbNS8ZFOasvaSwF6sqBw-xRiXCgLfpCW2fsXlw1p-DFbZf6IO"/>
        <div class="p-6 flex flex-col flex-1">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-[#131514] dark:text-background-light">Hope Academy</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kenya</p>
                </div>
                <span class="text-xs font-bold bg-primary/20 text-primary px-2 py-1 rounded-full">Books</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 flex-1">Funding for 150 new reading books to build their first school library.</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                <div class="bg-primary h-2.5 rounded-full" style="width: 75%"></div>
            </div>
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                <span>$1,500 Raised</span>
                <span>$2,000 Goal</span>
            </div>
            <div class="flex gap-2 mt-auto">
                <button class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">View School</button>
                <button class="flex-shrink-0 flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
                    <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- School Card 2 -->
    <div class="flex flex-col rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark shadow-sm">
        <img class="h-48 w-full object-cover" alt="Students sitting at new wooden desks in a classroom." src="https://lh3.googleusercontent.com/aida-public/AB6AXuB-YGgj4qPRLT-uTLRyQJRYeuOoNWtrcc2ocSWxBBR8KhZOk5BEScQ4UCpmArzEtdOYKmL9ZSo0brkN_MeuAOJ-tmtZTxQ5Hw-BOC2rCj73rVZujKaO9RtN-y-kP5bUeSW3kzJC5ZmlBUxsfTEDy59Rby4nTU23ncI2d-TAxRdqG10VHGSyeGPo5Uo2SPg2wgour5oJ_6U7o3O-aTdSKu5lfzte5uk6VQ5cS_N-qTC9Ik8lQ2yLexnV45ehPbMM8sMJVaulNBp4xx2x"/>
        <div class="p-6 flex flex-col flex-1">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-[#131514] dark:text-background-light">Bwindi Primary</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Uganda</p>
                </div>
                <span class="text-xs font-bold bg-primary/20 text-primary px-2 py-1 rounded-full">Desks</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 flex-1">Help us replace 30 broken desks to provide a proper learning space for students.</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                <div class="bg-primary h-2.5 rounded-full" style="width: 40%"></div>
            </div>
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                <span>$600 Raised</span>
                <span>$1,500 Goal</span>
            </div>
            <div class="flex gap-2 mt-auto">
                <button class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">View School</button>
                <button class="flex-shrink-0 flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
                    <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- School Card 3 -->
    <div class="flex flex-col rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-background-light dark:bg-background-dark shadow-sm">
        <img class="h-48 w-full object-cover" alt="Close-up of a child's hands with a new pencil and notebook." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDn03pFytsEivL2CDdOgcFCuR4Ejc4t7UjpKSJR-Lfb8icpeWJjf9cnt9qgycbjj9SOLVrel_GUwXi6bgaFtKrF7n-gMlmmq3ezjj7OwL5dtC62lKPK8hm0rg_G33W2-4sJ7MBCQ49EHeTBghVUZxp46E9W_dt6eAF5yuO3HQiBxE2DUYFcMR8BZxI5IlqBocCaxJhBsegNNBcSpnQSrsL9Ls9ke_zZ2eVWJScE6eT02DuEHiTJ5f3DqYzK_aTGS7dzY84821J6qxF6"/>
        <div class="p-6 flex flex-col flex-1">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-[#131514] dark:text-background-light">Zola Community School</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Zambia</p>
                </div>
                <span class="text-xs font-bold bg-primary/20 text-primary px-2 py-1 rounded-full">Supplies</span>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 flex-1">Provide essential school supplies like notebooks and pencils for an entire year.</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-2">
                <div class="bg-primary h-2.5 rounded-full" style="width: 90%"></div>
            </div>
            <div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-4">
                <span>$450 Raised</span>
                <span>$500 Goal</span>
            </div>
            <div class="flex gap-2 mt-auto">
                <button class="flex-1 flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary/20 text-primary dark:bg-primary/30 dark:text-background-light text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/30 dark:hover:bg-primary/40">View School</button>
                <button class="flex-shrink-0 flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 w-10 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
                    <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                </button>
            </div>
        </div>
    </div>
</div>
</div>
</section>

<!-- Impact Metrics Section -->
<section id="about" class="flex justify-center py-10 sm:py-20 px-4">
<div class="w-full max-w-6xl">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
    <div>
        <p class="text-5xl font-black text-primary">12,000+</p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Students Supported</p>
    </div>
    <div>
        <p class="text-5xl font-black text-primary">85</p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Schools Partnered</p>
    </div>
    <div>
        <p class="text-5xl font-black text-primary">6</p>
        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">Countries Reached</p>
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
    <button class="flex min-w-[84px] max-w-sm mx-auto sm:mx-0 cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
        <span class="truncate">Donate Now</span>
    </button>
    <a href="#schools" class="flex min-w-[84px] max-w-sm mx-auto sm:mx-0 cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-5 bg-background-light dark:bg-background-dark text-[#131514] dark:text-background-light text-base font-bold leading-normal tracking-[0.015em] hover:bg-opacity-90">
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
            <img src="assets/logo.png" alt="GiveToGrow Logo" class="h-6 w-auto"/>
            <h2 class="text-lg font-bold">GiveToGrow</h2>
        </div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Connecting generosity with classrooms in need.</p>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-wider uppercase">Quick Links</h3>
        <ul class="mt-4 space-y-4">
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#how-it-works">How it Works</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#schools">Our Schools</a></li>
            <li><a class="text-base text-gray-500 dark:text-gray-400 hover:text-primary" href="#about">About Us</a></li>
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
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Payments are securely processed by Stripe.</p>
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
