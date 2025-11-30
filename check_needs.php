<?php
require_once __DIR__ . '/settings/db_class.php';

$db = new db_connection();

echo "<h2>Recent School Needs</h2>";

$needs = $db->db_fetch_all("SELECT need_id, school_id, item_name, image_url, created_at FROM school_needs ORDER BY created_at DESC LIMIT 10");

if ($needs) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>School ID</th><th>Item Name</th><th>Image URL</th><th>Image Preview</th><th>Created</th></tr>";
    foreach ($needs as $need) {
        echo "<tr>";
        echo "<td>" . $need['need_id'] . "</td>";
        echo "<td>" . $need['school_id'] . "</td>";
        echo "<td>" . htmlspecialchars($need['item_name']) . "</td>";
        echo "<td style='max-width:300px; word-break:break-all;'>" . htmlspecialchars($need['image_url'] ?? 'NULL') . "</td>";
        echo "<td>";
        if ($need['image_url']) {
            echo "<img src='" . htmlspecialchars($need['image_url']) . "' style='max-width:100px; max-height:100px;' onerror=\"this.src='https://placehold.co/100x100/red/white?text=BROKEN'\">";
        } else {
            echo "No URL";
        }
        echo "</td>";
        echo "<td>" . $need['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No needs found</p>";
}
?>
