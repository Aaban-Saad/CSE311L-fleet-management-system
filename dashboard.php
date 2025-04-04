<?php
require_once 'functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    header("Location: index.php");
    exit;
}

// Get user information
$user_email = $_SESSION['user_email'];
$user_db = $_SESSION['user_db'];

// Connect to user database
$user_conn = connect_to_user_database($_SESSION['user_email']);

// Check if tables exist and create them if they don't
ensure_tables_exist($user_conn);

// Handle logout
if (isset($_GET['logout'])) {
    logout_user();
    header("Location: index.php");
    exit;
}

// Get active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'vehicles';

// Get counts
$vehicle_count = get_count($user_conn, 'Vehicles');
$driver_count = get_count($user_conn, 'Drivers');
$maintenance_count = get_count($user_conn, 'Maintainances');
$fuel_count = get_count($user_conn, 'FuelTransactions');

// Get data based on active tab
$vehicles = ($active_tab == 'vehicles') ? get_vehicles($user_conn) : [];
$drivers = ($active_tab == 'drivers') ? get_drivers($user_conn) : [];
$maintenance = ($active_tab == 'maintenance') ? get_maintenance($user_conn) : [];
$fuel_transactions = ($active_tab == 'fuel') ? get_fuel_transactions($user_conn) : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <title>FleetOpz | Dashboard</title>
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
            <span class="text-sm"><?php echo htmlspecialchars($user_email); ?></span>
            <a href="?logout=1" class="text-sm bg-red-600 px-3 py-1 rounded hover:bg-red-700">Logout</a>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm">
            <h1 class="text-2xl font-bold mb-4">Fleet Management Dashboard</h1>
            <p class="mb-4">Connected to database: <strong><?php echo htmlspecialchars($user_db); ?></strong></p>
            
            <!-- Dashboard Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'vehicles' ? 'ring-2 ring-blue-500' : ''; ?>">
                    <h3 class="font-bold mb-2">Vehicles</h3>
                    <p class="text-3xl font-bold"><?php echo $vehicle_count; ?></p>
                </div>
                <div class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'drivers' ? 'ring-2 ring-blue-500' : ''; ?>">
                    <h3 class="font-bold mb-2">Drivers</h3>
                    <p class="text-3xl font-bold"><?php echo $driver_count; ?></p>
                </div>
                <div class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'maintenance' ? 'ring-2 ring-blue-500' : ''; ?>">
                    <h3 class="font-bold mb-2">Maintenance</h3>
                    <p class="text-3xl font-bold"><?php echo $maintenance_count; ?></p>
                </div>
                <div class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'fuel' ? 'ring-2 ring-blue-500' : ''; ?>">
                    <h3 class="font-bold mb-2">Fuel Transactions</h3>
                    <p class="text-3xl font-bold"><?php echo $fuel_count; ?></p>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-700 mb-6">
                <nav class="flex -mb-px">
                    <a href="?tab=vehicles" class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'vehicles' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                        Vehicles
                    </a>
                    <a href="?tab=drivers" class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'drivers' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                        Drivers
                    </a>
                    <a href="?tab=maintenance" class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'maintenance' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                        Maintenance
                    </a>
                    <a href="?tab=fuel" class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'fuel' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                        Fuel Transactions
                    </a>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="mb-6">
                <?php if ($active_tab == 'vehicles'): ?>
                    <!-- Vehicles Tab -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Vehicles</h2>
                        <a href="vehicle_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Vehicle</a>
                    </div>
                    
                    <?php if (empty($vehicles)): ?>
                        <div class="bg-black/20 p-6 rounded-lg text-center">
                            <p>No vehicles found. Add your first vehicle to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-black/20 rounded-lg">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
                                        <th class="px-4 py-2 text-left">Model</th>
                                        <th class="px-4 py-2 text-left">Year</th>
                                        <th class="px-4 py-2 text-left">License No</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <tr class="border-t border-gray-700">
                                            <td class="px-4 py-2"><?php echo $vehicle['vehicle_id']; ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['model']); ?></td>
                                            <td class="px-4 py-2"><?php echo $vehicle['year']; ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($vehicle['licence_no']); ?></td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded text-xs 
                                                    <?php 
                                                    if ($vehicle['status'] == 'Active') echo 'bg-green-800';
                                                    elseif ($vehicle['status'] == 'Inactive') echo 'bg-red-800';
                                                    else echo 'bg-yellow-800';
                                                    ?>">
                                                    <?php echo $vehicle['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <a href="vehicle_form.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="text-blue-400 hover:text-blue-300">Edit</a>
                                                    <a href="vehicle_delete.php?id=<?php echo $vehicle['vehicle_id']; ?>" class="text-red-400 hover:text-red-300" onclick="return confirm('Are you sure you want to delete this vehicle?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($active_tab == 'drivers'): ?>
                    <!-- Drivers Tab -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Drivers</h2>
                        <a href="driver_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Driver</a>
                    </div>
                    
                    <?php if (empty($drivers)): ?>
                        <div class="bg-black/20 p-6 rounded-lg text-center">
                            <p>No drivers found. Add your first driver to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-black/20 rounded-lg">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
                                        <th class="px-4 py-2 text-left">Name</th>
                                        <th class="px-4 py-2 text-left">Contact</th>
                                        <th class="px-4 py-2 text-left">License</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Vehicle</th>
                                        <th class="px-4 py-2 text-left">Hire Date</th>
                                        <th class="px-4 py-2 text-left">Salary</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($drivers as $driver): ?>
                                        <tr class="border-t border-gray-700">
                                            <td class="px-4 py-2"><?php echo $driver['driver_id']; ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($driver['name']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($driver['contact']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($driver['licence']); ?></td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded text-xs 
                                                    <?php 
                                                    if ($driver['status'] == 'Available') echo 'bg-green-800';
                                                    elseif ($driver['status'] == 'Hired') echo 'bg-blue-800';
                                                    else echo 'bg-red-800';
                                                    ?>">
                                                    <?php echo $driver['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2"><?php echo $driver['vehicle_model'] ? htmlspecialchars($driver['vehicle_model']) : 'None'; ?></td>
                                            <td class="px-4 py-2"><?php echo $driver['hire_date'] ? $driver['hire_date'] : 'N/A'; ?></td>
                                            <td class="px-4 py-2"><?php echo $driver['salary'] ? '$' . number_format($driver['salary'], 2) : 'N/A'; ?></td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <a href="driver_form.php?id=<?php echo $driver['driver_id']; ?>" class="text-blue-400 hover:text-blue-300">Edit</a>
                                                    <a href="driver_delete.php?id=<?php echo $driver['driver_id']; ?>" class="text-red-400 hover:text-red-300" onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($active_tab == 'maintenance'): ?>
                    <!-- Maintenance Tab -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Maintenance Records</h2>
                        <a href="maintenance_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Maintenance</a>
                    </div>
                    
                    <?php if (empty($maintenance)): ?>
                        <div class="bg-black/20 p-6 rounded-lg text-center">
                            <p>No maintenance records found. Add your first maintenance record to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-black/20 rounded-lg">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
                                        <th class="px-4 py-2 text-left">Vehicle</th>
                                        <th class="px-4 py-2 text-left">Service Type</th>
                                        <th class="px-4 py-2 text-left">Date</th>
                                        <th class="px-4 py-2 text-left">Status</th>
                                        <th class="px-4 py-2 text-left">Cost</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance as $record): ?>
                                        <tr class="border-t border-gray-700">
                                            <td class="px-4 py-2"><?php echo $record['maintainance_id']; ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($record['vehicle_model']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($record['service_type']); ?></td>
                                            <td class="px-4 py-2"><?php echo $record['date']; ?></td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded text-xs 
                                                    <?php echo $record['status'] == 'Completed' ? 'bg-green-800' : 'bg-yellow-800'; ?>">
                                                    <?php echo $record['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">$<?php echo number_format($record['cost'], 2); ?></td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <a href="maintenance_form.php?id=<?php echo $record['maintainance_id']; ?>" class="text-blue-400 hover:text-blue-300">Edit</a>
                                                    <a href="maintenance_delete.php?id=<?php echo $record['maintainance_id']; ?>" class="text-red-400 hover:text-red-300" onclick="return confirm('Are you sure you want to delete this maintenance record?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($active_tab == 'fuel'): ?>
                    <!-- Fuel Transactions Tab -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Fuel Transactions</h2>
                        <a href="fuel_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Fuel Transaction</a>
                    </div>
                    
                    <?php if (empty($fuel_transactions)): ?>
                        <div class="bg-black/20 p-6 rounded-lg text-center">
                            <p>No fuel transactions found. Add your first fuel transaction to get started.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-black/20 rounded-lg">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">ID</th>
                                        <th class="px-4 py-2 text-left">Date</th>
                                        <th class="px-4 py-2 text-left">Vehicle</th>
                                        <th class="px-4 py-2 text-left">Driver</th>
                                        <th class="px-4 py-2 text-left">Fuel Type</th>
                                        <th class="px-4 py-2 text-left">Amount</th>
                                        <th class="px-4 py-2 text-left">Cost</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fuel_transactions as $transaction): ?>
                                        <tr class="border-t border-gray-700">
                                            <td class="px-4 py-2"><?php echo $transaction['transaction_id']; ?></td>
                                            <td class="px-4 py-2"><?php echo $transaction['date']; ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['vehicle_model']); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['driver_name']); ?></td>
                                            <td class="px-4 py-2"><?php echo $transaction['fuel_type']; ?></td>
                                            <td class="px-4 py-2"><?php echo number_format($transaction['amount'], 2); ?> L</td>
                                            <td class="px-4 py-2">$<?php echo number_format($transaction['cost'], 2); ?></td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">
                                                    <a href="fuel_form.php?id=<?php echo $transaction['transaction_id']; ?>" class="text-blue-400 hover:text-blue-300">Edit</a>
                                                    <a href="fuel_delete.php?id=<?php echo $transaction['transaction_id']; ?>" class="text-red-400 hover:text-red-300" onclick="return confirm('Are you sure you want to delete this fuel transaction?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>

</html>


