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
    header("Location: dashboard.php?tab=fuel");
    exit;
}

$transaction_id = (int)$_GET['id'];

// Delete fuel transaction
$stmt = $user_conn->prepare("DELETE FROM FuelTransactions WHERE transaction_id = ?");
$stmt->bind_param("i", $transaction_id);

if ($stmt->execute()) {
    header("Location: dashboard.php?tab=fuel&deleted=1");
} else {
    header("Location: dashboard.php?tab=fuel&error=delete_failed");
}
exit;
?>

