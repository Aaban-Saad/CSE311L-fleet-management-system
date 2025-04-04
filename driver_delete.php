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
    header("Location: dashboard.php?tab=drivers");
    exit;
}

$driver_id = (int)$_GET['id'];

// Delete driver
$stmt = $user_conn->prepare("DELETE FROM Drivers WHERE driver_id = ?");
$stmt->bind_param("i", $driver_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=drivers&deleted=1");
} else {
    header("Location: dashboard.php?tab=drivers&error=delete_failed");
}
exit;
?>

