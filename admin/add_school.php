<?php
session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

// Handle success/error messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Add School – GiveToGrow Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
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
        
        // function toggleTheme() {
        //     const html = document.documentElement;
        //     const isDark = html.classList.contains('dark');
        //     html.classList.toggle('dark');
        //     localStorage.setItem('theme', isDark ? 'light' : 'dark');
        // }
        
        // initTheme();
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#333333] dark:text-background-light font-display">
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
                    <a href="add_school.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
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
                    <a href="add_need.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
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
                <h1 class="text-3xl font-bold text-[#131514] dark:text-background-light">Add New School</h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">Register a new underresourced school to the platform</p>
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
            
            <form action="../actions/admin_add_school.php" method="POST" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8">
                <div class="space-y-6">
                    <!-- School Name -->
                    <div>
                        <label for="school_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            School Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="school_name" name="school_name" required
                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g., Kibera Primary School"/>
                    </div>
                    
                    <!-- Location & Region -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Location/City <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="location" name="location" required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Accra"/>
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Region <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="country" name="country" required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., Greater Accra, Ashanti, Northern, etc."/>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            School Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="description" name="description" required rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Provide a compelling description of the school and why it needs support..."></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This will be displayed to potential donors</p>
                    </div>
                    
                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            School Image <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Hidden input to store the image URL -->
                        <input type="hidden" id="image_url" name="image_url" required/>
                        
                        <!-- Upload Button -->
                        <div id="upload-container" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer" onclick="openUploadWidget()">
                            <div id="upload-placeholder">
                                <span class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                                <p class="text-gray-600 dark:text-gray-400 font-medium">Click to upload a school image</p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Supports: JPG, PNG, GIF, WEBP (Max 10MB)</p>
                            </div>
                            
                            <!-- Preview (hidden by default) -->
                            <div id="image-preview" class="hidden">
                                <img id="preview-img" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg shadow-md"/>
                                <p id="preview-name" class="text-sm text-gray-600 dark:text-gray-400 mt-2"></p>
                                <button type="button" onclick="event.stopPropagation(); removeImage();" class="mt-2 text-red-500 hover:text-red-700 text-sm flex items-center gap-1 mx-auto">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                    Remove Image
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Images are securely stored on Cloudinary</p>
                    </div>
                    
                    <!-- Total Students & Fundraising Goal -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="total_students" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Total Students <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="total_students" name="total_students" required min="1"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 300"/>
                        </div>
                        
                        <div>
                            <label for="fundraising_goal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Fundraising Goal (GHS) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="fundraising_goal" name="fundraising_goal" required min="1" step="0.01"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g., 10000.00"/>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            School Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-[#131514] dark:text-background-light focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Active schools will be visible to donors on the public site</p>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-4">
                    <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">add</span>
                        Add School
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
// Cloudinary configuration
// ⚠️ IMPORTANT: Replace 'YOUR_CLOUD_NAME' with your actual Cloudinary cloud name
// You can find this in your Cloudinary dashboard at https://cloudinary.com/console
const CLOUDINARY_CLOUD_NAME = 'dlih7wpyw';
const CLOUDINARY_UPLOAD_PRESET = 'givetogrow_unsigned';

function openUploadWidget() {
    cloudinary.openUploadWidget({
        cloudName: CLOUDINARY_CLOUD_NAME,
        uploadPreset: CLOUDINARY_UPLOAD_PRESET,
        sources: ['local', 'url', 'camera'],
        multiple: false,
        maxFiles: 1,
        maxFileSize: 10000000, // 10MB
        resourceType: 'image',
        clientAllowedFormats: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        cropping: true,
        croppingAspectRatio: 16/9,
        croppingShowDimensions: true,
        folder: 'givetogrow/schools',
        styles: {
            palette: {
                window: "#FFFFFF",
                windowBorder: "#A4B8A4",
                tabIcon: "#A4B8A4",
                menuIcons: "#5A616A",
                textDark: "#000000",
                textLight: "#FFFFFF",
                link: "#A4B8A4",
                action: "#A4B8A4",
                inactiveTabIcon: "#9CA3AF",
                error: "#EF4444",
                inProgress: "#A4B8A4",
                complete: "#10B981",
                sourceBg: "#F7F7F7"
            },
            fonts: {
                default: { active: true }
            }
        }
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            // Get the secure URL
            const imageUrl = result.info.secure_url;
            const fileName = result.info.original_filename + '.' + result.info.format;
            
            // Update the hidden input
            document.getElementById('image_url').value = imageUrl;
            
            // Show preview
            document.getElementById('upload-placeholder').classList.add('hidden');
            document.getElementById('image-preview').classList.remove('hidden');
            document.getElementById('preview-img').src = imageUrl;
            document.getElementById('preview-name').textContent = fileName;
            
            // Update container style
            document.getElementById('upload-container').classList.remove('border-dashed');
            document.getElementById('upload-container').classList.add('border-solid', 'border-primary');
            
            Swal.fire({
                title: 'Image Uploaded!',
                text: 'Your image has been uploaded successfully.',
                icon: 'success',
                confirmButtonColor: '#A4B8A4',
                timer: 2000
            });
        }
    });
}

function removeImage() {
    // Clear the hidden input
    document.getElementById('image_url').value = '';
    
    // Hide preview, show placeholder
    document.getElementById('upload-placeholder').classList.remove('hidden');
    document.getElementById('image-preview').classList.add('hidden');
    document.getElementById('preview-img').src = '';
    document.getElementById('preview-name').textContent = '';
    
    // Reset container style
    document.getElementById('upload-container').classList.add('border-dashed');
    document.getElementById('upload-container').classList.remove('border-solid', 'border-primary');
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const imageUrl = document.getElementById('image_url').value;
    if (!imageUrl) {
        e.preventDefault();
        Swal.fire({
            title: 'Image Required',
            text: 'Please upload an image for the school.',
            icon: 'warning',
            confirmButtonColor: '#A4B8A4'
        });
    }
});
</script>

</body>
</html>
