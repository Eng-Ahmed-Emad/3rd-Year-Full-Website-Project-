<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRÈS CHIC | Login</title>
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
        // Basic validation
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = "All fields are required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            try {
                // Prepare and execute the query
                $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['logged_in'] = true;
                    $_SESSION['role'] = $user['role'];

                    // Set admin session if user is admin
                    if ($user['role'] === 'admin') {
                        $_SESSION['admin_logged_in'] = true;
                        header("Location: admin.php");
                    } else {
                        header("Location: Home.php");
                    }
                    exit();
                } else {
                    $error = "Invalid email or password";
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
            <div onclick="toggleCartPanel()" class="cursor-pointer">🛒</div>
            <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200">
                <span id="darkModeIcon" class="text-lg">🌙</span>
            </button>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 dark:text-white">Welcome Back</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 dark:bg-red-900 dark:border-red-700 dark:text-red-200" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" required
                        class="form-input w-full">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="form-input w-full">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" 
                            class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded dark:border-gray-600 dark:focus:ring-white">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-black hover:text-gray-700 dark:text-white dark:hover:text-gray-300">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-black text-white py-3 px-4 rounded-md hover:bg-gray-800 transition-colors duration-200 dark:bg-white dark:text-black dark:hover:bg-gray-200">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account? 
                    <a href="register.php" class="text-black dark:text-white hover:underline">Register here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html> 