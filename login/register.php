<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$register_error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GiveToGrow - Create Account</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#A4B8A4",
                        "secondary": "#F57C00",
                        "accent": "#7CB342",
                        "background-light": "#FAFAFA",
                        "background-dark": "#1A1A1A",
                    },
                    fontFamily: {
                        "display": ["Lexend"]
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
    </script>

    <style>
        body {
            font-family: 'Lexend', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings:
                "FILL" 0,
                "wght" 400,
                "GRAD" 0,
                "opsz" 24;
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

<body class="bg-background-light dark:bg-background-dark font-display text-[#333333] dark:text-gray-200">
    <div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4 sm:p-6">

        <!-- Header with theme toggle -->
        <header class="absolute top-0 left-0 right-0 flex items-center justify-between px-6 py-4 sm:px-8 sm:py-6">
            <div class="flex items-center gap-3 text-[#131514] dark:text-white">
                <div class="h-8 w-8">
                    <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
                </div>
                <h2 class="text-xl font-bold leading-tight tracking-tight">GiveToGrow</h2>
            </div>
            <button onclick="toggleTheme()" class="flex items-center justify-center h-10 w-10 rounded-full bg-white/70 dark:bg-gray-900/60 border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Toggle theme">
                <span class="material-symbols-outlined dark:hidden">dark_mode</span>
                <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
            </button>
        </header>

        <div class="layout-container flex h-full w-full max-w-md grow flex-col items-center justify-center pt-16">

            <!-- Header -->
            <div class="flex flex-col items-center gap-2 pb-8 pt-6 text-center">
                <div class="h-12 w-12">
                    <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
                </div>
                <h1 class="text-[#131514] dark:text-white text-3xl font-bold leading-tight">
                    Join GiveToGrow
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-base">
                    Start making an impact on education today.
                </p>
            </div>

            <!-- Error message -->
            <?php if (!empty($register_error)): ?>
                <div class="w-full mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-200">
                    <?= htmlspecialchars($register_error) ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form
                method="POST"
                action="../actions/register_customer.php"
                class="flex w-full flex-col items-center">

                <!-- Email -->
                <div class="w-full">
                    <label class="flex w-full flex-col">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Email</p>
                        <input
                            type="email"
                            name="email"
                            required
                            autocomplete="off"
                            placeholder="Enter your email"
                            class="form-input flex w-full rounded-lg h-14 p-[15px] bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                    </label>
                </div>

                <!-- Password -->
                <div class="w-full pt-4">
                    <label class="flex w-full flex-col">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Password</p>
                        <div class="relative flex w-full items-center">
                            <input
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Enter your password"
                                class="form-input flex w-full rounded-lg h-14 p-[15px] pr-12 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                            <button
                                type="button"
                                class="absolute right-0 flex h-14 w-14 items-center justify-center text-gray-500 dark:text-gray-400"
                                onclick="
                const p = this.previousElementSibling;
                p.type = p.type === 'password' ? 'text' : 'password';
                this.querySelector('span').textContent = p.type === 'password' ? 'visibility' : 'visibility_off';
              ">
                                <span class="material-symbols-outlined text-2xl">visibility</span>
                            </button>
                        </div>
                    </label>
                </div>

                <!-- Confirm Password -->
                <div class="w-full pt-4">
                    <label class="flex w-full flex-col">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Confirm Password</p>
                        <div class="relative flex w-full items-center">
                            <input
                                type="password"
                                name="confirm"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                                class="form-input flex w-full rounded-lg h-14 p-[15px] pr-12 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                            <button
                                type="button"
                                class="absolute right-0 flex h-14 w-14 items-center justify-center text-gray-500 dark:text-gray-400"
                                onclick="
                const p = this.previousElementSibling;
                p.type = p.type === 'password' ? 'text' : 'password';
                this.querySelector('span').textContent = p.type === 'password' ? 'visibility' : 'visibility_off';
              ">
                                <span class="material-symbols-outlined text-2xl">visibility</span>
                            </button>
                        </div>
                    </label>
                </div>

                <!-- Submit -->
                <div class="w-full pt-6">
                    <button
                        type="submit"
                        class="flex h-14 w-full items-center justify-center rounded-lg bg-primary px-6 text-base font-semibold text-white transition-colors hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-background-dark">
                        Create Account
                    </button>
                </div>

                <!-- Terms -->
                <div class="w-full px-4 pt-4 pb-6 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        By creating an account, you agree to our
                        <a href="#" class="font-medium text-primary hover:underline">Terms of Service</a>
                        and
                        <a href="#" class="font-medium text-primary hover:underline">Privacy Policy</a>.
                    </p>
                </div>

                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>

                <!-- Sign in link -->
                <div class="w-full pt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Already have an account?
                        <a href="login.php" class="font-semibold text-primary hover:underline">Sign In</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>

</html>