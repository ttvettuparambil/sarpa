<?php
include 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);


$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $latitude = !empty($_POST['latitude']) ? htmlspecialchars(trim($_POST['latitude'])) : 0;
    $longitude = !empty($_POST['longitude']) ? htmlspecialchars(trim($_POST['longitude'])) : 0;   

    // Use procedural MySQLi
    $sql = "INSERT INTO users (role, first_name, last_name, address, email, password, latitude, longitude) 
            VALUES ('user', ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssd", $first_name, $last_name, $address, $email, $password, $latitude, $longitude);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => "Registration successful. <a href='login.php' class='text-blue-600 dark:text-blue-400 hover:text-blue-500'>Login here</a>."
        ];
        header("Location: login.php");
        exit;
    } else {
        $message = "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SARPA</title>
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

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById("latitude").value = position.coords.latitude;
                    document.getElementById("longitude").value = position.coords.longitude;
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200" onload="getLocation()">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Create your account</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Or <a href="login.php" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">sign in to your existing account</a>
                </p>
            </div>
            
            <?php include 'components/alerts.php'; ?>
            
            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" method="POST">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                First Name
                            </label>
                            <div class="mt-1">
                                <input id="first_name" name="first_name" type="text" required 
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                              dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Last Name
                            </label>
                            <div class="mt-1">
                                <input id="last_name" name="last_name" type="text" required 
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                              dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Address
                        </label>
                        <div class="mt-1">
                            <textarea id="address" name="address" rows="3" required 
                                      class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                             focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                             dark:bg-gray-700 dark:text-white sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            Register
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                Or register as
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <a href="partner-register.php" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Register as Partner
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
