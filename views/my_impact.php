<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';
$db = new db_connection();

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'User');

// Fetch total amount donated by this user
$total_query = "
    SELECT COALESCE(SUM(amount), 0) as total_donated
    FROM donations
    WHERE user_id = ? AND payment_status = 'completed'
";
$total_result = $db->db_fetch_one($total_query, [$user_id]);
$total_donated = $total_result['total_donated'] ?? 0;

// Fetch number of schools supported
$schools_query = "
    SELECT COUNT(DISTINCT school_id) as schools_count
    FROM donations
    WHERE user_id = ? AND payment_status = 'completed'
";
$schools_result = $db->db_fetch_one($schools_query, [$user_id]);
$schools_supported = $schools_result['schools_count'] ?? 0;

// Fetch number of items funded
$items_query = "
    SELECT COUNT(*) as items_count
    FROM donations
    WHERE user_id = ? AND payment_status = 'completed' AND need_id IS NOT NULL
";
$items_result = $db->db_fetch_one($items_query, [$user_id]);
$items_funded = $items_result['items_count'] ?? 0;

// Fetch monthly donation data for chart (last 7 months)
$months_data = [];
for ($i = 6; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_label = date('M', strtotime("-$i months"));
    
    $month_query = "
        SELECT COALESCE(SUM(amount), 0) as month_total
        FROM donations
        WHERE user_id = ? 
        AND payment_status = 'completed'
        AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ";
    $month_result = $db->db_fetch_one($month_query, [$user_id, $month]);
    $months_data[] = [
        'label' => $month_label,
        'amount' => $month_result['month_total'] ?? 0
    ];
}

// Calculate max for chart scaling
$max_amount = max(array_column($months_data, 'amount'));
$max_amount = $max_amount > 0 ? $max_amount : 100;

// Calculate percentage increase (compare last month to previous month)
$last_month_amount = $months_data[6]['amount'] ?? 0;
$prev_month_amount = $months_data[5]['amount'] ?? 0;
$percentage_change = 0;
if ($prev_month_amount > 0) {
    $percentage_change = round((($last_month_amount - $prev_month_amount) / $prev_month_amount) * 100);
}

// Fetch donation history
$history_query = "
    SELECT 
        d.donation_id,
        d.amount,
        d.created_at,
        d.payment_status,
        d.school_id,
        s.school_name,
        GROUP_CONCAT(DISTINCT sn.item_name SEPARATOR ', ') as items
    FROM donations d
    LEFT JOIN schools s ON d.school_id = s.school_id
    LEFT JOIN school_needs sn ON d.need_id = sn.need_id
    WHERE d.user_id = ?
    GROUP BY d.donation_id
    ORDER BY d.created_at DESC
    LIMIT 20
";
$donation_history = $db->db_fetch_all($history_query, [$user_id]);
if (!$donation_history) {
    $donation_history = [];
}

// Fetch recent updates from schools the user has donated to
$recent_updates = [];
$updates_query = "
    SELECT su.*, s.school_name, s.image_url as school_image
    FROM school_updates su
    JOIN schools s ON su.school_id = s.school_id
    WHERE su.school_id IN (
        SELECT DISTINCT school_id 
        FROM donations 
        WHERE user_id = ? AND payment_status = 'completed'
    )
    AND su.is_published = 1
    ORDER BY su.created_at DESC
    LIMIT 5
";
$result = $db->db_fetch_all($updates_query, [$user_id]);
if ($result) {
    $recent_updates = $result;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Your Impact Dashboard - GiveToGrow</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24
        }
    </style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#333333] dark:text-background-light">
    <div class="relative flex min-h-screen w-full">
        <!-- SideNavBar -->
        <aside class="sticky top-0 h-screen w-64 flex-shrink-0 bg-white dark:bg-background-dark/50 border-r border-black/10 dark:border-white/10 p-4">
            <div class="flex h-full flex-col justify-between">
                <div class="flex flex-col gap-8">
                    <div class="flex items-center gap-2 px-2">
                        <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
                        <h1 class="text-[#131514] dark:text-background-light text-xl font-bold">GiveToGrow</h1>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="flex gap-3 px-3 py-2 items-center">
                            <div class="bg-primary/30 flex items-center justify-center rounded-full size-10">
                                <span class="material-symbols-outlined text-primary">person</span>
                            </div>
                            <div class="flex flex-col">
                                <h2 class="text-[#131514] dark:text-background-light text-base font-medium leading-normal"><?php echo $user_name; ?></h2>
                                <p class="text-[#60826b] dark:text-primary/80 text-sm font-normal leading-normal">GiveToGrow Member</p>
                            </div>
                        </div>
                        <nav class="flex flex-col gap-2 mt-4">
                            <a class="flex items-center gap-3 px-3 py-2 text-[#131514] dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg" href="dashboard.php">
                                <span class="material-symbols-outlined">dashboard</span>
                                <p class="text-sm font-medium leading-normal">Back Home</p>
                            </a>
                            <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 dark:bg-primary/20 text-[#131514] dark:text-white" href="my_impact.php">
                                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">favorite</span>
                                <p class="text-sm font-medium leading-normal">Your Impact</p>
                            </a>
                            <a class="flex items-center gap-3 px-3 py-2 text-[#131514] dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg" href="schools.php">
                                <span class="material-symbols-outlined">search</span>
                                <p class="text-sm font-medium leading-normal">Find a School</p>
                            </a>
                        </nav>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <a href="schools.php" class="flex min-w-[84px] cursor-pointer items-center justify-center overflow-hidden rounded-xl h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em] hover:bg-primary/90">
                        <span class="truncate">Make a New Donation</span>
                    </a>
                    <div class="flex flex-col gap-1 border-t border-black/10 dark:border-white/10 pt-2">
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a class="flex items-center gap-3 px-3 py-2 text-[#131514] dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg" href="../admin/index.php">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            <p class="text-sm font-medium leading-normal">Admin Panel</p>
                        </a>
                        <?php endif; ?>
                        <a class="flex items-center gap-3 px-3 py-2 text-[#131514] dark:text-gray-300 hover:bg-primary/10 dark:hover:bg-primary/20 rounded-lg" href="../actions/logout.php">
                            <span class="material-symbols-outlined">logout</span>
                            <p class="text-sm font-medium leading-normal">Log Out</p>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 lg:p-10">
            <div class="mx-auto max-w-7xl">
                <!-- PageHeading -->
                <div class="flex flex-wrap justify-between gap-3 mb-6">
                    <div class="flex min-w-72 flex-col gap-2">
                        <p class="text-[#131514] dark:text-background-light text-4xl font-black leading-tight tracking-[-0.033em]">Your Impact Dashboard</p>
                        <p class="text-[#60826b] dark:text-primary/80 text-base font-normal leading-normal">See the incredible difference you're making.</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white dark:bg-background-dark/50 border border-black/10 dark:border-white/10">
                        <p class="text-[#60826b] dark:text-primary/80 text-base font-medium leading-normal">Total Amount Donated</p>
                        <p class="text-[#131514] dark:text-background-light tracking-light text-3xl font-bold leading-tight">₵<?php echo number_format($total_donated, 0); ?></p>
                    </div>
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white dark:bg-background-dark/50 border border-black/10 dark:border-white/10">
                        <p class="text-[#60826b] dark:text-primary/80 text-base font-medium leading-normal">Schools Supported</p>
                        <p class="text-[#131514] dark:text-background-light tracking-light text-3xl font-bold leading-tight"><?php echo $schools_supported; ?></p>
                    </div>
                    <div class="flex min-w-[158px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white dark:bg-background-dark/50 border border-black/10 dark:border-white/10">
                        <p class="text-[#60826b] dark:text-primary/80 text-base font-medium leading-normal">Items Funded</p>
                        <p class="text-[#131514] dark:text-background-light tracking-light text-3xl font-bold leading-tight"><?php echo $items_funded; ?></p>
                    </div>
                </div>

                <!-- Charts -->
                <div class="flex flex-wrap gap-6 mb-6">
                    <div class="flex min-w-72 flex-1 flex-col gap-4 rounded-xl border border-black/10 dark:border-white/10 p-6 bg-white dark:bg-background-dark/50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[#131514] dark:text-background-light text-lg font-bold leading-normal">Your Donations by Month</p>
                                <p class="text-[#60826b] dark:text-primary/80 text-sm font-normal leading-normal">Last 7 Months</p>
                            </div>
                            <?php if ($percentage_change != 0): ?>
                            <div class="flex gap-1 items-center">
                                <p class="<?php echo $percentage_change > 0 ? 'text-green-600' : 'text-red-600'; ?> text-base font-medium leading-normal">
                                    <?php echo $percentage_change > 0 ? '+' : ''; ?><?php echo $percentage_change; ?>%
                                </p>
                                <span class="material-symbols-outlined <?php echo $percentage_change > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $percentage_change > 0 ? 'trending_up' : 'trending_down'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="grid min-h-[220px] grid-flow-col gap-6 grid-rows-[1fr_auto] items-end justify-items-center px-3 pt-4">
                            <?php foreach ($months_data as $month): 
                                $height = $max_amount > 0 ? round(($month['amount'] / $max_amount) * 100) : 0;
                                $height = max($height, 5); // Minimum 5% height for visibility
                            ?>
                            <div class="bg-primary/40 dark:bg-primary/30 w-full rounded-t-md" style="height: <?php echo $height; ?>%;"></div>
                            <p class="text-[#60826b] dark:text-gray-400 text-[13px] font-bold leading-normal tracking-[0.015em]"><?php echo $month['label']; ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- SectionHeader for Donation History -->
                <h2 class="text-[#131514] dark:text-background-light text-[22px] font-bold leading-tight tracking-[-0.015em] px-1 pb-3 pt-5">Your Complete Donation History</h2>

                <!-- Donation History Table -->
                <?php if (empty($donation_history)): ?>
                <div class="rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-background-dark/50 p-12 text-center">
                    <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">history</span>
                    <h3 class="text-xl font-bold text-[#131514] dark:text-background-light mb-2">No Donations Yet</h3>
                    <p class="text-[#60826b] dark:text-gray-400 mb-6">Start making a difference today!</p>
                    <a href="schools.php" class="inline-block bg-primary text-white px-6 py-3 rounded-xl font-bold hover:bg-primary/90">
                        Browse Schools
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-background-dark/50">
                    <table class="w-full text-sm text-left text-[#131514] dark:text-gray-300">
                        <thead class="text-xs text-[#60826b] dark:text-primary/80 uppercase bg-background-light dark:bg-background-dark/60">
                            <tr>
                                <th class="px-6 py-3" scope="col">Date</th>
                                <th class="px-6 py-3" scope="col">School Name</th>
                                <th class="px-6 py-3" scope="col">Amount</th>
                                <th class="px-6 py-3" scope="col">Items</th>
                                <th class="px-6 py-3" scope="col">Status</th>
                                <th class="px-6 py-3" scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donation_history as $donation): 
                                $status_class = $donation['payment_status'] === 'completed' ? 'green' : 'yellow';
                                $status_text = $donation['payment_status'] === 'completed' ? 'Completed' : 'In Progress';
                            ?>
                            <tr class="border-b border-black/10 dark:border-white/10">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('Y-m-d', strtotime($donation['created_at'])); ?></td>
                                <td class="px-6 py-4 font-medium text-[#131514] dark:text-background-light"><?php echo htmlspecialchars($donation['school_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4">₵<?php echo number_format($donation['amount'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($donation['items'] ?? 'General Donation'); ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center bg-<?php echo $status_class; ?>-100 dark:bg-<?php echo $status_class; ?>-900/50 text-<?php echo $status_class; ?>-800 dark:text-<?php echo $status_class; ?>-300 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                        <span class="w-2 h-2 me-1 bg-<?php echo $status_class; ?>-500 rounded-full"></span>
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex gap-2 justify-end">
                                    <?php if ($donation['school_id']): ?>
                                    <a href="school_detail.php?id=<?php echo $donation['school_id']; ?>" class="p-2 rounded-md hover:bg-primary/10 dark:hover:bg-primary/20">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- School Updates Section -->
                <div class="flex justify-between items-center px-1 pb-3 pt-8">
                    <h2 class="text-[#131514] dark:text-background-light text-[22px] font-bold leading-tight tracking-[-0.015em]">Updates From Schools You Support</h2>
                    <?php if (!empty($recent_updates)): ?>
                    <a href="my_updates.php" class="text-primary font-medium text-sm hover:underline">View All Updates →</a>
                    <?php endif; ?>
                </div>

                <?php if (empty($recent_updates)): ?>
                <div class="rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-background-dark/50 p-12 text-center">
                    <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">campaign</span>
                    <h3 class="text-xl font-bold text-[#131514] dark:text-background-light mb-2">No Updates Yet</h3>
                    <p class="text-[#60826b] dark:text-gray-400 mb-2">Schools you support will post updates and photos here.</p>
                    <p class="text-[#60826b] dark:text-gray-400 text-sm">Check back soon to see how your donations are making a difference!</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php 
                    $update_types = [
                        'general' => ['icon' => 'campaign', 'color' => 'bg-blue-100 text-blue-700'],
                        'milestone' => ['icon' => 'emoji_events', 'color' => 'bg-yellow-100 text-yellow-700'],
                        'progress' => ['icon' => 'trending_up', 'color' => 'bg-green-100 text-green-700'],
                        'completion' => ['icon' => 'check_circle', 'color' => 'bg-purple-100 text-purple-700'],
                        'thank_you' => ['icon' => 'favorite', 'color' => 'bg-pink-100 text-pink-700']
                    ];
                    
                    foreach ($recent_updates as $update): 
                        $type_info = $update_types[$update['update_type'] ?? 'general'] ?? $update_types['general'];
                    ?>
                    <div class="rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-background-dark/50 overflow-hidden hover:shadow-lg transition-shadow">
                        <?php if (!empty($update['image_url'])): ?>
                        <div class="h-48 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($update['image_url']); ?>');"></div>
                        <?php endif; ?>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <span class="<?php echo $type_info['color']; ?> px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1">
                                    <span class="material-symbols-outlined" style="font-size: 14px;"><?php echo $type_info['icon']; ?></span>
                                    <?php echo ucfirst(str_replace('_', ' ', $update['update_type'] ?? 'Update')); ?>
                                </span>
                                <span class="text-xs text-gray-500"><?php echo date('M j, Y', strtotime($update['created_at'])); ?></span>
                            </div>
                            <h4 class="font-bold text-[#131514] dark:text-background-light mb-1"><?php echo htmlspecialchars($update['update_title']); ?></h4>
                            <p class="text-sm text-primary font-medium mb-2"><?php echo htmlspecialchars($update['school_name']); ?></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                <?php echo htmlspecialchars(substr($update['update_description'], 0, 120)); ?>...
                            </p>
                            <a href="school_updates.php?school_id=<?php echo $update['school_id']; ?>" class="inline-flex items-center gap-1 text-primary text-sm font-medium mt-3 hover:underline">
                                Read More <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
