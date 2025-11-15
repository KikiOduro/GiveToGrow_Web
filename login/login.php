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
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"
    rel="stylesheet" />

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#89a48b",
            "background-light": "#f7f7f7",
            "background-dark": "#181a18",
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

    document.addEventListener("DOMContentLoaded", () => {
      initTheme();

      const btn = document.getElementById("theme-toggle");
      if (btn) {
        btn.addEventListener("click", () => {
          const isDark = document.documentElement.classList.contains("dark");
          const next = isDark ? "light" : "dark";
          applyTheme(next);
          localStorage.setItem("theme", next);
        });
      }
    });
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
        <div class="h-6 w-6 text-primary">
          <svg
            viewBox="0 0 48 48"
            xmlns="http://www.w3.org/2000/svg"
            class="h-full w-full"
            fill="none">
            <g clip-path="url(#clip0_6_543)">
              <path
                d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z"
                fill="currentColor"></path>
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M7.24189 26.4066C7.31369 26.4411 7.64204 26.5637 8.52504 26.3738C9.59462 26.1438 11.0343 25.5311 12.7183 24.4963C14.7583 23.2426 17.0256 21.4503 19.238 19.238C21.4503 17.0256 23.2426 14.7583 24.4963 12.7183C25.5311 11.0343 26.1438 9.59463 26.3738 8.52504C26.5637 7.64204 26.4411 7.31369 26.4066 7.24189C26.345 7.21246 26.143 7.14535 25.6664 7.1918C24.9745 7.25925 23.9954 7.5498 22.7699 8.14278C20.3369 9.32007 17.3369 11.4915 14.4142 14.4142C11.4915 17.3369 9.32007 20.3369 8.14278 22.7699C7.5498 23.9954 7.25925 24.9745 7.1918 25.6664C7.14534 26.143 7.21246 26.345 7.24189 26.4066ZM29.9001 10.7285C29.4519 12.0322 28.7617 13.4172 27.9042 14.8126C26.465 17.1544 24.4686 19.6641 22.0664 22.0664C19.6641 24.4686 17.1544 26.465 14.8126 27.9042C13.4172 28.7617 12.0322 29.4519 10.7285 29.9001L21.5754 40.747C21.6001 40.7606 21.8995 40.931 22.8729 40.7217C23.9424 40.4916 25.3821 39.879 27.0661 38.8441C29.1062 37.5904 31.3734 35.7982 33.5858 33.5858C35.7982 31.3734 37.5904 29.1062 38.8441 27.0661C39.879 25.3821 40.4916 23.9425 40.7216 22.8729C40.931 21.8995 40.7606 21.6001 40.747 21.5754L29.9001 10.7285ZM29.2403 4.41187L43.5881 18.7597C44.9757 20.1473 44.9743 22.1235 44.6322 23.7139C44.2714 25.3919 43.4158 27.2666 42.252 29.1604C40.8128 31.5022 38.8165 34.012 36.4142 36.4142C34.012 38.8165 31.5022 40.8128 29.1604 42.252C27.2666 43.4158 25.3919 44.2714 23.7139 44.6322C22.1235 44.9743 20.1473 44.9757 18.7597 43.5881L4.41187 29.2403C3.29027 28.1187 3.08209 26.5973 3.21067 25.2783C3.34099 23.9415 3.8369 22.4852 4.54214 21.0277C5.96129 18.0948 8.43335 14.7382 11.5858 11.5858C14.7382 8.43335 18.0948 5.9613 21.0277 4.54214C22.4852 3.8369 23.9415 3.34099 25.2783 3.21067C26.5973 3.08209 28.1187 3.29028 29.2403 4.41187Z"
                fill="currentColor"></path>
            </g>
            <defs>
              <clipPath id="clip0_6_543">
                <rect width="48" height="48" fill="white" />
              </clipPath>
            </defs>
          </svg>
        </div>
        <h2 class="text-xl font-bold leading-tight tracking-tight">
          GiveToGrow
        </h2>
      </div>

      <!-- Theme toggle -->
      <button
        id="theme-toggle"
        type="button"
        class="inline-flex items-center gap-1 rounded-full border border-gray-300 dark:border-gray-700 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-200 bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        aria-label="Toggle theme">
        <span class="material-symbols-outlined text-sm">dark_mode</span>
        <span>Theme</span>
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

        <form class="mt-8 space-y-6" method="POST" action="login_process.php">
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
              href="../register.php"
              class="font-medium text-primary hover:underline">Sign Up</a>
          </p>
        </div>
      </div>
    </main>
  </div>
</body>

</html>