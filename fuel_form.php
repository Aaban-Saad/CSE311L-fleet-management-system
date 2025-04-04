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

$fuel = [
    'transaction_id' => '',
    'date' => date('Y-m-d'),
    'vehicle_id' => '',
    'driver_id' => '',
    'fuel_type' => 'Petrol',
    'amount' => '',
    'cost' => ''
];

$is_edit = false;
$error = '';
$success = '';

// Get all vehicles and drivers for dropdowns
$vehicles = get_vehicles($user_conn);
$drivers = get_drivers($user_conn);

// Check if editing existing fuel transaction
if (isset($_GET['id'])) {
    $transaction_id = (int)$_GET['id'];
    $fuel_data = get_fuel_transaction_by_id($user_conn, $transaction_id);
    
    if ($fuel_data) {
        $fuel = $fuel_data;
        $is_edit = true;
    } else {
        $error = "Fuel transaction not found";
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $date = sanitize_input($_POST['date']);
    $vehicle_id = (int)$_POST['vehicle_id'];
    $driver_id = (int)$_POST['driver_id'];
    $fuel_type = sanitize_input($_POST['fuel_type']);
    $amount = (float)$_POST['amount'];
    $cost = (float)$_POST['cost'];
    
    // Validate input
    if (empty($date) || empty($vehicle_id) || empty($driver_id) || empty($fuel_type) || empty($amount) || empty($cost)) {
        $error = "Please fill in all fields";
    } else {
        if ($is_edit) {
            // Update existing fuel transaction
            $stmt = $user_conn->prepare("UPDATE FuelTransactions SET date = ?, vehicle_id = ?, driver_id = ?, fuel_type = ?, amount = ?, cost = ? WHERE transaction_id = ?");
            $stmt->bind_param("siisddi", $date, $vehicle_id, $driver_id, $fuel_type, $amount, $cost, $fuel['transaction_id']);
            
            if ($stmt->execute()) {
                $success = "Fuel transaction updated successfully";
                // Refresh fuel transaction data
                $fuel = get_fuel_transaction_by_id($user_conn, $fuel['transaction_id']);
            } else {
                $error = "Error updating fuel transaction: " . $user_conn->error;
            }
        } else {
            // Insert new fuel transaction
            $stmt = $user_conn->prepare("INSERT INTO FuelTransactions (date, vehicle_id, driver_id, fuel_type, amount, cost) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siisdd", $date, $vehicle_id, $driver_id, $fuel_type, $amount, $cost);
            
            if ($stmt->execute()) {
                header("Location: dashboard.php?tab=fuel");
                exit;
            } else {
                $error = "Error adding fuel transaction: " . $user_conn->error;
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
    <title>FleetOpz | <?php echo $is_edit ? 'Edit' : 'Add'; ?> Fuel Transaction</title>
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
            <a href="dashboard.php?tab=fuel" class="text-sm bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Back to Fuel Transactions</a>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-4"><?php echo $is_edit ? 'Edit' : 'Add'; ?> Fuel Transaction</h1>
            
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
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit ? "?id=" . $fuel['transaction_id'] : "")); ?>" class="space-y-4">
                <div>
                    <label for="date" class="block mb-1">Date *</label>
                    <input type="date" id="date" name="date" value="<?php echo $fuel['date']; ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="vehicle_id" class="block mb-1">Vehicle *</label>
                    <select id="vehicle_id" name="vehicle_id" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="">Select Vehicle</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle['vehicle_id']; ?>" <?php echo $fuel['vehicle_id'] == $vehicle['vehicle_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vehicle['model'] . ' (' . $vehicle['licence_no'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="driver_id" class="block mb-1">Driver *</label>
                    <select id="driver_id" name="driver_id" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="">Select Driver</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo $driver['driver_id']; ?>" <?php echo $fuel['driver_id'] == $driver['driver_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($driver['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="fuel_type" class="block mb-1">Fuel Type *</label>
                    <select id="fuel_type" name="fuel_type" class="w-full p-2 rounded bg-black/30 text-white" required>
                        <option value="Petrol" <?php echo $fuel['fuel_type'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                        <option value="Diesel" <?php echo $fuel['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Electric" <?php echo $fuel['fuel_type'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                        <option value="CNG" <?php echo $fuel['fuel_type'] == 'CNG' ? 'selected' : ''; ?>>CNG</option>
                    </select>
                </div>
                
                <div>
                    <label for="amount" class="block mb-1">Amount (Liters) *</label>
                    <input type="number" id="amount" name="amount" value="<?php echo $fuel['amount']; ?>" step="0.01" min="0" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div>
                    <label for="cost" class="block mb-1">Cost ($) *</label>
                    <input type="number" id="cost" name="cost" value="<?php echo $fuel['cost']; ?>" step="0.01" min="0" class="w-full p-2 rounded bg-black/30 text-white" required>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        <?php echo $is_edit ? 'Update Fuel Transaction' : 'Add Fuel Transaction'; ?>
                    </button>
                    <a href="dashboard.php?tab=fuel" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>

