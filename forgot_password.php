<?php
session_start();
include 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);


$resetLink = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Get user by email
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id);
        mysqli_stmt_fetch($stmt);

        $token = bin2hex(random_bytes(16));
        $expires_at = time() + 3600; // valid for 1 hour

        // Insert token into password_resets table
        $stmt2 = mysqli_prepare($conn, "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "isi", $user_id, $token, $expires_at);
        mysqli_stmt_execute($stmt2);

        // Generate reset link
        $resetLink = "/reset_password.php?token=$token";
    } else {
        $message = "No user found with that email.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SARPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF'
                    }
                }
            }
        }
        
        // Check for dark mode preference in localStorage
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Forgot Password</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Remember your password? <a href="login.php" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">Sign in</a>
                </p>
            </div>
            
            <?php if ($resetLink || $message): ?>
            <div class="rounded-md p-4 <?php echo $resetLink ? 'bg-green-50 dark:bg-green-900/30' : 'bg-red-50 dark:bg-red-900/30'; ?>">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <?php if ($resetLink): ?>
                        <i class="ri-checkbox-circle-line text-green-400 text-xl"></i>
                        <?php else: ?>
                        <i class="ri-error-warning-line text-red-400 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium <?php echo $resetLink ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'; ?>">
                            <?php if ($resetLink): ?>
                                Password reset link has been generated (valid for 1 hour):
                                <a href="<?= $resetLink ?>" class="underline"><?= $resetLink ?></a>
                            <?php else: ?>
                                <?= $message ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" method="POST">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                   placeholder="Enter your email address">
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            Send Reset Link
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
        // Toggle dark mode
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('darkMode', 'false');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('darkMode', 'true');
            }
        }
    </script>
</body>
</html>
