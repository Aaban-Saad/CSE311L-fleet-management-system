<?php
require_once 'functions.php';

$error = '';
$success = '';

// Check if user is already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

// Process signup form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    
    // Validate input
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Register user
            if (register_user($email, $password)) {
                $success = "Registration successful! You can now login. If you encounter any issues, please contact support.";
            } else {
                $error = "Registration failed. Please try again or contact support.";
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
    <title>FleetOpz | Signup</title>
    <style>
        body {
            background-color: rgb(24, 28, 31);
            background-image: url('public/bg.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>

<body>
    <nav class="p-10 flex items-center justify-between text-gray-200">
        <a href="index.php" class="flex items-center justify-center gap-2 font-bold">
            <img src="public/logo.png" height="18px" width="18px" alt="logo">
            FleetOpz
        </a>
    </nav>

    <main class="flex items-center justify-center">
        <div class="mt-12 bg-white w-[350px] rounded-2xl p-10 shadow-2xl">
            <div class="flex justify-evenly font-bold text-sm">
                <a href="index.php" class="text-black/50 hover:cursor-pointer">Login</a>
                <a href="signup.php" class="text-black hover:cursor-pointer">Signup</a>
            </div>
            <div class="mt-8">
                <h2 class="font-bold">Create Account</h2>
                <p class="text-xs text-black/50">Please fill in your details</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-xs">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-xs">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mt-5 space-y-5">
                <div>
                    <label for="email" class="text-xs">Email</label>
                    <input id="email" name="email" type="email" placeholder="Email" class="border-1 rounded-md py-2 px-3 w-full">
                </div>

                <div>
                    <label for="password" class="text-xs">Password</label>
                    <input id="password" name="password" type="password" placeholder="Password" class="border-1 rounded-md py-2 px-3 w-full">
                </div>

                <div>
                    <label for="confirm_password" class="text-xs">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm Password" class="border-1 rounded-md py-2 px-3 w-full">
                </div>

                <button type="submit" class="w-full bg-black text-white p-2 rounded-md hover:cursor-pointer">Sign Up</button>
            </form>
        </div>
    </main>
</body>

</html>


