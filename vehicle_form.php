<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

// Connect to user database
$user_conn = connect_to_user_database($_SESSION['user_email']);

$vehicle = [
    'vehicle_id' => '',
    'model' => '',
    'year' => date('Y'),
    'licence_no' => '',
    'status' => 'Active'
];

$is_edit = false;
$error = '';
$success = '';

// Check if editing existing vehicle
if (isset($_GET['id'])) {
    $vehicle_id = (int)$_GET['id'];
    $vehicle_data = get_vehicle_by_id($user_conn, $vehicle_id);
    
    if ($vehicle_data) {
        $vehicle = $vehicle_data;
        $is_edit = true;
    } else {
        $error = "Vehicle not found";
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $model = sanitize_input($_POST['model']);
    $year = sanitize_input($_POST['year']);
    $licence_no = sanitize_input($_POST['licence_no']);
    $status = sanitize_input($_POST['status']);
    
    // Validate input
    if (empty($model) || empty($year) || empty($licence_no) || empty($status)) {
        $error = "Please fill in all fields";
    } else {
        if ($is_edit) {
            // Update existing vehicle
            $stmt = $user_conn->prepare("UPDATE Vehicles SET model = ?, year = ?, licence_no = ?, status = ? WHERE vehicle_id = ?");
            $stmt->bind_param("sissi", $model, $year, $licence_no, $status, $vehicle['vehicle_id']);
            
            if ($stmt->execute()) {
                $success = "Vehicle updated successfully";
                // Refresh vehicle data
                $vehicle = get_vehicle_by_id($user_conn, $vehicle['vehicle_id']);
            } else {
                $error = "Error updating vehicle: " . $user_conn->error;
            }
        } else {
            // Insert new vehicle
            try {
                $stmt = $user_conn->prepare("INSERT INTO Vehicles (model, year, licence_no, status) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $user_conn->error);
                }
                
                $bind_result = $stmt->bind_param("siss", $model, $year, $licence_no, $status);
                if (!$bind_result) {
                    throw new Exception("Binding parameters failed: " . $stmt->error);
                }
                
                $exec_result = $stmt->execute();
                if (!$exec_result) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                header("Location: dashboard.php?tab=vehicles");
                exit;
            } catch (Exception $e) {
                $error = "Error adding vehicle: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>FleetOpz | <?php echo $is_edit ? 'Edit' : 'Add'; ?> Vehicle</title>
    <style>
        body {
            background-color: rgb(24, 28, 31);
        }
    </style>
</head>

<body class="text-white">
    <nav class="p-6 flex items-center justify-between bg-black/30">
        <a href="dashboard.php" class="flex items-center justify-center gap-2 font-bold">
            <img src="public/logo.png" height="18px" width="18px" alt="logo">
            FleetOpz
        </a>
        <div class="flex items-center gap-4">
            <a href="dashboard.php?tab=vehicles" class="text-sm bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Back to Vehicles</a>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4"><?php echo $is_edit ? 'Edit' : 'Add'; ?> Vehicle</h1>
            
            <?php if (!empty($error)): ?>
                <div class="mb-4 bg-red-900/50 border border-red-500 text-white px-4 py-3 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="mb-4 bg-green-900/50 border border-green-500 text-white px-4 py-3 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit ? "?id=" . $vehicle['vehicle_id'] : "")); ?>" class="space-y-4">
                <div>
                    <label for="model" class="block mb-1">Model</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="year" class="block mb-1">Year</label>
                    <input type="number" id="year" name="year" value="<?php echo $vehicle['year']; ?>" min="1900" max="<?php echo date('Y') + 1; ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="licence_no" class="block mb-1">License Number</label>
                    <input type="text" id="licence_no" name="licence_no" value="<?php echo htmlspecialchars($vehicle['licence_no']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="status" class="block mb-1">Status</label>
                    <select id="status" name="status" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="Active" <?php echo $vehicle['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $vehicle['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Under Maintenance" <?php echo $vehicle['status'] == 'Under Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                    </select>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        <?php echo $is_edit ? 'Update Vehicle' : 'Add Vehicle'; ?>
                    </button>
                    <a href="dashboard.php?tab=vehicles" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>

