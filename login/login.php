<?php
// Start session for flash messages + storing user info later
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Get any previous error message and old email from session
$login_error = $_SESSION['login_error'] ?? '';
$old_email   = $_SESSION['old_email'] ?? '';

// Clear them so they don't persist forever
unset($_SESSION['login_error'], $_SESSION['old_email']);

// Simple escape helper
function e($value)
{
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en" class="light">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GiveToGrow - Log In</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700&display=swap"
    rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    rel="stylesheet" />

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#A4B8A4",
            secondary: "#F57C00",
            accent: "#7CB342",
            "background-light": "#FAFAFA",
            "background-dark": "#1A1A1A",
          },
          fontFamily: {
            display: ["Lexend", "sans-serif"],
          },
          borderRadius: {
            DEFAULT: "0.5rem",
            lg: "1rem",
            xl: "1.5rem",
            full: "9999px",
          },
        },
      },
    };
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
    }
  </style>

  <!-- Dark mode toggle logic -->
  <script>
    const applyTheme = (theme) => {
      const root = document.documentElement;
      if (theme === "dark") {
        root.classList.add("dark");
        root.classList.remove("light");
      } else {
        root.classList.remove("dark");
        root.classList.add("light");
      }
    };

    const initTheme = () => {
      const stored = localStorage.getItem("theme");
      if (stored === "dark" || stored === "light") {
        applyTheme(stored);
      } else {
        const prefersDark = window.matchMedia(
          "(prefers-color-scheme: dark)"
        ).matches;
        applyTheme(prefersDark ? "dark" : "light");
      }
    };

    const toggleTheme = () => {
      const isDark = document.documentElement.classList.contains("dark");
      const next = isDark ? "light" : "dark";
      applyTheme(next);
      localStorage.setItem("theme", next);
    };

    // Initialize theme on page load
    initTheme();
  </script>
</head>

<body
  class="font-display bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200">
  <div
    class="relative flex min-h-screen w-full flex-col items-center justify-center p-4">
    <!-- Header -->
    <header
      class="absolute top-0 left-0 right-0 flex items-center justify-between px-6 py-4 sm:px-8 sm:py-6">
      <div class="flex items-center gap-3 text-[#131514] dark:text-white">
        <div class="h-8 w-8">
          <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-full w-full object-contain"/>
        </div>
        <h2 class="text-xl font-bold leading-tight tracking-tight">
          GiveToGrow
        </h2>
      </div>

      <!-- Theme toggle -->
            <!-- Theme toggle -->
      <button
        onclick="toggleTheme()"
        type="button"
        class="flex items-center justify-center h-10 w-10 rounded-full bg-white/70 dark:bg-gray-900/60 border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        aria-label="Toggle theme">
        <span class="material-symbols-outlined dark:hidden">dark_mode</span>
        <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
      </button>
    </header>

    <!-- Login Card -->
    <main class="w-full max-w-md pt-16">
      <div
        class="bg-white dark:bg-background-dark/50 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 sm:p-10">
        <div class="text-center">
          <h1
            class="text-[#131514] dark:text-gray-100 text-3xl font-bold leading-tight tracking-tight">
            Welcome Back
          </h1>
          <p
            class="text-gray-600 dark:text-gray-400 text-base font-normal leading-normal mt-2">
            Log in to make an impact
          </p>
        </div>

        <!-- Error Message -->
        <?php if (!empty($login_error)): ?>
          <div
            class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-500/60 dark:text-red-200">
            <?php echo e($login_error); ?>
          </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST" action="../actions/login_customer.php">
          <!-- Email Field -->
          <div class="flex flex-col">
            <label
              for="email"
              class="text-[#131514] dark:text-gray-200 text-sm font-medium leading-normal pb-2">Email Address</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              placeholder="Enter your email"
              value="<?php echo e($old_email); ?>"
              class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#131514] dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/50 border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-gray-800 h-12 placeholder:text-gray-400 dark:placeholder:text-gray-500 px-4 text-base font-normal leading-normal" />
          </div>

          <!-- Password Field -->
          <div class="flex flex-col">
            <div class="flex justify-between items-baseline">
              <label
                for="password"
                class="text-[#131514] dark:text-gray-200 text-sm font-medium leading-normal pb-2">Password</label>
              <a
                href="#"
                class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
            </div>
            <div class="relative flex w-full flex-1 items-stretch">
              <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="Enter your password"
                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#131514] dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary/50 border border-gray-300 dark:border-gray-600 bg-background-light dark:bg-gray-800 h-12 placeholder:text-gray-400 dark:placeholder:text-gray-500 pl-4 pr-12 text-base font-normal leading-normal" />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 dark:text-gray-400"
                aria-label="Toggle password visibility"
                onclick="
                  const pwd = document.getElementById('password');
                  if (!pwd) return;
                  const isPassword = pwd.type === 'password';
                  pwd.type = isPassword ? 'text' : 'password';
                  this.querySelector('span').textContent = isPassword ? 'visibility' : 'visibility_off';
                ">
                <span class="material-symbols-outlined text-xl">visibility_off</span>
              </button>
            </div>
          </div>

          <!-- CTA Button -->
          <button
            type="submit"
            class="flex w-full items-center justify-center rounded-lg h-12 px-6 text-base font-medium leading-normal text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:focus:ring-offset-background-dark transition-colors">
            Log In
          </button>
        </form>

        <!-- Sign-up Link -->
        <div class="mt-8 text-center">
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Don't have an account?
            <a
              href="../login/register.php"
              class="font-medium text-primary hover:underline">Sign Up</a>
          </p>
        </div>
      </div>
    </main>
  </div>
</body>

</html>