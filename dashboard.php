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
    <script src="https://cdn.tailwindcss.com"></script>
    <title>FleetOpz | Dashboard</title>
    <style>
        .modal {
            transition: opacity 0.25s ease;
        }

        body.modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }

        .active-nav-item {
            font-weight: bold;
            color: #8b5cf6;
            background-color: rgba(139, 92, 246, 0.1);
            border-radius: 0.5rem;
        }
    </style>
</head>


<body class="min-h-screen bg-gray-50">
    <div class="flex h-screen overflow-hidden">

        <!-- <nav class="p-6 flex items-center justify-between bg-black/30">
            <a href="dashboard.php" class="flex items-center justify-center gap-2 font-bold">
                <img src="public/logo.png" height="18px" width="18px" alt="logo">
                FleetOpz
            </a>
            <div class="flex items-center gap-4">
                <span class="text-sm"><?php echo htmlspecialchars($user_email); ?></span>
                <a href="?logout=1" class="text-sm bg-red-600 px-3 py-1 rounded hover:bg-red-700">Logout</a>
            </div>
        </nav> -->


        <!-- Sidebar (Left) -->
        <div class="w-64 bg-white shadow-md hidden md:block">
            <!-- Logo -->
            <div class="p-4 border-b">
                <a href="#" class="flex items-center justify-center gap-2 font-bold text-xl">
                    <img src="public/logo.png" class="h-7" alt="logo">
                    <span>FleetOpz</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <div class="px-4">
                    <button id="add-vehicle-btn"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-black font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add Vehicle
                    </button>
                </div>
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="dashboard">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="?tab=vehicles"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200 <?php echo $active_tab == 'vehicles' ? 'font-bold' : 'font-base'; ?>"
                            data-page="vehicles">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Vehicles
                        </a>
                    </li>
                    <li>
                        <a href="?tab=drivers"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200 <?php echo $active_tab == 'drivers' ? 'font-bold' : 'font-base'; ?>"
                            data-page="drivers">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Drivers
                        </a>
                    </li>
                    <li>
                        <a href="?tab=maintenance"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200 <?php echo $active_tab == 'maintenance' ? 'font-bold' : 'font-base'; ?>"
                            data-page="maintenance">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Maintenance
                        </a>
                    </li>
                    <li>
                        <a href="?tab=fuel"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200 <?php echo $active_tab == 'fuel' ? 'font-bold' : 'font-base'; ?>"
                            data-page="fuel">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Fuel & Energy
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="reports">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Reports
                        </a>
                    </li>
                </ul>
            </nav>
        </div>



        <!-- Mobile nav -->
        <div class="bg-white shadow-xl absolute bottom-0 w-screen md:hidden">
            <div class="px-4">
                <!-- Vehicle add button -->
                <button id="add-vehicle-btn-mobile"
                    class="fixed bottom-14 right-3 w-10 h-10 bg-purple-600 hover:bg-purple-700 text-black font-medium rounded-full transition duration-200 flex items-center justify-center gap-2 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <nav class="h-16 flex items-center justify-center px-4">
                <ul class="flex w-full items-center justify-between">
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="dashboard">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="vehicles">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="drivers">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="maintenance">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="fuel">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="nav-link flex items-center gap-3 p-3 text-gray-700 hover:bg-purple-50 rounded-lg transition duration-200"
                            data-page="reports">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <main class="flex-1 overflow-y-auto mb-16 md:mb-0">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center gap-4">
                        <h1 id="page-title" class="text-2xl font-semibold text-gray-800">
                            <?php echo ucfirst($active_tab); ?>
                        </h1>
                    </div>
                    <div class="flex items-center gap-4 md:gap-2">
                        <!-- Search Box -->
                        <div class="relative hidden md:block">
                            <input type="text" placeholder="Search..."
                                class="w-44 xl:w-56 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <!-- User Profile -->
                        <div class="flex items-center gap-2">
                            <div
                                class="h-10 w-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-semibold">
                                JD
                            </div>
                            <span
                                class="font-medium hidden sm:block"><?php echo htmlspecialchars($user_email); ?></span>
                        </div>
                    </div>
                </div>
            </header>


            <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm">
                

                <!-- Dashboard Summary -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'vehicles' ? 'ring-2 ring-blue-500' : ''; ?>">
                        <h3 class="font-bold mb-2">Vehicles</h3>
                        <p class="text-3xl font-bold"><?php echo $vehicle_count; ?></p>
                    </div>
                    <div
                        class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'drivers' ? 'ring-2 ring-blue-500' : ''; ?>">
                        <h3 class="font-bold mb-2">Drivers</h3>
                        <p class="text-3xl font-bold"><?php echo $driver_count; ?></p>
                    </div>
                    <div
                        class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'maintenance' ? 'ring-2 ring-blue-500' : ''; ?>">
                        <h3 class="font-bold mb-2">Maintenance</h3>
                        <p class="text-3xl font-bold"><?php echo $maintenance_count; ?></p>
                    </div>
                    <div
                        class="bg-black/20 p-4 rounded-lg <?php echo $active_tab == 'fuel' ? 'ring-2 ring-blue-500' : ''; ?>">
                        <h3 class="font-bold mb-2">Fuel Transactions</h3>
                        <p class="text-3xl font-bold"><?php echo $fuel_count; ?></p>
                    </div>
                </div> -->

                <!-- Tabs Navigation -->
                <!-- <div class="border-b border-gray-700 mb-6">
                    <nav class="flex -mb-px">
                        <a href="?tab=vehicles"
                            class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'vehicles' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                            Vehicles
                        </a>
                        <a href="?tab=drivers"
                            class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'drivers' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                            Drivers
                        </a>
                        <a href="?tab=maintenance"
                            class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'maintenance' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                            Maintenance
                        </a>
                        <a href="?tab=fuel"
                            class="py-2 px-4 text-center border-b-2 <?php echo $active_tab == 'fuel' ? 'border-blue-500 text-blue-500' : 'border-transparent hover:border-gray-300'; ?> font-medium">
                            Fuel Transactions
                        </a>
                    </nav>
                </div> -->

                <!-- Tab Content -->
                <div class="mb-6">
                    <?php if ($active_tab == 'vehicles'): ?>
                        <!-- Vehicles Tab -->
                        <div id="vehicles-content" class="page-content">
                            <div class="bg-white rounded-lg shadow mb-6">
                                <div class="p-6 flex flex-wrap items-center justify-between gap-4">
                                    <a href="vehicle_form.php" id="add-vehicle-btn-2"
                                        class="px-4 py-2 bg-purple-600 rounded-lg font-medium flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Add Vehicle
                                    </a>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Vehicle ID</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Make/Model</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Year</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    License Plate</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Status</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Assigned Driver</th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vehicles-table-body" class="bg-white divide-y divide-gray-200">
                                            <?php if (empty($vehicles)): ?>
                                                <tr>
                                                    <td colspan="7" class="p-6 text-center text-gray-500">
                                                        No vehicles found. Add a vehicle to get started.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($vehicles as $vehicle): ?>
                                                    <tr>
                                                        <td class="px-6 py-4"><?php echo $vehicle['vehicle_id']; ?></td>
                                                        <td class="px-6 py-4"><?php echo htmlspecialchars($vehicle['model']); ?>
                                                        </td>
                                                        <td class="px-6 py-4"><?php echo $vehicle['year']; ?></td>
                                                        <td class="px-6 py-4">
                                                            <?php echo htmlspecialchars($vehicle['licence_no']); ?>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <span class="px-2 py-1 rounded text-xs 
                                                <?php
                                                if ($vehicle['status'] == 'Active')
                                                    echo 'bg-green-800 text-black';
                                                elseif ($vehicle['status'] == 'Inactive')
                                                    echo 'bg-red-800 text-black';
                                                else
                                                    echo 'bg-yellow-800 text-black';
                                                ?>">
                                                                <?php echo $vehicle['status']; ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4"><?php echo $vehicle['assigned_driver'] ?? 'None'; ?>
                                                        </td>
                                                        <td class="px-6 py-4">
                                                            <div class="flex space-x-2">
                                                                <a href="vehicle_form.php?id=<?php echo $vehicle['vehicle_id']; ?>"
                                                                    class="text-blue-400 hover:text-blue-300">Edit</a>
                                                                <a href="vehicle_delete.php?id=<?php echo $vehicle['vehicle_id']; ?>"
                                                                    class="text-red-400 hover:text-red-300"
                                                                    onclick="return confirm('Are you sure you want to delete this vehicle?')">Delete</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                    <?php elseif ($active_tab == 'drivers'): ?>
                        <!-- Drivers Tab -->
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Drivers</h2>
                            <a href="driver_form.php" class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded">Add
                                Driver</a>
                        </div>

                        <?php if (empty($drivers)): ?>
                            <div class="bg-black/20 p-6 rounded-lg text-center">
                                <p>No drivers found. Add your first driver to get started.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">

                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Name</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Contact
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                License
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Vehicle
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Hire Date
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Salary
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
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
                                                        if ($driver['status'] == 'Available')
                                                            echo 'bg-green-800';
                                                        elseif ($driver['status'] == 'Hired')
                                                            echo 'bg-blue-800';
                                                        else
                                                            echo 'bg-red-800';
                                                        ?>">
                                                        <?php echo $driver['status']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <?php echo $driver['vehicle_model'] ? htmlspecialchars($driver['vehicle_model']) : 'None'; ?>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <?php echo $driver['hire_date'] ? $driver['hire_date'] : 'N/A'; ?>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <?php echo $driver['salary'] ? '$' . number_format($driver['salary'], 2) : 'N/A'; ?>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="flex space-x-2">
                                                        <a href="driver_form.php?id=<?php echo $driver['driver_id']; ?>"
                                                            class="text-blue-400 hover:text-blue-300">Edit</a>
                                                        <a href="driver_delete.php?id=<?php echo $driver['driver_id']; ?>"
                                                            class="text-red-400 hover:text-red-300"
                                                            onclick="return confirm('Are you sure you want to delete this driver?')">Delete</a>
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
                            <a href="maintenance_form.php"
                                class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded">Add Maintenance</a>
                        </div>

                        <?php if (empty($maintenance)): ?>
                            <div class="bg-black/20 p-6 rounded-lg text-center">
                                <p>No maintenance records found. Add your first maintenance record to get started.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Vehicle
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Service Type
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cost
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
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
                                                    <span
                                                        class="px-2 py-1 rounded text-xs 
                                                        <?php echo $record['status'] == 'Completed' ? 'bg-green-800' : 'bg-yellow-800'; ?>">
                                                        <?php echo $record['status']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">$<?php echo number_format($record['cost'], 2); ?></td>
                                                <td class="px-4 py-2">
                                                    <div class="flex space-x-2">
                                                        <a href="maintenance_form.php?id=<?php echo $record['maintainance_id']; ?>"
                                                            class="text-blue-400 hover:text-blue-300">Edit</a>
                                                        <a href="maintenance_delete.php?id=<?php echo $record['maintainance_id']; ?>"
                                                            class="text-red-400 hover:text-red-300"
                                                            onclick="return confirm('Are you sure you want to delete this maintenance record?')">Delete</a>
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
                            <a href="fuel_form.php" class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded">Add
                                Fuel
                                Transaction</a>
                        </div>

                        <?php if (empty($fuel_transactions)): ?>
                            <div class="bg-black/20 p-6 rounded-lg text-center">
                                <p>No fuel transactions found. Add your first fuel transaction to get started.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Vehicle
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Driver
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fuel Type
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cost
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fuel_transactions as $transaction): ?>
                                            <tr class="border-t border-gray-700">
                                                <td class="px-4 py-2"><?php echo $transaction['transaction_id']; ?></td>
                                                <td class="px-4 py-2"><?php echo $transaction['date']; ?></td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['vehicle_model']); ?>
                                                </td>
                                                <td class="px-4 py-2"><?php echo htmlspecialchars($transaction['driver_name']); ?>
                                                </td>
                                                <td class="px-4 py-2"><?php echo $transaction['fuel_type']; ?></td>
                                                <td class="px-4 py-2"><?php echo number_format($transaction['amount'], 2); ?> L</td>
                                                <td class="px-4 py-2">$<?php echo number_format($transaction['cost'], 2); ?></td>
                                                <td class="px-4 py-2">
                                                    <div class="flex space-x-2">
                                                        <a href="fuel_form.php?id=<?php echo $transaction['transaction_id']; ?>"
                                                            class="text-blue-400 hover:text-blue-300">Edit</a>
                                                        <a href="fuel_delete.php?id=<?php echo $transaction['transaction_id']; ?>"
                                                            class="text-red-400 hover:text-red-300"
                                                            onclick="return confirm('Are you sure you want to delete this fuel transaction?')">Delete</a>
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
    </div>
</body>

</html>