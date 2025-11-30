<?php
/**
 * Donation Success Page
 * 
 * The feel-good page! Users land here after completing a donation.
 * Shows a summary of everything they just donated, with confetti
 * animation to celebrate their generosity.
 * 
 * Also provides a "Download Receipt" button and links to view impact.
 */

session_start();

// Must be logged in to see donation confirmation
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Need a valid donation ID to show the receipt
$donation_id = isset($_GET['donation_id']) ? intval($_GET['donation_id']) : 0;

if ($donation_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$user_id = $_SESSION['user_id'];

// Get the transaction timestamp so we can find all items from the same checkout
$primary_donation_query = "SELECT transaction_date, donor_email FROM donations WHERE donation_id = ? AND user_id = ?";
$primary_donation = $db->db_fetch_one($primary_donation_query, [$donation_id, $user_id]);

// If donation doesn't exist or belongs to someone else, redirect
if (!$primary_donation) {
    header("Location: dashboard.php");
    exit();
}

// Get ALL donations from this transaction (user might have donated multiple items at once)
$all_donations_query = "SELECT d.*, s.school_name, s.country, sn.item_name 
                        FROM donations d
                        JOIN schools s ON d.school_id = s.school_id
                        JOIN school_needs sn ON d.need_id = sn.need_id
                        WHERE d.user_id = ? AND d.transaction_date = ?
                        ORDER BY d.donation_id ASC";
$donations = $db->db_fetch_all($all_donations_query, [$user_id, $primary_donation['transaction_date']]);

// Add up the total and get a nice list of schools
$total_amount = 0;
$schools_list = [];
foreach ($donations as $d) {
    $total_amount += $d['amount'];
    if (!in_array($d['school_name'], $schools_list)) {
        $schools_list[] = $d['school_name'];
    }
}

$donor_email = $primary_donation['donor_email'];
// Format schools nicely: "School A, School B and School C"
$schools_text = count($schools_list) > 1 ? implode(', ', array_slice($schools_list, 0, -1)) . ' and ' . end($schools_list) : $schools_list[0];
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
        
        /* Print/Receipt Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-section, #receipt-section * {
                visibility: visible;
            }
            #receipt-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 20px;
            }
            .no-print {
                display: none !important;
            }
        }
        
        #receipt-section {
            display: none;
        }
        
        #receipt-section.show-receipt {
            display: block;
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
                Your generous contribution will make a real difference at <?php echo htmlspecialchars($schools_text); ?>.
            </p>
        </div>
        
        <!-- Donation Details Card -->
        <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-lg border border-neutral-200 dark:border-neutral-800 p-6 mb-6">
            <div class="space-y-4">
                <!-- Transaction ID -->
                <div class="flex justify-between items-center pb-4 border-b border-neutral-200 dark:border-neutral-800">
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">Transaction ID</span>
                    <span class="font-bold text-[#131514] dark:text-white">#<?php echo str_pad($donation_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <!-- Items List -->
                <div class="space-y-3">
                    <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Items Donated</span>
                    <?php foreach ($donations as $donation): ?>
                    <div class="flex justify-between items-start py-2 <?php echo $donation !== end($donations) ? 'border-b border-neutral-100 dark:border-neutral-800' : ''; ?>">
                        <div class="flex-1">
                            <p class="font-semibold text-[#131514] dark:text-white text-sm">
                                <?php echo htmlspecialchars($donation['item_name']); ?> 
                                <span class="text-neutral-500">×<?php echo $donation['quantity']; ?></span>
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                <?php echo htmlspecialchars($donation['school_name']); ?>
                            </p>
                        </div>
                        <span class="font-semibold text-[#131514] dark:text-white text-sm">
                            ₵<?php echo number_format($donation['amount'], 2); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Total -->
                <div class="flex justify-between items-center pt-4 border-t border-neutral-200 dark:border-neutral-800">
                    <span class="text-base font-bold text-[#131514] dark:text-white">Total Amount</span>
                    <span class="text-2xl font-black text-primary">₵<?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="space-y-3">
            <button onclick="downloadReceipt()" 
               class="flex w-full items-center justify-center gap-2 rounded-lg h-12 bg-green-600 text-white text-base font-bold hover:bg-green-700 transition-colors">
                <span class="material-symbols-outlined">download</span>
                Download Receipt
            </button>
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
                A confirmation email has been sent to <strong><?php echo htmlspecialchars($donor_email); ?></strong>
            </p>
        </div>
    </div>
</div>

<!-- Printable Receipt Section (Hidden, shown only for print/download) -->
<div id="receipt-section" class="bg-white p-8 max-w-2xl mx-auto">
    <div class="border-2 border-gray-300 p-8">
        <!-- Receipt Header -->
        <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
            <h1 class="text-3xl font-bold text-green-700 mb-2">GiveToGrow</h1>
            <p class="text-gray-600">Empowering Schools, Transforming Lives</p>
            <p class="text-sm text-gray-500 mt-2">http://169.239.251.102:442/~akua.oduro</p>
        </div>
        
        <!-- Receipt Title -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">DONATION RECEIPT</h2>
            <p class="text-gray-600 mt-1">Official Receipt for Tax Purposes</p>
        </div>
        
        <!-- Receipt Details -->
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p class="text-gray-600">Receipt Number:</p>
                <p class="font-bold text-gray-800">#<?php echo str_pad($donation_id, 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="text-right">
                <p class="text-gray-600">Date:</p>
                <p class="font-bold text-gray-800"><?php echo date('F j, Y', strtotime($primary_donation['transaction_date'])); ?></p>
            </div>
            <div>
                <p class="text-gray-600">Donor Email:</p>
                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($donor_email); ?></p>
            </div>
            <div class="text-right">
                <p class="text-gray-600">Payment Status:</p>
                <p class="font-bold text-green-600">COMPLETED</p>
            </div>
        </div>
        
        <!-- Donation Items Table -->
        <table class="w-full mb-6 text-sm">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-2 text-gray-600">Item</th>
                    <th class="text-left py-2 text-gray-600">School</th>
                    <th class="text-center py-2 text-gray-600">Qty</th>
                    <th class="text-right py-2 text-gray-600">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donations as $donation): ?>
                <tr class="border-b border-gray-200">
                    <td class="py-3 text-gray-800"><?php echo htmlspecialchars($donation['item_name']); ?></td>
                    <td class="py-3 text-gray-600"><?php echo htmlspecialchars($donation['school_name']); ?></td>
                    <td class="py-3 text-center text-gray-800"><?php echo $donation['quantity']; ?></td>
                    <td class="py-3 text-right text-gray-800">₵<?php echo number_format($donation['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-300">
                    <td colspan="3" class="py-3 text-right font-bold text-gray-800">Total Donation:</td>
                    <td class="py-3 text-right font-bold text-xl text-green-700">₵<?php echo number_format($total_amount, 2); ?></td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Thank You Message -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-center">
            <p class="text-green-800 font-semibold">Thank you for your generous donation!</p>
            <p class="text-green-600 text-sm mt-1">Your contribution will help provide essential resources to students in need.</p>
        </div>
        
        <!-- Footer -->
        <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
            <p>This receipt is computer-generated and is valid without signature.</p>
            <p class="mt-1">For any inquiries, please contact: support@givetogrow.org</p>
            <p class="mt-2 font-semibold">GiveToGrow - Making Education Accessible for All</p>
        </div>
    </div>
</div>

<script>
function downloadReceipt() {
    // Show receipt section for printing
    document.getElementById('receipt-section').classList.add('show-receipt');
    
    // Trigger print dialog
    window.print();
    
    // Hide receipt section after print dialog closes
    setTimeout(function() {
        document.getElementById('receipt-section').classList.remove('show-receipt');
    }, 1000);
}
</script>

</body>
</html>
