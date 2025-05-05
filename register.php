<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRÈS CHIC | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="./darkMode.css" rel="stylesheet">
    <script src="./darkMode.js"></script>
    <style>
        body {
            font-family: 'Georgia', serif;
        }
        .form-input {
            background-color: transparent;
            border-bottom: 1px solid #e5e5e5;
            padding: 8px 0;
            transition: border-color 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: #000;
        }
        .dark .form-input {
            border-color: #333;
            color: #ffffff;
        }
        .dark .form-input:focus {
            border-color: #ffffff;
        }
        .dark .form-input::placeholder {
            color: #666;
        }
    </style>
</head>
<body class="min-h-screen bg-white dark:bg-gray-900">
    <?php
    session_start();
    require_once 'config.php';
    
    $error = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and trim all form data
        $username = trim($_POST['username']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone_number = trim($_POST['phone_number']);
        $address = trim($_POST['address']);

        // Validate all required fields
        if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || 
            empty($password) || empty($confirm_password) || empty($phone_number) || empty($address)) {
            $error = "All fields are required";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } elseif (!preg_match("/^[0-9]{10,15}$/", $phone_number)) {
            $error = "Invalid phone number format";
        } else {
            try {
                // Check if username or email already exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Username or email already exists";
                } else {
                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, email, password_hash, phone_number, address, role) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, 'user')");
                    $stmt->execute([
                        $username,
                        $first_name,
                        $last_name,
                        $email,
                        $password_hash,
                        $phone_number,
                        $address
                    ]);
                    
                    // Get the new user's ID
                    $user_id = $pdo->lastInsertId();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['role'] = 'user';
                    
                    // Redirect to login page
                    header("Location: login.php");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Database error occurred. Please try again later.";
            }
        }
    }
    ?>

    <!-- Navbar -->
    <header class="flex justify-between items-center px-4 py-3 border-b transition-colors duration-200">
        <div class="flex items-center gap-4">
            <div class="text-3xl font-bold tracking-wider">TRÈS CHIC</div>
        </div>
        <div class="flex items-center gap-6 text-base">
            <div class="cursor-pointer" onclick="toggleContactPanel()">Contactez-nous</div>
            <div><a href="login.php">🔑</a></div>
            <div onclick="toggleCartPanel()" class="cursor-pointer">🛒</div>
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                <span id="darkModeIcon" class="text-lg">🌙</span>
            </button>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 dark:text-white">Create Your Account</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 dark:bg-red-900 dark:border-red-700 dark:text-red-200" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                        <input type="text" id="first_name" name="first_name" required
                            class="form-input w-full">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required
                            class="form-input w-full">
                    </div>
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input type="text" id="username" name="username" required
                        class="form-input w-full">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" required
                        class="form-input w-full">
                </div>

                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" required
                        class="form-input w-full">
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <input type="text" id="address" name="address" required
                        class="form-input w-full">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" id="password" name="password" required
                            class="form-input w-full">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                            class="form-input w-full">
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-black text-white py-3 px-4 rounded-md hover:bg-gray-800 transition-colors duration-200 dark:bg-white dark:text-black dark:hover:bg-gray-200">
                    Create Account
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Already have an account? 
                    <a href="login.php" class="text-black dark:text-white hover:underline">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html> 