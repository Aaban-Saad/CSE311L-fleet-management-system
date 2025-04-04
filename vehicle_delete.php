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
    header("Location: dashboard.php?tab=vehicles");
    exit;
}

$vehicle_id = (int)$_GET['id'];

// Delete vehicle
$stmt = $user_conn->prepare("DELETE FROM Vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=vehicles&deleted=1");
} else {
    header("Location: dashboard.php?tab=vehicles&error=delete_failed");
}
exit;
?>

