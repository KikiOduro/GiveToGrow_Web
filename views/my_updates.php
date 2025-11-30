<?php
/**
 * My Impact Updates Page
 * 
 * Shows donors the updates from schools they've supported. This is where
 * the magic happens - donors can see the real-world impact of their
 * contributions through photos, milestone announcements, and progress reports.
 * 
 * Only shows updates from schools where the user has completed donations.
 * Think of it as a personalized news feed of good news from their schools.
 * 
 * Update types shown:
 * - General: Regular news and announcements
 * - Milestone: Big achievements (like reaching funding goals)
 * - Progress: Work in progress updates
 * - Completion: Projects finished!
 * - Thank You: Gratitude messages from schools
 */

session_start();

// Must be logged in to see your updates
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';
$db = new db_connection();

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'User');

// Start with empty updates array
$updates = [];

// Get updates from schools this user has donated to
// Uses a subquery to find schools where they have completed donations
try {
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
    ";
    $result = $db->db_fetch_all($updates_query, [$user_id]);
    if ($result) {
        $updates = $result;
    }
} catch (Exception $e) {
    // Table might not exist yet or query failed - just show empty state
    $updates = [];
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>My Impact Updates - GiveToGrow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: { "primary": "#A4B8A4" },
                    fontFamily: { "display": ["Lexend", "sans-serif"] }
                }
            }
        }
        
        // function initTheme() {
        //     const theme = localStorage.getItem('theme') || 'light';
        //     document.documentElement.classList.toggle('dark', theme === 'dark');
        // }
        
        // function toggleTheme() {
        //     const html = document.documentElement;
        //     const isDark = html.classList.contains('dark');
        //     html.classList.toggle('dark');
        //     localStorage.setItem('theme', isDark ? 'light' : 'dark');
        // }
        
        // initTheme();
    </script>
</head>
<body class="bg-gray-50 dark:bg-neutral-900 font-display">
    <!-- Header -->
    <div class="sticky top-0 z-20 bg-white dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="p-2 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-full">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">My Impact Updates</h1>
            </div>
            <button onclick="toggleTheme()" class="p-2 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-full">
                <span class="material-symbols-outlined dark:hidden">dark_mode</span>
                <span class="material-symbols-outlined hidden dark:inline">light_mode</span>
            </button>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 py-8">
        <?php if (empty($updates)): ?>
        <div class="text-center py-20">
            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-neutral-600 mb-4">notifications_off</span>
            <h2 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 mb-2">No Updates Yet</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                You'll see impact updates here from schools you've supported.
            </p>
            <a href="schools.php" class="inline-block bg-primary text-white px-6 py-3 rounded-full font-bold hover:opacity-90">
                Explore Schools
            </a>
        </div>
        <?php else: ?>
        
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-gray-200 dark:border-neutral-700">
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Total Updates</p>
                <p class="text-3xl font-bold text-neutral-800 dark:text-neutral-100"><?php echo count($updates); ?></p>
            </div>
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 border border-gray-200 dark:border-neutral-700">
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-1">Schools Followed</p>
                <p class="text-3xl font-bold text-neutral-800 dark:text-neutral-100">
                    <?php echo count(array_unique(array_column($updates, 'school_id'))); ?>
                </p>
            </div>
        </div>
        
        <!-- Updates Timeline -->
        <div class="space-y-6">
            <?php 
            $update_types = [
                'general' => ['icon' => 'campaign', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                'milestone' => ['icon' => 'emoji_events', 'color' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'],
                'progress' => ['icon' => 'trending_up', 'color' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
                'completion' => ['icon' => 'check_circle', 'color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                'thank_you' => ['icon' => 'favorite', 'color' => 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400']
            ];
            
            foreach ($updates as $update): 
                $type_info = $update_types[$update['update_type']] ?? $update_types['general'];
            ?>
            <a href="school_updates.php?school_id=<?php echo $update['school_id']; ?>" 
               class="block bg-white dark:bg-neutral-800 rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700 hover:shadow-lg transition-shadow cursor-pointer">
                <?php if ($update['image_url']): ?>
                <div class="h-64 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($update['image_url']); ?>');"></div>
                <?php endif; ?>
                
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="<?php echo $type_info['color']; ?> px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 16px;"><?php echo $type_info['icon']; ?></span>
                                <?php echo ucfirst(str_replace('_', ' ', $update['update_type'])); ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <?php echo date('F j, Y', strtotime($update['created_at'])); ?>
                        </p>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 mb-2">
                        <?php echo htmlspecialchars($update['update_title']); ?>
                    </h3>
                    
                    <p class="text-sm text-primary dark:text-primary font-medium mb-4">
                        <?php echo htmlspecialchars($update['school_name']); ?>
                    </p>
                    
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                        <?php echo nl2br(htmlspecialchars(substr($update['update_description'], 0, 150))); ?><?php echo strlen($update['update_description']) > 150 ? '...' : ''; ?>
                    </p>
                    
                    <span class="inline-flex items-center gap-2 text-primary font-medium">
                        View Full Impact Report
                        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_forward</span>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
