<?php
require_once 'config.php';

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Modify the create_user_database function to handle connection errors gracefully
function create_user_database($email) {
    global $conn, $host, $username, $password;
    
    // Sanitize email to make it a valid database name
    $db_name = str_replace(['@', '.', '-', '+'], '_', $email);
    
    try {
        // Create user database
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name`";
        if ($conn->query($sql) !== TRUE) {
            error_log("Error creating database: " . $conn->error);
            return $db_name; // Return the database name anyway since it might already exist
        }
        
        // Connect to the new database
        $user_conn = new mysqli($host, $username, $password, $db_name);
        
        // If connection successful, create tables
        if (!$user_conn->connect_error) {
            create_tables($user_conn);
            $user_conn->close();
        } else {
            error_log("Error connecting to user database: " . $user_conn->connect_error);
        }
        
        return $db_name;
    } catch (Exception $e) {
        error_log("Exception in create_user_database: " . $e->getMessage());
        return $db_name; // Return the database name anyway to continue the process
    }
}

// Function to create tables in a user database
function create_tables($user_conn) {
    // Create Vehicles table
    $sql = "CREATE TABLE IF NOT EXISTS Vehicles (
        vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
        model VARCHAR(100) NOT NULL,
        year YEAR NOT NULL,
        licence_no VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('Active', 'Inactive', 'Under Maintenance') NOT NULL
    )";
    $user_conn->query($sql);
    
    // Create Drivers table
    $sql = "CREATE TABLE IF NOT EXISTS Drivers (
        driver_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        contact VARCHAR(20) UNIQUE NOT NULL,
        licence VARCHAR(50) UNIQUE NOT NULL,
        status ENUM('Available', 'Hired', 'Inactive') NOT NULL,
        vehicle_id INT,
        hire_date DATE,
        salary DECIMAL(10,2),
        FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id) ON DELETE SET NULL
    )";
    $user_conn->query($sql);
    
    // Create Maintainances table
    $sql = "CREATE TABLE IF NOT EXISTS Maintainances (
        maintainance_id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_id INT NOT NULL,
        service_type VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        status ENUM('Pending', 'Completed') NOT NULL,
        cost DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id) ON DELETE CASCADE
    )";
    $user_conn->query($sql);
    
    // Create FuelTransactions table
    $sql = "CREATE TABLE IF NOT EXISTS FuelTransactions (
        transaction_id INT AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        vehicle_id INT NOT NULL,
        driver_id INT NOT NULL,
        fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'CNG') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        cost DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id) ON DELETE CASCADE,
        FOREIGN KEY (driver_id) REFERENCES Drivers(driver_id) ON DELETE CASCADE
    )";
    $user_conn->query($sql);
}

// Function to check if tables exist and create them if they don't
function ensure_tables_exist($user_conn) {
    // Check if Vehicles table exists
    $result = $user_conn->query("SHOW TABLES LIKE 'Vehicles'");
    if ($result->num_rows == 0) {
        // Tables don't exist, create them
        create_tables($user_conn);
        echo '<div class="bg-green-900/50 border border-green-500 text-white px-4 py-3 rounded mb-4">Database tables have been created successfully.</div>';
    }
}

// Add this function to help with debugging
function debug_to_console($data) {
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }
    
    echo "<script>console.log('Debug: " . addslashes($output) . "');</script>";
}

// Modify the connect_to_user_database function to ensure proper error handling
function connect_to_user_database($email) {
    global $host, $username, $password;
    
    // Sanitize email to get database name
    $db_name = str_replace(['@', '.', '-', '+'], '_', $email);
    
    // Enable error reporting for mysqli
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        // Connect to user database
        $user_conn = new mysqli($host, $username, $password, $db_name);
        
        if ($user_conn->connect_error) {
            throw new Exception("Connection failed: " . $user_conn->connect_error);
        }
        
        return $user_conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}

// Function to register a new user
function register_user($email, $password) {
    global $conn;
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);
    
    if ($stmt->execute()) {
        // Create user database
        $db_name = create_user_database($email);
        if ($db_name) {
            return true;
        }
    }
    
    return false;
}

// Function to authenticate user
function login_user($email, $password) {
    global $conn;
    
    // Get user from database
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_db'] = str_replace(['@', '.', '-', '+'], '_', $email);
            
            return true;
        }
    }
    
    return false;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to logout user
function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    return true;
}

// Function to get count of records
function get_count($user_conn, $table) {
    try {
        $result = $user_conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
    } catch (Exception $e) {
        error_log("Error getting count: " . $e->getMessage());
    }
    return 0;
}

// Function to get all vehicles
function get_vehicles($user_conn) {
    try {
        $result = $user_conn->query("SELECT * FROM Vehicles ORDER BY vehicle_id DESC");
        $vehicles = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $vehicles[] = $row;
            }
        }
        return $vehicles;
    } catch (Exception $e) {
        error_log("Error getting vehicles: " . $e->getMessage());
        return [];
    }
}

// Function to get all drivers
function get_drivers($user_conn) {
    try {
        $result = $user_conn->query("SELECT d.*, v.model as vehicle_model FROM Drivers d LEFT JOIN Vehicles v ON d.vehicle_id = v.vehicle_id ORDER BY driver_id DESC");
        $drivers = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $drivers[] = $row;
            }
        }
        return $drivers;
    } catch (Exception $e) {
        error_log("Error getting drivers: " . $e->getMessage());
        return [];
    }
}

// Function to get all maintenance records
function get_maintenance($user_conn) {
    try {
        $result = $user_conn->query("SELECT m.*, v.model as vehicle_model FROM Maintainances m JOIN Vehicles v ON m.vehicle_id = v.vehicle_id ORDER BY maintainance_id DESC");
        $maintenance = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $maintenance[] = $row;
            }
        }
        return $maintenance;
    } catch (Exception $e) {
        error_log("Error getting maintenance records: " . $e->getMessage());
        return [];
    }
}

// Function to get all fuel transactions
function get_fuel_transactions($user_conn) {
    try {
        $result = $user_conn->query("SELECT f.*, v.model as vehicle_model, d.name as driver_name FROM FuelTransactions f JOIN Vehicles v ON f.vehicle_id = v.vehicle_id JOIN Drivers d ON f.driver_id = d.driver_id ORDER BY transaction_id DESC");
        $transactions = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
        }
        return $transactions;
    } catch (Exception $e) {
        error_log("Error getting fuel transactions: " . $e->getMessage());
        return [];
    }
}

// Function to get a vehicle by ID
function get_vehicle_by_id($user_conn, $id) {
    try {
        $stmt = $user_conn->prepare("SELECT * FROM Vehicles WHERE vehicle_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error getting vehicle by ID: " . $e->getMessage());
    }
    return null;
}

// Function to get a driver by ID
function get_driver_by_id($user_conn, $id) {
    try {
        $stmt = $user_conn->prepare("SELECT * FROM Drivers WHERE driver_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error getting driver by ID: " . $e->getMessage());
    }
    return null;
}

// Function to get a maintenance record by ID
function get_maintenance_by_id($user_conn, $id) {
    try {
        $stmt = $user_conn->prepare("SELECT * FROM Maintainances WHERE maintainance_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error getting maintenance by ID: " . $e->getMessage());
    }
    return null;
}

// Function to get a fuel transaction by ID
function get_fuel_transaction_by_id($user_conn, $id) {
    try {
        $stmt = $user_conn->prepare("SELECT * FROM FuelTransactions WHERE transaction_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    } catch (Exception $e) {
        error_log("Error getting fuel transaction by ID: " . $e->getMessage());
    }
    return null;
}
?>


