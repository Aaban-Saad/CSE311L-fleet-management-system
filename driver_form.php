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

// Check if tables exist and create them if they don't
ensure_tables_exist($user_conn);

$driver = [
  'driver_id' => '',
  'name' => '',
  'contact' => '',
  'licence' => '',
  'status' => 'Available',
  'vehicle_id' => null,
  'hire_date' => '',
  'salary' => ''
];

$is_edit = false;
$error = '';
$success = '';

// Get all vehicles for dropdown
$vehicles = get_vehicles($user_conn);

// Check if editing existing driver
if (isset($_GET['id'])) {
  $driver_id = (int)$_GET['id'];
  $driver_data = get_driver_by_id($user_conn, $driver_id);
  
  if ($driver_data) {
      $driver = $driver_data;
      $is_edit = true;
  } else {
      $error = "Driver not found";
  }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize input
  $name = sanitize_input($_POST['name']);
  $contact = sanitize_input($_POST['contact']);
  $licence = sanitize_input($_POST['licence']);
  $status = sanitize_input($_POST['status']);
  $vehicle_id = !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
  $hire_date = !empty($_POST['hire_date']) ? sanitize_input($_POST['hire_date']) : null;
  $salary = !empty($_POST['salary']) ? (float)$_POST['salary'] : null;
  
  // Validate input
  if (empty($name) || empty($contact) || empty($licence) || empty($status)) {
      $error = "Please fill in all required fields";
  } else {
      if ($is_edit) {
          // Update existing driver
          try {
              $stmt = $user_conn->prepare("UPDATE Drivers SET name = ?, contact = ?, licence = ?, status = ?, vehicle_id = ?, hire_date = ?, salary = ? WHERE driver_id = ?");
              
              // If vehicle_id is null, bind it as NULL
              if ($vehicle_id === null) {
                  $stmt->bind_param("ssssiidi", $name, $contact, $licence, $status, $null, $hire_date, $salary, $driver['driver_id']);
                  $null = NULL;
              } else {
                  $stmt->bind_param("ssssisdi", $name, $contact, $licence, $status, $vehicle_id, $hire_date, $salary, $driver['driver_id']);
              }
              
              if ($stmt->execute()) {
                  $success = "Driver updated successfully";
                  // Refresh driver data
                  $driver = get_driver_by_id($user_conn, $driver['driver_id']);
              } else {
                  $error = "Error updating driver: " . $user_conn->error;
              }
          } catch (Exception $e) {
              $error = "Error updating driver: " . $e->getMessage();
          }
      } else {
          // Insert new driver
          try {
              // Prepare the SQL statement
              $sql = "INSERT INTO Drivers (name, contact, licence, status";
              $types = "ssss"; // Initial parameter types
              $params = [$name, $contact, $licence, $status]; // Initial parameters
              
              // Add optional fields if they exist
              if ($vehicle_id !== null) {
                  $sql .= ", vehicle_id";
                  $types .= "i";
                  $params[] = $vehicle_id;
              }
              
              if ($hire_date !== null) {
                  $sql .= ", hire_date";
                  $types .= "s";
                  $params[] = $hire_date;
              }
              
              if ($salary !== null) {
                  $sql .= ", salary";
                  $types .= "d";
                  $params[] = $salary;
              }
              
              $sql .= ") VALUES (?" . str_repeat(", ?", count($params) - 1) . ")";
              
              $stmt = $user_conn->prepare($sql);
              if (!$stmt) {
                  throw new Exception("Prepare failed: " . $user_conn->error);
              }
              
              // Dynamically bind parameters
              $stmt->bind_param($types, ...$params);
              
              $exec_result = $stmt->execute();
              if (!$exec_result) {
                  throw new Exception("Execute failed: " . $stmt->error);
              }
              
              header("Location: dashboard.php?tab=drivers");
              exit;
          } catch (Exception $e) {
              $error = "Error adding driver: " . $e->getMessage();
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
  <title>FleetOpz | <?php echo $is_edit ? 'Edit' : 'Add'; ?> Driver</title>
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
          <a href="dashboard.php?tab=drivers" class="text-sm bg-gray-600 px-3 py-1 rounded hover:bg-gray-700">Back to Drivers</a>
      </div>
  </nav>

  <main class="container mx-auto p-6">
      <div class="bg-white/10 rounded-lg p-6 backdrop-blur-sm max-w-2xl mx-auto">
          <h1 class="text-2xl font-bold mb-4"><?php echo $is_edit ? 'Edit' : 'Add'; ?> Driver</h1>
          
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
          
          <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . ($is_edit ? "?id=" . $driver['driver_id'] : "")); ?>" class="space-y-4">
              <div>
                  <label for="name" class="block mb-1">Name *</label>
                  <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($driver['name']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
              </div>
              
              <div>
                  <label for="contact" class="block mb-1">Contact *</label>
                  <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($driver['contact']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
              </div>
              
              <div>
                  <label for="licence" class="block mb-1">License *</label>
                  <input type="text" id="licence" name="licence" value="<?php echo htmlspecialchars($driver['licence']); ?>" class="w-full p-2 rounded bg-black/30 text-white" required>
              </div>
              
              <div>
                  <label for="status" class="block mb-1">Status *</label>
                  <select id="status" name="status" class="w-full p-2 rounded bg-black/30 text-white" required>
                      <option value="Available" <?php echo $driver['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                      <option value="Hired" <?php echo $driver['status'] == 'Hired' ? 'selected' : ''; ?>>Hired</option>
                      <option value="Inactive" <?php echo $driver['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                  </select>
              </div>
              
              <div>
                  <label for="vehicle_id" class="block mb-1">Assigned Vehicle</label>
                  <select id="vehicle_id" name="vehicle_id" class="w-full p-2 rounded bg-black/30 text-white">
                      <option value="">None</option>
                      <?php foreach ($vehicles as $vehicle): ?>
                          <option value="<?php echo $vehicle['vehicle_id']; ?>" <?php echo $driver['vehicle_id'] == $vehicle['vehicle_id'] ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($vehicle['model'] . ' (' . $vehicle['licence_no'] . ')'); ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
              
              <div>
                  <label for="hire_date" class="block mb-1">Hire Date</label>
                  <input type="date" id="hire_date" name="hire_date" value="<?php echo $driver['hire_date']; ?>" max="<?php echo date('Y-m-d'); ?>" class="w-full p-2 rounded bg-black/30 text-white">
                  <p class="text-xs text-gray-400 mt-1">Please select a date not in the future</p>
              </div>
              
              <div>
                  <label for="salary" class="block mb-1">Salary</label>
                  <input type="number" id="salary" name="salary" value="<?php echo $driver['salary']; ?>" step="0.01" min="0" class="w-full p-2 rounded bg-black/30 text-white">
              </div>
              
              <div class="pt-4">
                  <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                      <?php echo $is_edit ? 'Update Driver' : 'Add Driver'; ?>
                  </button>
                  <a href="dashboard.php?tab=drivers" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded ml-2">Cancel</a>
              </div>
          </form>
      </div>
  </main>
</body>

</html>


