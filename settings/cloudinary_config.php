<?php
/**
 * Cloudinary Configuration for GiveToGrow
 * 
 * ============================================
 * 🚀 SETUP INSTRUCTIONS:
 * ============================================
 * 
 * 1. Go to https://cloudinary.com and create a FREE account
 * 
 * 2. After signing up, go to your Dashboard:
 *    https://cloudinary.com/console
 * 
 * 3. Copy your "Cloud Name" from the dashboard
 *    (It looks like: dxxxxx or your-company-name)
 * 
 * 4. Create an UNSIGNED Upload Preset:
 *    - Go to Settings → Upload → Upload Presets
 *    - Click "Add Upload Preset"
 *    - Set "Signing Mode" to "Unsigned"
 *    - Name it: givetogrow_unsigned
 *    - Save
 * 
 * 5. Replace 'YOUR_CLOUD_NAME' below with your actual cloud name
 * 
 * ============================================
 */

// Your Cloudinary Cloud Name (from dashboard)
define('CLOUDINARY_CLOUD_NAME', 'dlih7wpyw');

// Upload preset name (create this in Cloudinary settings)
define('CLOUDINARY_UPLOAD_PRESET', 'givetogrow_unsigned');

// Folders for organizing uploads
define('CLOUDINARY_FOLDER_SCHOOLS', 'givetogrow/schools');
define('CLOUDINARY_FOLDER_NEEDS', 'givetogrow/needs');

/**
 * Get Cloudinary configuration for JavaScript
 */
function getCloudinaryConfig() {
    return [
        'cloudName' => CLOUDINARY_CLOUD_NAME,
        'uploadPreset' => CLOUDINARY_UPLOAD_PRESET
    ];
}
?>
