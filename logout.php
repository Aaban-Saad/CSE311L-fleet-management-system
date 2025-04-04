<?php
require_once 'functions.php';

// Logout user
logout_user();

// Redirect to login page
header("Location: index.php");
exit;
?>

