<?php
session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();

// Fetch all schools for dropdown
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools WHERE status = 'active' ORDER BY school_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id = intval($_POST['school_id']);
    $need_id = !empty($_POST['need_id']) ? intval($_POST['need_id']) : null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $update_type = $_POST['update_type'];
    $image_url = trim($_POST['image_url']);
    $created_by = $_SESSION['user_id'];
    
    if ($school_id && $title && $description) {
        $insert_query = "
            INSERT INTO school_updates (school_id, need_id, update_title, update_description, update_type, image_url, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        if ($db->db_query($insert_query, [$school_id, $need_id, $title, $description, $update_type, $image_url, $created_by])) {
            $_SESSION['success_message'] = "Update posted successfully!";
            
            // Create notifications for donors
            $update_id = $db->last_insert_id();
            $notify_query = "
                INSERT INTO update_notifications (update_id, user_id, is_read)
                SELECT ?, DISTINCT user_id, 0
                FROM donations
                WHERE school_id = ? AND payment_status = 'completed'
            ";
            $db->db_query($notify_query, [$update_id, $school_id]);
            
            // Send email notifications (optional - comment out if not using email)
            if (isset($_POST['send_email']) && $_POST['send_email'] === '1') {
                require_once __DIR__ . '/../actions/send_update_notifications.php';
                $email_count = sendUpdateNotifications($update_id);
                $_SESSION['success_message'] .= " Email notifications sent to {$email_count} donors.";
            }
            
            header("Location: post_update.php");
            exit();
        } else {
            $error_message = "Failed to post update. Please try again.";
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
        
        function loadNeeds() {
            const schoolId = document.getElementById('school_id').value;
            const needSelect = document.getElementById('need_id');
            
            if (!schoolId) {
                needSelect.innerHTML = '<option value="">Select school first</option>';
                return;
            }
            
            // This would normally be an AJAX call, but for simplicity we'll leave it as optional
            needSelect.innerHTML = '<option value="">General update (not specific to a need)</option>';
        }
    </script>
</head>
<body class="bg-gray-50 font-display">
    <?php include 'includes/admin_sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Post School Update</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Post Update Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">School *</label>
                        <select name="school_id" id="school_id" required onchange="loadNeeds()" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">Select a school</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?php echo $school['school_id']; ?>"><?php echo htmlspecialchars($school['school_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Related Need (Optional)</label>
                        <select name="need_id" id="need_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="">General update (not specific to a need)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Update Type *</label>
                        <select name="update_type" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="general">General Update</option>
                            <option value="milestone">Milestone Achieved</option>
                            <option value="progress">Progress Report</option>
                            <option value="completion">Project Completed</option>
                            <option value="thank_you">Thank You Message</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Update Title *</label>
                        <input type="text" name="title" required maxlength="255" 
                               placeholder="e.g., Textbooks Delivered Successfully!"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"/>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Update Description *</label>
                        <textarea name="description" required rows="6"
                                  placeholder="Provide details about the update, progress made, or thank donors for their contribution..."
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Image URL (Optional)</label>
                        <input type="url" name="image_url" 
                               placeholder="https://example.com/image.jpg"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"/>
                        <p class="text-xs text-gray-500 mt-1">Provide a URL to an image showing the progress or impact</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="send_email" value="1" id="send_email" checked
                               class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"/>
                        <label for="send_email" class="text-sm font-medium">
                            Send email notifications to donors
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:opacity-90">
                        Post Update
                    </button>
                </form>
            </div>
            
            <!-- Recent Updates -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Recent Updates</h2>
                <?php if (empty($recent_updates)): ?>
                <p class="text-gray-500 text-center py-8">No updates posted yet.</p>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recent_updates as $update): ?>
                    <div class="border-l-4 border-primary pl-4 py-2">
                        <h3 class="font-bold"><?php echo htmlspecialchars($update['update_title']); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($update['school_name']); ?> · <?php echo date('M d, Y', strtotime($update['created_at'])); ?></p>
                        <span class="text-xs bg-primary/20 text-primary px-2 py-1 rounded-full"><?php echo $update['update_type']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
