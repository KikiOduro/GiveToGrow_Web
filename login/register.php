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

    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#89a48b",
                        "background-light": "#f7f7f7",
                        "background-dark": "#181a18",
                    },
                    fontFamily: {
                        "display": ["Lexend"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Lexend', sans-serif;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#333333] dark:text-gray-200">
    <div class="relative flex min-h-screen w-full flex-col items-center justify-center p-4 sm:p-6">

        <div class="layout-container flex h-full w-full max-w-md grow flex-col items-center justify-center">

            <!-- Logo + Title -->
            <div class="flex flex-col items-center gap-2 pb-8 pt-6 text-center">
                <svg class="text-primary h-12 w-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <h1 class="text-[#131514] dark:text-white text-3xl font-bold">Join GiveToGrow</h1>
                <p class="text-gray-600 dark:text-gray-400">Start making an impact on education today.</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($register_error)): ?>
                <div class="w-full mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-200">
                    <?= htmlspecialchars($register_error) ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="actions/register_customer.php" class="flex w-full flex-col items-center">

                <!-- Email -->
                <div class="w-full">
                    <label class="flex flex-col w-full">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Email</p>
                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="Enter your email"
                            class="form-input w-full rounded-lg h-14 p-[15px] bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                    </label>
                </div>

                <!-- Password -->
                <div class="w-full pt-4">
                    <label class="flex flex-col w-full">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Password</p>
                        <div class="relative flex items-center">
                            <input
                                type="password"
                                name="password"
                                required
                                placeholder="Enter your password"
                                class="form-input w-full rounded-lg h-14 p-[15px] pr-12 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                            <button type="button" class="absolute right-0 flex h-14 w-14 items-center justify-center text-gray-500 dark:text-gray-400"
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
                    <label class="flex flex-col w-full">
                        <p class="text-[#131514] dark:text-gray-300 text-base font-medium pb-2">Confirm Password</p>
                        <div class="relative flex items-center">
                            <input
                                type="password"
                                name="confirm"
                                required
                                placeholder="Confirm your password"
                                class="form-input w-full rounded-lg h-14 p-[15px] pr-12 bg-white dark:bg-background-dark border border-gray-300 dark:border-gray-700 text-[#131514] dark:text-white placeholder:text-gray-500" />
                            <button type="button" class="absolute right-0 flex h-14 w-14 items-center justify-center text-gray-500 dark:text-gray-400"
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
                    <button type="submit"
                        class="h-14 w-full rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 focus:ring-2 focus:ring-primary">
                        Create Account
                    </button>
                </div>

                <!-- Terms -->
                <div class="w-full px-4 pt-4 pb-6 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        By creating an account, you agree to our
                        <a href="#" class="text-primary font-medium hover:underline">Terms of Service</a>
                        and
                        <a href="#" class="text-primary font-medium hover:underline">Privacy Policy</a>.
                    </p>
                </div>

                <!-- Divider -->
                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>

                <!-- Sign In -->
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