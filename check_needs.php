<?php
require_once __DIR__ . '/settings/db_class.php';

$db = new db_connection();

echo "<h2>Recent School Needs (Last 10)</h2>";

$needs = $db->db_fetch_all("SELECT need_id, school_id, item_name, image_url, created_at FROM school_needs ORDER BY created_at DESC LIMIT 10");

if ($needs) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>School ID</th><th>Item Name</th><th>Image URL (raw)</th><th>Image Preview</th><th>Created</th></tr>";
    foreach ($needs as $need) {
        $imgUrl = $need['image_url'] ?? '';
        echo "<tr>";
        echo "<td>" . $need['need_id'] . "</td>";
        echo "<td>" . $need['school_id'] . "</td>";
        echo "<td>" . htmlspecialchars($need['item_name']) . "</td>";
        echo "<td style='max-width:300px; word-break:break-all; font-size:11px;'><code>" . htmlspecialchars($imgUrl) . "</code><br><strong>Length: " . strlen($imgUrl) . "</strong></td>";
        echo "<td style='width:120px;'>";
        if ($imgUrl && strlen($imgUrl) > 10) {
            echo "<img src='" . htmlspecialchars($imgUrl) . "' style='max-width:100px; max-height:100px;' onerror=\"this.outerHTML='<span style=color:red>BROKEN</span>'\">";
        } else {
            echo "<span style='color:orange;'>Empty/Invalid URL</span>";
        }
        echo "</td>";
        echo "<td>" . $need['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No needs found</p>";
}

// Also show what the form would submit
echo "<hr><h3>Test Form Submission</h3>";
echo "<p>If you're having issues, the image_url field might not be getting the value. Check browser console for errors.</p>";
?>

