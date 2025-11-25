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
        function initTheme() {
            const theme = localStorage.getItem('theme') || 'light';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        }
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
        // Initialize theme on page load
        initTheme();
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
<img src="assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
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
<button onclick="toggleTheme()" class="flex items-center justify-center h-10 w-10 rounded-full bg-background-light dark:bg-card-dark border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Toggle theme">
<span class="material-symbols-outlined dark:hidden">dark_mode</span>
<span class="material-symbols-outlined hidden dark:inline">light_mode</span>
</button>
<a href="login/login.php" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-background-light dark:bg-card-dark text-sm font-bold border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-gray-700">
<span class="truncate">Log In</span>
</a>
<a href="login/register.php" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
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
<p class="text-base lg:text-lg text-gray-600 dark:text-gray-400">Connect with under-resourced schools in Africa and provide the tools they need to succeed. Your contribution makes a direct impact.</p>
</div>
<div class="flex flex-wrap gap-4 justify-center lg:justify-start">
<button class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-primary text-white text-base font-bold hover:opacity-90">
<span class="truncate">Donate Now</span>
</button>
<button class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-transparent text-sm font-bold border border-border-light dark:border-border-dark hover:bg-gray-100 dark:hover:bg-gray-800">
<span class="truncate">See how it works</span>
</button>
</div>
</div>
<div class="relative w-full aspect-[4/3] bg-center bg-no-repeat bg-cover rounded-xl shadow-lg" data-alt="Smiling African students in a classroom" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCi5OCWzAydhaRtSWfJ_6_A42t3KvWrWj6xNp0GhAZB3YQbeBIPHSKTpx2idI-XoigskdqDRhAJDZT0FqcRrErM7ILRESxdn6BP3nP2Lxa8SQqqxqHIKP8e9NeWvghZhDLtzMHGRrKcYSpnqs5z6v597NYcoBrzv3EdDVhD1xY5KeF_mEx_b4Prmm1U6_HWD3aGmZjN-tfdPeqyUQR0XJzfQWyqFXPErEEv8vniM0hbeOOZmSQDZfdxRdU4EeOg4XaqSih1efcw706C");'>
<div class="absolute -bottom-6 -right-6 bg-card-light dark:bg-card-dark p-4 rounded-lg shadow-xl w-64 border border-border-light dark:border-border-dark">
<p class="text-sm font-bold mb-1">Kigali Primary School</p>
<p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Needs 50 new desks</p>
<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
<div class="bg-primary h-2.5 rounded-full" style="width: 75%"></div>
</div>
<p class="text-right text-xs mt-1 font-medium">75% Funded</p>
</div>
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
<!-- School Card 1 -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-md overflow-hidden flex flex-col border border-border-light dark:border-border-dark">
<div class="relative">
<img class="h-48 w-full object-cover" alt="A classroom in Ghana with students at desks" src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800&h=600&fit=crop"/>
<div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">High Priority</div>
</div>
<div class="p-6 flex-grow flex flex-col">
<h3 class="text-lg font-bold">Bright Future Academy</h3>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Accra, Ghana</p>
<p class="text-sm text-gray-600 dark:text-gray-400 flex-grow mb-4">Seeking funds for new textbooks to improve literacy for 150 students.</p>
<div class="mb-4">
<div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
<span>$4,150 raised</span>
<span>$5,000 goal</span>
</div>
<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 83%;"></div>
</div>
</div>
<div class="flex items-center gap-2 mt-auto">
<button class="w-full flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
<span>View School</span>
</button>
<button class="flex-shrink-0 flex cursor-pointer items-center justify-center rounded-full h-10 w-10 bg-primary/20 text-primary hover:bg-primary/30">
<span class="material-symbols-outlined">add_shopping_cart</span>
</button>
</div>
</div>
</div>
            <!-- School Card 2 -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-md overflow-hidden flex flex-col border border-border-light dark:border-border-dark">
<img class="h-48 w-full object-cover" alt="Children playing soccer outside a school building in Kenya" src="https://images.unsplash.com/photo-1529390079861-591de354faf5?w=800&h=600&fit=crop"/>
<div class="p-6 flex-grow flex flex-col">
<h3 class="text-lg font-bold">Hope &amp; Progress School</h3>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Nairobi, Kenya</p>
<p class="text-sm text-gray-600 dark:text-gray-400 flex-grow mb-4">Needs sports equipment to promote physical education and teamwork.</p>
<div class="mb-4">
<div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
<span>$600 raised</span>
<span>$1,000 goal</span>
</div>
<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 60%;"></div>
</div>
</div>
<div class="flex items-center gap-2 mt-auto">
<button class="w-full flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
<span>View School</span>
</button>
<button class="flex-shrink-0 flex cursor-pointer items-center justify-center rounded-full h-10 w-10 bg-primary/20 text-primary hover:bg-primary/30">
<span class="material-symbols-outlined">add_shopping_cart</span>
</button>
</div>
</div>
</div>
            <!-- School Card 3 -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-md overflow-hidden flex flex-col border border-border-light dark:border-border-dark">
<img class="h-48 w-full object-cover" alt="A student writing on a chalkboard in a rural classroom in Rwanda" src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=600&fit=crop"/>
<div class="p-6 flex-grow flex flex-col">
<h3 class="text-lg font-bold">Ubumwe Learning Center</h3>
<p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Kigali, Rwanda</p>
<p class="text-sm text-gray-600 dark:text-gray-400 flex-grow mb-4">Raising funds for classroom essentials like chalk, notebooks, and pencils.</p>
<div class="mb-4">
<div class="flex justify-between text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
<span>$450 raised</span>
<span>$500 goal</span>
</div>
<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
<div class="bg-primary h-2 rounded-full" style="width: 90%;"></div>
</div>
</div>
<div class="flex items-center gap-2 mt-auto">
<button class="w-full flex cursor-pointer items-center justify-center overflow-hidden rounded-full h-10 px-4 bg-primary text-white text-sm font-bold hover:opacity-90">
<span>View School</span>
</button>
<button class="flex-shrink-0 flex cursor-pointer items-center justify-center rounded-full h-10 w-10 bg-primary/20 text-primary hover:bg-primary/30">
<span class="material-symbols-outlined">add_shopping_cart</span>
</button>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- Impact Metrics Section -->
<section class="bg-primary/90 text-white py-12 sm:py-16">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
<div>
<p class="text-4xl lg:text-5xl font-black">12,500+</p>
<p class="text-lg font-medium mt-2">Students Supported</p>
</div>
<div>
<p class="text-4xl lg:text-5xl font-black">85</p>
<p class="text-lg font-medium mt-2">Schools Partnered</p>
</div>
<div>
<p class="text-4xl lg:text-5xl font-black">7</p>
<p class="text-lg font-medium mt-2">Countries Reached</p>
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
<button class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-primary text-white text-base font-bold hover:opacity-90">
<span class="truncate">Donate Now</span>
</button>
<button class="flex min-w-[120px] cursor-pointer items-center justify-center overflow-hidden rounded-full h-12 px-6 bg-transparent border border-border-light dark:border-border-dark text-sm font-bold hover:bg-gray-100 dark:hover:bg-gray-800">
<span class="truncate">Browse Schools</span>
</button>
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
<img src="assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
</div>
<h2 class="text-xl font-bold">GiveToGrow</h2>
</div>
<p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Connecting donors with under-resourced schools to create a lasting impact on education in Africa.</p>
<div class="mt-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
<span class="material-symbols-outlined !text-xl">verified_user</span>
<span>Secure Payments by Stripe</span>
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
