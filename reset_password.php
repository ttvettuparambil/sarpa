<?php
session_start();
include 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);


if (!isset($_GET['token']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}


$token = $_GET['token'] ?? '';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ?");
    $now = time();
    mysqli_stmt_bind_param($stmt, "si", $token, $now);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id);
        mysqli_stmt_fetch($stmt);

        // Update user's password
        $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $new_password, $user_id);
        mysqli_stmt_execute($update);

        // Delete used token
        $delete = mysqli_prepare($conn, "DELETE FROM password_resets WHERE token = ?");
        mysqli_stmt_bind_param($delete, "s", $token);
        mysqli_stmt_execute($delete);

        $success = true;
    } else {
        $error = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SARPA</title>
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
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Reset Password</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter your new password below
                </p>
            </div>
            
            <?php if ($success): ?>
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-checkbox-circle-line text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            Your password has been successfully updated.
                        </p>
                        <div class="mt-4">
                            <div class="-mx-2 -my-1.5 flex">
                                <a href="login.php" class="bg-green-50 dark:bg-green-900/50 px-2 py-1.5 rounded-md text-sm font-medium text-green-800 dark:text-green-200 hover:bg-green-100 dark:hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Go to login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php if ($error): ?>
                <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="ri-error-warning-line text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                <?= $error ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <form class="space-y-6" method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                New Password
                            </label>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" required 
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                                       placeholder="Enter your new password">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
