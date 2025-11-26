<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.php");
    exit();
}

require_once __DIR__ . '/settings/db_class.php';
$db = new db_connection();

$user_id = $_SESSION['user_id'];
$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

// Fetch school information
$school_query = "SELECT * FROM schools WHERE school_id = ?";
$school = $db->db_fetch_one($school_query, [$school_id]);

if (!$school) {
    header("Location: schools.php");
    exit();
}

// Fetch all updates for this school
$updates_query = "
    SELECT su.*, u.user_name as author_name
    FROM school_updates su
    LEFT JOIN users u ON su.created_by = u.user_id
    WHERE su.school_id = ? AND su.is_published = 1
    ORDER BY su.created_at DESC
";
$updates = $db->db_fetch_all($updates_query, [$school_id]);

// Fetch impact metrics
$metrics_query = "
    SELECT * FROM impact_metrics
    WHERE school_id = ?
    ORDER BY measurement_date DESC
";
$metrics = $db->db_fetch_all($metrics_query, [$school_id]);

// Check if user has donated to this school (for subscription)
$donation_check = "
    SELECT COUNT(*) as has_donated
    FROM donations
    WHERE user_id = ? AND school_id = ? AND payment_status = 'completed'
";
$donation_result = $db->db_fetch_one($donation_check, [$user_id, $school_id]);
$has_donated = $donation_result && $donation_result['has_donated'] > 0;

// Mark updates as read for this user
if (!empty($updates)) {
    foreach ($updates as $update) {
        $mark_read = "
            INSERT INTO update_notifications (update_id, user_id, is_read, read_at)
            VALUES (?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()
        ";
        $db->db_query($mark_read, [$update['update_id'], $user_id]);
    }
}

$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($school['school_name']); ?> - Updates & Impact</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#A4B8A4",
                        "background-light": "#f7f7f7"
                    },
                    fontFamily: {
                        "display": ["Lexend", "sans-serif"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background-light font-display">
<div class="min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
                <h2 class="text-lg font-bold">GiveToGrow</h2>
            </div>
            <div class="flex items-center gap-4">
                <a href="schools.php" class="text-sm font-medium hover:text-primary">Back to Schools</a>
                <span class="text-sm">Welcome, <strong><?php echo $user_name; ?></strong></span>
                <a href="actions/logout.php" class="px-4 py-2 bg-primary text-white rounded-full text-sm font-bold hover:opacity-90">Log Out</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- School Header -->
        <div class="bg-white rounded-xl p-6 mb-8 shadow-sm">
            <div class="flex items-start gap-6">
                <img src="<?php echo htmlspecialchars($school['image_url']); ?>" alt="<?php echo htmlspecialchars($school['school_name']); ?>" class="w-32 h-32 object-cover rounded-lg"/>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($school['school_name']); ?></h1>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($school['location']); ?>, <?php echo htmlspecialchars($school['country']); ?></p>
                    <?php if ($has_donated): ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary/20 text-primary rounded-full text-sm font-bold">
                            <span class="material-symbols-outlined text-lg">favorite</span>
                            You're supporting this school!
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Impact Metrics -->
        <?php if (!empty($metrics)): ?>
        <div class="bg-white rounded-xl p-6 mb-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">insights</span>
                Impact Metrics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($metrics as $metric): ?>
                <div class="bg-primary/10 rounded-lg p-4">
                    <p class="text-3xl font-black text-primary mb-2">
                        <?php echo number_format($metric['metric_value'], 0); ?><?php echo $metric['metric_unit'] === 'percent' ? '%' : ''; ?>
                    </p>
                    <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($metric['metric_label']); ?></p>
                    <?php if ($metric['measurement_date']): ?>
                    <p class="text-xs text-gray-500 mt-2">As of <?php echo date('M d, Y', strtotime($metric['measurement_date'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Updates Timeline -->
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">update</span>
                Progress Updates
            </h2>
            
            <?php if (empty($updates)): ?>
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">pending_actions</span>
                <p class="text-gray-500">No updates yet. Check back soon for progress reports!</p>
            </div>
            <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($updates as $update): ?>
                <div class="border-l-4 border-primary pl-6 pb-6 relative">
                    <div class="absolute -left-2 top-0 w-4 h-4 bg-primary rounded-full"></div>
                    
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($update['update_title']); ?></h3>
                            <p class="text-sm text-gray-500">
                                <?php echo date('F d, Y', strtotime($update['created_at'])); ?>
                                <?php if ($update['author_name']): ?>
                                · Posted by <?php echo htmlspecialchars($update['author_name']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php
                        $badge_colors = [
                            'milestone' => 'bg-green-100 text-green-800',
                            'progress' => 'bg-blue-100 text-blue-800',
                            'completion' => 'bg-purple-100 text-purple-800',
                            'thank_you' => 'bg-pink-100 text-pink-800',
                            'general' => 'bg-gray-100 text-gray-800'
                        ];
                        $badge_color = $badge_colors[$update['update_type']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-3 py-1 <?php echo $badge_color; ?> rounded-full text-xs font-bold uppercase">
                            <?php echo htmlspecialchars($update['update_type']); ?>
                        </span>
                    </div>
                    
                    <?php if ($update['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($update['image_url']); ?>" alt="Update image" class="w-full h-64 object-cover rounded-lg mb-4"/>
                    <?php endif; ?>
                    
                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($update['update_description'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- CTA for non-donors -->
        <?php if (!$has_donated): ?>
        <div class="mt-8 bg-primary/20 rounded-xl p-8 text-center">
            <h3 class="text-2xl font-bold mb-4">Want to see your impact here?</h3>
            <p class="text-gray-700 mb-6">Support <?php echo htmlspecialchars($school['school_name']); ?> and receive regular updates on how your contribution is making a difference.</p>
            <a href="school_detail.php?id=<?php echo $school_id; ?>" class="inline-block px-6 py-3 bg-primary text-white rounded-full font-bold hover:opacity-90">
                View Projects & Donate
            </a>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
