<?php
require_once 'functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

// Connect to user database
$user_conn = connect_to_user_database($_SESSION['user_email']);

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?tab=maintenance");
    exit;
}

$maintenance_id = (int)$_GET['id'];

// Delete maintenance record
$stmt = $user_conn->prepare("DELETE FROM Maintainances WHERE maintainance_id = ?");
$stmt->bind_param("i", $maintenance_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=maintenance&deleted=1");
} else {
    header("Location: dashboard.php?tab=maintenance&error=delete_failed");
}
exit;
?>

