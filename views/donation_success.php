<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$donation_id = isset($_GET['donation_id']) ? intval($_GET['donation_id']) : 0;

if ($donation_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$user_id = $_SESSION['user_id'];

// Fetch donation details
$donation_query = "SELECT d.*, s.school_name, s.country, sn.item_name 
                   FROM donations d
                   JOIN schools s ON d.school_id = s.school_id
                   JOIN school_needs sn ON d.need_id = sn.need_id
                   WHERE d.donation_id = ? AND d.user_id = ?";
$donation = $db->db_fetch_one($donation_query, [$donation_id, $user_id]);

if (!$donation) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Donation Successful - GiveToGrow</title>
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
                },
            },
        }
        
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     document.documentElement.classList.toggle('dark', theme === 'dark');
        // }
        // initTheme();
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        @keyframes checkmark {
            0% { stroke-dashoffset: 100; }
            100% { stroke-dashoffset: 0; }
        }
        .checkmark {
            stroke-dasharray: 100;
            animation: checkmark 0.8s ease-in-out 0.4s forwards;
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
<div class="relative flex min-h-screen w-full flex-col items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Success Icon -->
        <div class="flex justify-center mb-8">
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-16 h-16" viewBox="0 0 52 52">
                        <circle class="checkmark" cx="26" cy="26" r="25" fill="none" stroke="#10b981" stroke-width="2"/>
                        <path class="checkmark" fill="none" stroke="#10b981" stroke-width="3" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Success Message -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-[#131514] dark:text-white mb-2">
                Thank You for Your Donation!
            </h1>
            <p class="text-neutral-600 dark:text-neutral-400">
                Your generous contribution will make a real difference at <?php echo htmlspecialchars($donation['school_name']); ?>.
            </p>
        </div>
        
        <!-- Donation Details Card -->
        <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-800 p-6 mb-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-4 border-b border-neutral-200 dark:border-neutral-800">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Donation ID</span>
                    <span class="font-bold text-[#131514] dark:text-white">#<?php echo str_pad($donation_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Item</span>
                    <span class="font-semibold text-[#131514] dark:text-white"><?php echo htmlspecialchars($donation['item_name']); ?></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">School</span>
                    <span class="font-semibold text-[#131514] dark:text-white"><?php echo htmlspecialchars($donation['school_name']); ?></span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Quantity</span>
                    <span class="font-semibold text-[#131514] dark:text-white">×<?php echo $donation['quantity']; ?></span>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-neutral-200 dark:border-neutral-800">
                    <span class="text-base font-bold text-[#131514] dark:text-white">Total Amount</span>
                    <span class="text-2xl font-black text-primary">₵<?php echo number_format($donation['amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="schools.php" 
               class="flex w-full items-center justify-center rounded-lg h-12 bg-primary text-white text-base font-bold hover:opacity-90 transition-opacity">
                Continue Donating
            </a>
            <a href="dashboard.php" 
               class="flex w-full items-center justify-center rounded-lg h-12 bg-neutral-200 dark:bg-neutral-800 text-[#131514] dark:text-white text-base font-bold hover:bg-neutral-300 dark:hover:bg-neutral-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
        
        <!-- Email Confirmation Notice -->
        <div class="mt-8 text-center">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                <span class="material-symbols-outlined text-base align-middle">mail</span>
                A confirmation email has been sent to <strong><?php echo htmlspecialchars($donation['donor_email']); ?></strong>
            </p>
        </div>
    </div>
</div>
</body>
</html>
