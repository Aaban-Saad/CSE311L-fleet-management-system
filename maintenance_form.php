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

$maintenance = [
    'maintainance_id' => '',
    'vehicle_id' => '',
    'service_type' => '',
    'date' => date('Y-m-d'),
    'status' => 'Pending',
    'cost' => ''
];

$is_edit = false;
$error = '';
$success = '';

// Get all vehicles for dropdown
$vehicles = get_vehicles($user_conn);

// Check if editing existing maintenance record
if (isset($_GET['id'])) {
    $maintenance_id = (int)$_GET['id'];
    $maintenance_data = get_maintenance_by_id($user_conn, $maintenance_id);
    
    if ($maintenance_data) {
        $maintenance = $maintenance_data;
        $is_edit = true;
    } else {
        $error = "Maintenance record not found";
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $vehicle_id = (int)$_POST['vehicle_id'];
    $service_type = sanitize_input($_POST['service_type']);
    $date = sanitize_input($_POST['date']);
    $status = sanitize_input($_POST['status']);
    $cost = (float)$_POST['cost'];
    
    // Validate input
    if (empty($vehicle_id) || empty($service_type) || empty($date) || empty($status) || empty($cost)) {
        $error = "Please fill in all fields";
    } else {
        if ($is_edit) {
            // Update existing maintenance record
            $stmt = $user_conn->prepare("UPDATE Maintainances SET vehicle_id = ?, service_type = ?, date = ?, status = ?, cost = ? WHERE maintainance_id = ?");
            $stmt->bind_param("isssdi", $vehicle_id, $service_type, $date, $status, $cost, $maintenance['maintainance_id']);
            
            if ($stmt->execute()) {
                $success = "Maintenance record updated successfully";
                // Refresh maintenance data
                $maintenance = get_maintenance_by_id($user_conn, $maintenance['maintainance_id']);
            } else {
                $error = "Error updating maintenance record: " . $user_conn->error;
            }
        } else {
            // Insert new maintenance record
            $stmt = $user_conn->prepare("INSERT INTO Maintainances (vehicle_id, service_type, date, status, cost) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isssd", $vehicle_id, $service_type, $date, $status, $cost);
            
            if ($stmt->execute()) {
                header("Location: dashboard.php?tab=maintenance");
                exit;
            } else {
                $error = "Error adding maintenance record: " . $user_conn->error;
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
    <title>FleetOpz | <?php echo $is_edit ? 'Edit' : 'Add'; ?> Maintenance</title>
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
            <a href="dashboard.php?tab=maintenance" class="text-sm bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Back to Maintenance</a>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4"><?php echo $is_edit ? 'Edit' : 'Add'; ?> Maintenance Record</h1>
            
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
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit ? "?id=" . $maintenance['maintainance_id'] : "")); ?>" class="space-y-4">
                <div>
                    <label for="vehicle_id" class="block mb-1">Vehicle *</label>
                    <select id="vehicle_id" name="vehicle_id" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="">Select Vehicle</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle['vehicle_id']; ?>" <?php echo $maintenance['vehicle_id'] == $vehicle['vehicle_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vehicle['model'] . ' (' . $vehicle['licence_no'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="service_type" class="block mb-1">Service Type *</label>
                    <input type="text" id="service_type" name="service_type" value="<?php echo htmlspecialchars($maintenance['service_type']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="date" class="block mb-1">Date *</label>
                    <input type="date" id="date" name="date" value="<?php echo $maintenance['date']; ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="status" class="block mb-1">Status *</label>
                    <select id="status" name="status" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="Pending" <?php echo $maintenance['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo $maintenance['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div>
                    <label for="cost" class="block mb-1">Cost ($) *</label>
                    <input type="number" id="cost" name="cost" value="<?php echo $maintenance['cost']; ?>" step="0.01" min="0" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        <?php echo $is_edit ? 'Update Maintenance Record' : 'Add Maintenance Record'; ?>
                    </button>
                    <a href="dashboard.php?tab=maintenance" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>

