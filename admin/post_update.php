<?php
/**
 * Post School Update - Admin Page
 * 
 * Admins use this page to share progress updates with donors.
 * When something good happens at a school (textbooks delivered,
 * new equipment installed, milestone reached), we post an update here.
 * 
 * These updates show up on:
 * - The school's detail page
 * - The donor's "My Updates" page (if they donated to that school)
 * - Can trigger email notifications to donors
 * 
 * Update types:
 * - general: Regular news
 * - milestone: Something big achieved
 * - progress: Status update on ongoing work
 * - completion: Project finished!
 * - thank_you: Gratitude message to donors
 */

session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

// Get active schools for the dropdown - no point showing inactive ones
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools WHERE status = 'active' ORDER BY school_name");

// Handle the form when admin submits a new update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = intval($_POST['school_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $update_type = $_POST['update_type'];
    $image_url = trim($_POST['image_url'] ?? '');
    
    if ($school_id && $title && $description) {
        $conn = $db->db_conn();
        
        $insert_query = "
            INSERT INTO school_updates (school_id, update_title, update_description, update_type, image_url, is_published)
            VALUES (?, ?, ?, ?, ?, 1)
        ";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('issss', $school_id, $title, $description, $update_type, $image_url);
        
        if ($stmt->execute()) {
            $success_message = "Update posted successfully!";
            $stmt->close();
        } else {
            $error_message = "Failed to post update: " . $stmt->error;
            $stmt->close();
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch recent updates
$recent_updates = $db->db_fetch_all("
    SELECT su.*, s.school_name
    FROM school_updates su
    JOIN schools s ON su.school_id = s.school_id
    ORDER BY su.created_at DESC
    LIMIT 10
");
if (!$recent_updates) {
    $recent_updates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post School Update - Admin</title>
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
                    <a href="manage_needs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100">
                        <span class="material-symbols-outlined">inventory_2</span>
                        Manage Needs
                    </a>
                </li>
                <li>
                    <a href="post_update.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
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
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Post School Update</h1>
                <p class="text-gray-600 mt-1">Share progress and updates with donors</p>
            </div>
            
            <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Post Update Form -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6 mb-8">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">School *</label>
                        <select name="school_id" id="school_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Select a school</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?php echo $school['school_id']; ?>"><?php echo htmlspecialchars($school['school_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Type *</label>
                        <select name="update_type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="general">General Update</option>
                            <option value="milestone">Milestone Achieved</option>
                            <option value="progress">Progress Report</option>
                            <option value="completion">Project Completed</option>
                            <option value="thank_you">Thank You Message</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Title *</label>
                        <input type="text" name="title" required maxlength="255" 
                               placeholder="e.g., Textbooks Delivered Successfully!"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"/>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Description *</label>
                        <textarea name="description" required rows="6"
                                  placeholder="Provide details about the update, progress made, or thank donors for their contribution..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image URL (Optional)</label>
                        <input type="url" name="image_url" id="image_url"
                               placeholder="https://example.com/image.jpg"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"/>
                        <p class="text-xs text-gray-500 mt-1">Provide a URL to an image showing the progress or impact</p>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:opacity-90 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">send</span>
                        Post Update
                    </button>
                </form>
            </div>
            
            <!-- Recent Updates -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 p-6">
                <h2 class="text-xl font-bold mb-4">Recent Updates</h2>
                <?php if (empty($recent_updates)): ?>
                <p class="text-gray-500 text-center py-8">No updates posted yet.</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recent_updates as $update): ?>
                    <div class="border-l-4 border-primary pl-4 py-2">
                        <h3 class="font-bold text-gray-800"><?php echo htmlspecialchars($update['update_title']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($update['school_name']); ?> · <?php echo date('M d, Y', strtotime($update['created_at'])); ?></p>
                        <span class="inline-block mt-1 text-xs bg-primary/20 text-primary px-2 py-1 rounded-full"><?php echo ucfirst(str_replace('_', ' ', $update['update_type'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
