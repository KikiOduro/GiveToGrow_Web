<?php
session_start();
require_once __DIR__ . '/../settings/db_class.php';

// Check admin authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. Admin privileges required.";
    header("Location: ../dashboard.php");
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: ../admin/add_school.php");
    exit();
}

// Get form data
$school_name = trim($_POST['school_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$country = trim($_POST['country'] ?? '');
$description = trim($_POST['description'] ?? '');
$image_url = trim($_POST['image_url'] ?? '');
$total_students = intval($_POST['total_students'] ?? 0);
$fundraising_goal = floatval($_POST['fundraising_goal'] ?? 0);
$status = $_POST['status'] ?? 'active';

// Validate required fields
if (empty($school_name) || empty($location) || empty($country) || empty($description) || empty($image_url) || $fundraising_goal <= 0) {
    $_SESSION['error_message'] = "All required fields must be filled out.";
    header("Location: ../admin/add_school.php");
    exit();
}

// Insert school into database
$db = new db_connection();

try {
    $query = "INSERT INTO schools (school_name, location, country, description, image_url, total_students, fundraising_goal, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $result = $db->db_query($query, [
        $school_name,
        $location,
        $country,
        $description,
        $image_url,
        $total_students,
        $fundraising_goal,
        $status
    ]);
    
    if ($result) {
        $school_id = $db->last_insert_id();
        $_SESSION['success_message'] = "School '$school_name' added successfully! You can now add needs for this school.";
        header("Location: ../admin/add_need.php?school_id=" . $school_id);
    } else {
        $_SESSION['error_message'] = "Failed to add school. Please try again.";
        header("Location: ../admin/add_school.php");
    }
} catch (Exception $e) {
    error_log("Error adding school: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while adding the school.";
    header("Location: ../admin/add_school.php");
}

exit();
?>
