<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password (leave empty if no password)
$db_name = "fleetopz_users"; // Main database to store user credentials

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection to main database
    $conn = new mysqli($host, $username, $password);
    
    // Create main database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $db_name";
    if ($conn->query($sql) !== TRUE) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Connect to the main database
    $conn = new mysqli($host, $username, $password, $db_name);
    
    // Create users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS users (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      email VARCHAR(255) NOT NULL UNIQUE,
      password VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) !== TRUE) {
        throw new Exception("Error creating table: " . $conn->error);
    }
} catch (Exception $e) {
    // Log the error but don't stop execution
    error_log("Database setup error: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>


