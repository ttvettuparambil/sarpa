<?php

require 'dbConnection.php';

// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - SARPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // Using Tailwind's default blue palette
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
    
    <main class="flex-grow flex items-center justify-center px-4">
        <div class="text-center max-w-md mx-auto">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-blue-600 dark:text-blue-400 mb-4">404</h1>
                <h2 class="text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white mb-4">Page Not Found</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-8">
                    The page you're looking for doesn't exist.
                </p>
            </div>
            
            <div class="space-y-4">
                <a href="index.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300 transform hover:-translate-y-1">
                    Go Home
                </a>
                <div>
                    <a href="user-dashboard.php" class="inline-block px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg transition duration-300 ml-4">
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
