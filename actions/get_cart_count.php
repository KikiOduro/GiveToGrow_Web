<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}

require_once __DIR__ . '/../settings/db_class.php';

$user_id = $_SESSION['user_id'];

try {
    $db = new db_connection();
    
    // Get total quantity of items in cart
    $query = "SELECT COALESCE(SUM(quantity), 0) as total_items FROM cart WHERE user_id = ?";
    $result = $db->db_fetch_one($query, [$user_id]);
    
    $count = $result ? intval($result['total_items']) : 0;
    
    echo json_encode(['success' => true, 'count' => $count]);
    
} catch (Exception $e) {
    error_log("Get cart count exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0]);
}
?>
