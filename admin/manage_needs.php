<?php
session_start();
require_once __DIR__ . '/../settings/admin_check.php';
require_once __DIR__ . '/../settings/db_class.php';

$db = new db_connection();
$admin_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Admin';

// Get filter parameters
$school_filter = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with filters
$query = "SELECT sn.*, s.school_name, s.location 
          FROM school_needs sn
          JOIN schools s ON sn.school_id = s.school_id
          WHERE 1=1";
$params = [];

if ($school_filter > 0) {
    $query .= " AND sn.school_id = ?";
    $params[] = $school_filter;
}

if ($status_filter && in_array($status_filter, ['active', 'fulfilled', 'inactive'])) {
    $query .= " AND sn.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY sn.created_at DESC";

$needs = $db->db_fetch_all($query, $params);

// Get all schools for filter dropdown
$schools = $db->db_fetch_all("SELECT school_id, school_name FROM schools ORDER BY school_name ASC");

// Category options matching database ENUM
$categories = [
    'Books', 'TextBooks', 'Technology', 'Furniture', 'Sports Equipment',
    'Art Supplies', 'Laboratory Equipment', 'Musical Instruments', 'School Supplies',
    'Infrastructure', 'Uniforms', 'Clothes', 'Desks', 'Library Resources', 'Computers'
];
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Manage School Needs – GiveToGrow Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
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
                    <a href="add_need.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="material-symbols-outlined">add_circle</span>
                        Add School Need
                    </a>
                </li>
                <li>
                    <a href="manage_needs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
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
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-[#131514] dark:text-background-light">Manage School Needs</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">View, edit, and delete school needs</p>
                </div>
                <a href="add_need.php" class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90">
                    <span class="material-symbols-outlined">add</span>
                    Add New Need
                </a>
            </div>
        </header>
        
        <!-- Filters -->
        <div class="px-8 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex gap-4 items-center flex-wrap">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Filter by School</label>
                    <select name="school_id" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-sm">
                        <option value="">All Schools</option>
                        <?php foreach ($schools as $school): ?>
                        <option value="<?php echo $school['school_id']; ?>" <?php echo ($school_filter == $school['school_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($school['school_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Filter by Status</label>
                    <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo ($status_filter == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="fulfilled" <?php echo ($status_filter == 'fulfilled') ? 'selected' : ''; ?>>Fulfilled</option>
                        <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-opacity-90">
                        Apply Filters
                    </button>
                    <a href="manage_needs.php" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300">
                        Clear
                    </a>
                </div>
            </form>
        </div>
        
        <div class="p-8">
            <?php if (empty($needs)): ?>
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">inventory_2</span>
                <p class="text-gray-500 dark:text-gray-400 mt-4">No school needs found.</p>
                <a href="add_need.php" class="inline-flex items-center gap-2 mt-4 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90">
                    <span class="material-symbols-outlined">add</span>
                    Add First Need
                </a>
            </div>
            <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">School</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($needs as $need): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img src="<?php echo htmlspecialchars($need['image_url']); ?>" 
                                             alt="" 
                                             class="w-12 h-12 rounded-lg object-cover mr-3"
                                             onerror="this.src='https://placehold.co/100x100/A4B8A4/white?text=No+Image'"/>
                                        <div>
                                            <div class="text-sm font-medium text-[#131514] dark:text-background-light">
                                                <?php echo htmlspecialchars($need['item_name']); ?>
                                            </div>
                                            <div class="text-xs text-gray-500">ID: <?php echo $need['need_id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($need['school_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($need['location']); ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <?php echo htmlspecialchars($need['item_category']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    GHS <?php echo number_format($need['unit_price'], 2); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo $need['quantity_fulfilled']; ?> / <?php echo $need['quantity_needed']; ?>
                                    </div>
                                    <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                                        <?php $progress = ($need['quantity_needed'] > 0) ? ($need['quantity_fulfilled'] / $need['quantity_needed']) * 100 : 0; ?>
                                        <div class="bg-primary h-1.5 rounded-full" style="width: <?php echo min(100, $progress); ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php 
                                        echo $need['priority'] == 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : 
                                            ($need['priority'] == 'high' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' : 
                                            ($need['priority'] == 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400')); 
                                    ?>">
                                        <?php echo ucfirst($need['priority']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php 
                                        echo $need['status'] == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 
                                            ($need['status'] == 'fulfilled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : 
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'); 
                                    ?>">
                                        <?php echo ucfirst($need['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2">
                                        <button onclick="editNeed(<?php echo htmlspecialchars(json_encode($need)); ?>)" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Edit">
                                            <span class="material-symbols-outlined text-xl">edit</span>
                                        </button>
                                        <button onclick="deleteNeed(<?php echo $need['need_id']; ?>, '<?php echo htmlspecialchars(addslashes($need['item_name'])); ?>')" 
                                           class="text-red-600 hover:text-red-800 dark:text-red-400" title="Delete">
                                            <span class="material-symbols-outlined text-xl">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
        
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 p-6 transform transition-all">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-[#131514] dark:text-background-light">Edit School Need</h3>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="editForm" class="space-y-4">
                <input type="hidden" id="edit_need_id" name="need_id"/>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name *</label>
                        <input type="text" id="edit_item_name" name="item_name" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                        <select id="edit_item_category" name="item_category" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea id="edit_item_description" name="item_description" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900"></textarea>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Price (GHS) *</label>
                        <input type="number" id="edit_unit_price" name="unit_price" required min="0.01" step="0.01"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity Needed *</label>
                        <input type="number" id="edit_quantity_needed" name="quantity_needed" required min="1"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900"/>
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity Fulfilled</label>
                        <input type="number" id="edit_quantity_fulfilled" name="quantity_fulfilled" min="0"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                        <select id="edit_priority" name="priority" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                    <select id="edit_status" name="status" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900">
                        <option value="active">Active</option>
                        <option value="fulfilled">Fulfilled</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image</label>
                    <div class="flex gap-4 items-start">
                        <img id="edit_image_preview" src="" alt="Preview" class="w-20 h-20 rounded-lg object-cover border"
                             onerror="this.src='https://placehold.co/100x100/A4B8A4/white?text=No+Image'"/>
                        <div class="flex-1">
                            <input type="hidden" id="edit_image_url" name="image_url"/>
                            <div class="flex gap-2 mb-2">
                                <input type="url" id="edit_image_url_input" 
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-sm"
                                       placeholder="Paste image URL here..."
                                       onchange="setEditImageUrl(this.value)"/>
                                <button type="button" onclick="applyImageUrl()" 
                                        class="px-3 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-opacity-90">
                                    Set
                                </button>
                            </div>
                            <button type="button" onclick="openEditUploadWidget()" 
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 text-sm">
                                Or Upload via Cloudinary
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-primary text-white rounded-lg font-bold hover:bg-opacity-90">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-bold hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cloudinary configuration
const CLOUDINARY_CLOUD_NAME = 'dlih7wpyw';
const CLOUDINARY_UPLOAD_PRESET = 'givetogrow_unsigned';

// Edit modal functions
function editNeed(need) {
    document.getElementById('edit_need_id').value = need.need_id;
    document.getElementById('edit_item_name').value = need.item_name;
    document.getElementById('edit_item_description').value = need.item_description || '';
    document.getElementById('edit_item_category').value = need.item_category;
    document.getElementById('edit_unit_price').value = need.unit_price;
    document.getElementById('edit_quantity_needed').value = need.quantity_needed;
    document.getElementById('edit_quantity_fulfilled').value = need.quantity_fulfilled || 0;
    document.getElementById('edit_priority').value = need.priority;
    document.getElementById('edit_status').value = need.status;
    document.getElementById('edit_image_url').value = need.image_url || '';
    document.getElementById('edit_image_url_input').value = need.image_url || '';
    document.getElementById('edit_image_preview').src = need.image_url || 'https://placehold.co/100x100/A4B8A4/white?text=No+Image';
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Set image URL from direct input
function setEditImageUrl(url) {
    if (url && url.trim()) {
        document.getElementById('edit_image_url').value = url.trim();
        document.getElementById('edit_image_preview').src = url.trim();
    }
}

// Apply image URL button
function applyImageUrl() {
    const url = document.getElementById('edit_image_url_input').value.trim();
    if (url && url.startsWith('http')) {
        document.getElementById('edit_image_url').value = url;
        document.getElementById('edit_image_preview').src = url;
        Swal.fire({
            title: 'Image Updated!',
            icon: 'success',
            confirmButtonColor: '#A4B8A4',
            timer: 1500
        });
    } else {
        Swal.fire({
            title: 'Invalid URL',
            text: 'Please enter a valid image URL starting with http:// or https://',
            icon: 'warning',
            confirmButtonColor: '#A4B8A4'
        });
    }
}

function openEditUploadWidget() {
    cloudinary.openUploadWidget({
        cloudName: CLOUDINARY_CLOUD_NAME,
        uploadPreset: CLOUDINARY_UPLOAD_PRESET,
        sources: ['local', 'url', 'camera'],
        multiple: false,
        maxFiles: 1,
        maxFileSize: 10000000,
        resourceType: 'image',
        clientAllowedFormats: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        cropping: true,
        croppingAspectRatio: 1,
        folder: 'givetogrow/needs',
        styles: {
            palette: {
                window: "#FFFFFF",
                windowBorder: "#A4B8A4",
                tabIcon: "#A4B8A4",
                link: "#A4B8A4",
                action: "#A4B8A4",
                inProgress: "#A4B8A4",
                complete: "#10B981",
                sourceBg: "#F7F7F7"
            }
        }
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            const imageUrl = result.info.secure_url;
            document.getElementById('edit_image_url').value = imageUrl;
            document.getElementById('edit_image_url_input').value = imageUrl;
            document.getElementById('edit_image_preview').src = imageUrl;
            
            Swal.fire({
                title: 'Image Updated!',
                icon: 'success',
                confirmButtonColor: '#A4B8A4',
                timer: 1500
            });
        }
    });
}

// Handle edit form submission
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Saving Changes...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('../actions/update_need.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'School need updated successfully!',
                icon: 'success',
                confirmButtonColor: '#A4B8A4'
            }).then(() => location.reload());
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to update school need',
                icon: 'error',
                confirmButtonColor: '#A4B8A4'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while updating the school need.',
            icon: 'error',
            confirmButtonColor: '#A4B8A4'
        });
    });
});

// Delete function
function deleteNeed(needId, itemName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `You want to delete "<strong>${itemName}</strong>"?<br><br>This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#A4B8A4',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('../actions/delete_need.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'need_id=' + needId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'School need deleted successfully!',
                        icon: 'success',
                        confirmButtonColor: '#A4B8A4'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to delete school need',
                        icon: 'error',
                        confirmButtonColor: '#A4B8A4'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the school need.',
                    icon: 'error',
                    confirmButtonColor: '#A4B8A4'
                });
            });
        }
    });
}
</script>

</body>
</html>
