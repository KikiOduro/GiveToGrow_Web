<?php
/**
 * Admin Panel: Add School Need
 * 
 * This page allows administrators to add new items/needs for schools.
 * Each need represents something a school requires - like textbooks,
 * computers, sports equipment, etc. - that donors can contribute towards.
 * 
 * The form collects item details including name, description, category,
 * price per unit, quantity needed, and an image URL.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

// Check for any success/error messages from previous actions
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// If coming from add_school, pre-select that school in the dropdown
$preselected_school = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

// Get all schools for the dropdown menu
$schools = $db->db_fetch_all("SELECT school_id, school_name, location FROM schools ORDER BY school_name ASC");
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Add School Need – GiveToGrow Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#333333] dark:text-background-light font-display min-h-screen overflow-auto">
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <img src="../assets/logo.png" alt="GiveToGrow Logo" class="h-8 w-auto"/>
                <div>
                    <h2 class="text-lg font-bold text-[#131514] dark:text-background-light">GiveToGrow</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <nav class="flex-1 p-4">
            <ul class="space-y-2">
                <li>
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="add_school.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">school</span>
                        Add School
                    </a>
                </li>
                <li>
                    <a href="manage_schools.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">list</span>
                        Manage Schools
                    </a>
                </li>
                <li>
                    <a href="add_need.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <span class="material-symbols-outlined">add_circle</span>
                        Add School Need
                    </a>
                </li>
                <li>
                    <a href="manage_needs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">inventory_2</span>
                        Manage Needs
                    </a>
                </li>
                <li>
                    <a href="donations.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                        View Donations
                    </a>
                </li>
                <li>
                    <a href="post_update.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">campaign</span>
                        Post Update
                    </a>
                </li>
                <li>
                    <a href="../views/schools.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">public</span>
                        View Public Site
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
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
    <main class="flex-1 overflow-auto">
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-8 py-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="dashboard.php" class="text-gray-600 dark:text-gray-400 hover:text-primary">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-3xl font-bold text-[#131514] dark:text-background-light">Add School Need</h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">Add items and resources needed by schools</p>
        </header>
        
        <div class="p-8 max-w-4xl">
            <?php if ($success_message): ?>
            <div class="mb-6 p-4 bg-green-100 dark:bg-green-900/20 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-400 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-400 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined">error</span>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>
            
            <form action="../actions/admin_add_need.php" method="POST" id="addNeedForm" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8">
                <div class="space-y-6">
                    <!-- Select School -->
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Select School <span class="text-red-500">*</span>
                        </label>
                        <select id="school_id" name="school_id" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Choose a school...</option>
                            <?php foreach ($schools as $school): ?>
                            <option value="<?php echo $school['school_id']; ?>" <?php echo ($preselected_school == $school['school_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($school['school_name']) . ' (' . htmlspecialchars($school['location']) . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Item Name -->
                    <div>
                        <label for="item_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Item Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="item_name" name="item_name" required
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Science Textbooks"/>
                    </div>
                    
                    <!-- Item Description -->
                    <div>
                        <label for="item_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Item Description
                        </label>
                        <textarea id="item_description" name="item_description" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Provide details about the item..."></textarea>
                    </div>
                    
                    <!-- Category & Priority -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="item_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="item_category" name="item_category" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select Category</option>
                                <option value="Books">Books</option>
                                <option value="TextBooks">TextBooks</option>
                                <option value="Technology">Technology</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Sports Equipment">Sports Equipment</option>
                                <option value="Art Supplies">Art Supplies</option>
                                <option value="Laboratory Equipment">Laboratory Equipment</option>
                                <option value="Musical Instruments">Musical Instruments</option>
                                <option value="School Supplies">School Supplies</option>
                                <option value="Infrastructure">Infrastructure</option>
                                <option value="Uniforms">Uniforms</option>
                                <option value="Clothes">Clothes</option>
                                <option value="Desks">Desks</option>
                                <option value="Library Resources">Library Resources</option>
                                <option value="Computers">Computers</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select id="priority" name="priority" required
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Unit Price & Quantity -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Unit Price (GHS) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="unit_price" name="unit_price" required min="0.01" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 25.00"/>
                        </div>
                        
                        <div>
                            <label for="quantity_needed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Quantity Needed <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="quantity_needed" name="quantity_needed" required min="1"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 100"/>
                        </div>
                    </div>
                    
                    <!-- Image URL Input -->
                    <div>
                        <label for="image_url_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Item Image URL <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Find an image online, right-click it, and select "Copy Image Address" to get the URL
                        </p>
                        <div class="flex gap-2">
                            <input type="text" id="image_url_input" name="image_url" required
                                   class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="https://example.com/image.jpg"
                                   onchange="previewImage(this.value)"
                                   onpaste="setTimeout(function(){ previewImage(document.getElementById('image_url_input').value); }, 100)"/>
                            <button type="button" onclick="previewImage(document.getElementById('image_url_input').value)" 
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                                Preview
                            </button>
                        </div>
                        
                        <!-- Image Preview -->
                        <div id="image-preview-container" class="mt-4 hidden">
                            <div class="border-2 border-primary rounded-lg p-4 bg-gray-50 dark:bg-gray-900">
                                <img id="preview-img" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg shadow-md"/>
                                <p class="text-sm text-center text-green-600 dark:text-green-400 mt-2 flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-lg">check_circle</span>
                                    Image loaded successfully
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-4">
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Add School Need
                    </button>
                    <a href="dashboard.php"
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-bold hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Preview image from URL
function previewImage(url) {
    const previewContainer = document.getElementById('image-preview-container');
    const previewImg = document.getElementById('preview-img');
    
    if (url && url.trim().startsWith('http')) {
        // Set up error handler before setting src
        previewImg.onerror = function() {
            previewContainer.classList.add('hidden');
            Swal.fire({
                title: 'Image Error',
                text: 'Could not load the image. Please check the URL and try again.',
                icon: 'error',
                confirmButtonColor: '#A4B8A4'
            });
        };
        previewImg.onload = function() {
            previewContainer.classList.remove('hidden');
        };
        previewImg.src = url.trim();
    } else {
        previewContainer.classList.add('hidden');
    }
}

// Form validation before submit
document.getElementById('addNeedForm').addEventListener('submit', function(e) {
    const imageUrlInput = document.getElementById('image_url_input');
    const imageUrl = imageUrlInput.value.trim();
    
    console.log('Form submitting with image_url:', imageUrl);
    
    if (!imageUrl || !imageUrl.startsWith('http')) {
        e.preventDefault();
        Swal.fire({
            title: 'Image Required',
            text: 'Please enter a valid image URL starting with http:// or https://',
            icon: 'warning',
            confirmButtonColor: '#A4B8A4'
        });
        return false;
    }
    
    // Make sure the value is trimmed before submitting
    imageUrlInput.value = imageUrl;
    
    console.log('Submitting form with image_url value:', imageUrlInput.value);
});
</script>

</body>
</html>
