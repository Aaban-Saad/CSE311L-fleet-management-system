<?php
require_once 'functions.php';

$error = '';

// Check if user is already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Attempt to login
        if (login_user($email, $password)) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password";
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
    <title>FleetOpz | Login</title>
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
                <a href="index.php" class="text-black hover:cursor-pointer">Login</a>
                <a href="signup.php" class="text-black/50 hover:cursor-pointer">Signup</a>
            </div>
            <div class="mt-8">
                <h2 class="font-bold">Welcome</h2>
                <p class="text-xs text-black/50">Please enter your credentials</p>
            </div>
            <?php if (!empty($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-xs">
                    <?php echo $error; ?>
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
                    <a href="#" class="text-xs">Forgot Password</a>
                </div>

                <button type="submit" class="w-full bg-black text-white p-2 rounded-md hover:cursor-pointer">Login</button>
            </form>
        </div>
    </main>
</body>

</html>

