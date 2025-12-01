<?php
/**
 * Admin Donations Page
 * 
 * Shows all donations made on the platform with donor details,
 * amounts, timestamps, and which schools/needs were supported.
 * Admins can see the full history of who donated what and when.
 */

session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

// Get filter parameters
$filter_school = isset($_GET['school']) ? intval($_GET['school']) : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$query = "SELECT d.*, 
          u.user_name, u.user_email,
          s.school_name,
          sn.item_name
          FROM donations d
          LEFT JOIN users u ON d.user_id = u.user_id
          LEFT JOIN schools s ON d.school_id = s.school_id
          LEFT JOIN school_needs sn ON d.need_id = sn.need_id
          WHERE 1=1";

$params = [];

if ($filter_school > 0) {
    $query .= " AND d.school_id = ?";
    $params[] = $filter_school;
}

if ($filter_status !== '') {
    $query .= " AND d.payment_status = ?";
    $params[] = $filter_status;
}

if ($search !== '') {
    $query .= " AND (u.user_name LIKE ? OR u.user_email LIKE ? OR d.transaction_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY d.created_at DESC";

$donations = $db->db_fetch_all($query, $params);
if (!$donations) $donations = [];

// Get schools for filter dropdown
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools ORDER BY school_name");
if (!$schools) $schools = [];

// Calculate totals
$total_completed = 0;
$total_pending = 0;
foreach ($donations as $d) {
    if ($d['payment_status'] === 'completed') {
        $total_completed += $d['amount'];
    } elseif ($d['payment_status'] === 'pending') {
        $total_pending += $d['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Donations - GiveToGrow Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { "primary": "#A4B8A4" },
                    fontFamily: { "display": ["Lexend", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-gray-50 font-display">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
                <div>
                    <h2 class="text-lg font-bold text-[#131514]">GiveToGrow</h2>
                    <p class="text-xs text-gray-500">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <nav class="flex-1 p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="add_school.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">school</span>
                        Add School
                    </a>
                </li>
                <li>
                    <a href="manage_schools.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">list</span>
                        Manage Schools
                    </a>
                </li>
                <li>
                    <a href="add_need.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">add_circle</span>
                        Add School Need
                    </a>
                </li>
                <li>
                    <a href="donations.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                        View Donations
                    </a>
                </li>
                <li>
                    <a href="post_update.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">campaign</span>
                        Post Update
                    </a>
                </li>
                <li>
                    <a href="../views/schools.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">public</span>
                        View Public Site
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-600">Welcome, <strong><?php echo $admin_name; ?></strong></span>
            </div>
            <a href="../actions/logout.php" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                <span class="material-symbols-outlined text-xl">logout</span>
                Logout
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Donations</h1>
                <p class="text-gray-600 mt-1">View all donations and donor information</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Total Donations</p>
                            <p class="text-3xl font-bold text-gray-800"><?php echo count($donations); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">receipt_long</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Completed Amount</p>
                            <p class="text-3xl font-bold text-green-600">₵<?php echo number_format($total_completed, 2); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Pending Amount</p>
                            <p class="text-3xl font-bold text-yellow-600">₵<?php echo number_format($total_pending, 2); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined text-yellow-600 text-2xl">pending</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by name, email, or transaction ID..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"/>
                    </div>
                    
                    <div class="w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-2">School</label>
                        <select name="school" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Schools</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?php echo $school['school_id']; ?>" <?php echo $filter_school == $school['school_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($school['school_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="completed" <?php echo $filter_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="failed" <?php echo $filter_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg font-medium hover:opacity-90">
                        Filter
                    </button>
                    
                    <a href="donations.php" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300">
                        Reset
                    </a>
                </form>
            </div>
            
            <!-- Donations Table -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donor</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School / Item</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($donations)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">inbox</span>
                                    No donations found
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($donations as $donation): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-800">
                                            <?php echo $donation['is_anonymous'] ? 'Anonymous' : htmlspecialchars($donation['user_name'] ?? 'Unknown'); ?>
                                        </p>
                                        <?php if (!$donation['is_anonymous'] && $donation['user_email']): ?>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($donation['user_email']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($donation['school_name'] ?? 'Unknown School'); ?></p>
                                    <?php if ($donation['item_name']): ?>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($donation['item_name']); ?> (×<?php echo $donation['quantity'] ?? 1; ?>)</p>
                                    <?php else: ?>
                                    <p class="text-sm text-gray-500">General Donation</p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">₵<?php echo number_format($donation['amount'], 2); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_classes = [
                                        'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $status_class = $status_classes[$donation['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full <?php echo $status_class; ?>">
                                        <?php echo ucfirst($donation['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-800"><?php echo date('M d, Y', strtotime($donation['created_at'])); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo date('h:i A', strtotime($donation['created_at'])); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 font-mono">
                                        <?php echo $donation['transaction_id'] ? htmlspecialchars(substr($donation['transaction_id'], 0, 20)) . '...' : '-'; ?>
                                    </p>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
