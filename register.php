<?php
session_start();
include 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);

// Initialize variables to store form data
$first_name = $last_name = $email = $district = $city = $postcode = $phone = $address_line1 = $address_line2 = $landmark = '';
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data in variables
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $district = htmlspecialchars(trim($_POST['district']));
    $city = htmlspecialchars(trim($_POST['city']));
    $postcode = htmlspecialchars(trim($_POST['postcode']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address_line1 = htmlspecialchars(trim($_POST['address_line1']));
    $address_line2 = isset($_POST['address_line2']) ? htmlspecialchars(trim($_POST['address_line2'])) : '';
    $landmark = isset($_POST['landmark']) ? htmlspecialchars(trim($_POST['landmark'])) : '';
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'This email is already registered. Please use a different email or <a href="login.php" class="text-blue-600 dark:text-blue-400 hover:text-blue-500">login here</a>.'
        ];
        mysqli_stmt_close($check_stmt);
        $form_error = true;
    } else {
        mysqli_stmt_close($check_stmt);
    
        // Only proceed with registration if there are no form errors
        if (!$form_error) {
            // Prepare the SQL statement
            $sql = "INSERT INTO users (role, first_name, last_name, email, password, district, city, postcode, phone, address_line1, address_line2, landmark, created_at) 
                    VALUES ('user', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssssss", $first_name, $last_name, $email, $password, $district, $city, $postcode, $phone, $address_line1, $address_line2, $landmark);

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Registration successful. <a href='login.php' class='text-blue-600 dark:text-blue-400 hover:text-blue-500'>Login here</a>."
                    ];
                    mysqli_stmt_close($stmt);
                    header("Location: login.php");
                    exit;
                } else {
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => "Error: " . mysqli_error($conn)
                    ];
                    mysqli_stmt_close($stmt);
                    $form_error = true;
                }
            } // Close if (!$form_error)
        } // Close else after email check
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
                                <input id="first_name" name="first_name" type="text" required value="<?php echo htmlspecialchars($first_name); ?>"
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
                                <input id="last_name" name="last_name" type="text" required value="<?php echo htmlspecialchars($last_name); ?>"
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
                            <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo htmlspecialchars($email); ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                District
                            </label>
                            <div class="mt-1">
                                <select id="district" name="district" required 
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                           focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                           dark:bg-gray-700 dark:text-white sm:text-sm">
                                    <option value="" disabled <?php echo empty($district) ? 'selected' : ''; ?>>Select District</option>
                                    <option value="Thiruvananthapuram" <?php echo $district === 'Thiruvananthapuram' ? 'selected' : ''; ?>>Thiruvananthapuram</option>
                                    <option value="Kollam" <?php echo $district === 'Kollam' ? 'selected' : ''; ?>>Kollam</option>
                                    <option value="Pathanamthitta" <?php echo $district === 'Pathanamthitta' ? 'selected' : ''; ?>>Pathanamthitta</option>
                                    <option value="Alappuzha" <?php echo $district === 'Alappuzha' ? 'selected' : ''; ?>>Alappuzha</option>
                                    <option value="Kottayam" <?php echo $district === 'Kottayam' ? 'selected' : ''; ?>>Kottayam</option>
                                    <option value="Idukki" <?php echo $district === 'Idukki' ? 'selected' : ''; ?>>Idukki</option>
                                    <option value="Ernakulam" <?php echo $district === 'Ernakulam' ? 'selected' : ''; ?>>Ernakulam</option>
                                    <option value="Thrissur" <?php echo $district === 'Thrissur' ? 'selected' : ''; ?>>Thrissur</option>
                                    <option value="Palakkad" <?php echo $district === 'Palakkad' ? 'selected' : ''; ?>>Palakkad</option>
                                    <option value="Malappuram" <?php echo $district === 'Malappuram' ? 'selected' : ''; ?>>Malappuram</option>
                                    <option value="Kozhikode" <?php echo $district === 'Kozhikode' ? 'selected' : ''; ?>>Kozhikode</option>
                                    <option value="Wayanad" <?php echo $district === 'Wayanad' ? 'selected' : ''; ?>>Wayanad</option>
                                    <option value="Kannur" <?php echo $district === 'Kannur' ? 'selected' : ''; ?>>Kannur</option>
                                    <option value="Kasaragod" <?php echo $district === 'Kasaragod' ? 'selected' : ''; ?>>Kasaragod</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                City
                            </label>
                            <div class="mt-1">
                                <input id="city" name="city" type="text" required value="<?php echo htmlspecialchars($city); ?>"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                              dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Postcode
                            </label>
                            <div class="mt-1">
                                <input id="postcode" name="postcode" type="text" required value="<?php echo htmlspecialchars($postcode); ?>"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                              dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone Number
                            </label>
                            <div class="mt-1">
                                <input id="phone" name="phone" type="tel" required value="<?php echo htmlspecialchars($phone); ?>"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                              focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                              dark:bg-gray-700 dark:text-white sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="address_line1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Address Line 1
                        </label>
                        <div class="mt-1">
                            <input id="address_line1" name="address_line1" type="text" required value="<?php echo htmlspecialchars($address_line1); ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="address_line2" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Address Line 2 (Optional)
                        </label>
                        <div class="mt-1">
                            <input id="address_line2" name="address_line2" type="text" value="<?php echo htmlspecialchars($address_line2); ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="landmark" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Landmark (Optional)
                        </label>
                        <div class="mt-1">
                            <input id="landmark" name="landmark" type="text" value="<?php echo htmlspecialchars($landmark); ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
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
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        By registering, you agree to our <a href="terms.php" class="text-blue-600 dark:text-blue-400 hover:underline">Terms of Service</a> and 
                        <a href="privacy.php" class="text-blue-600 dark:text-blue-400 hover:underline">Privacy Policy</a>.
                    </div>

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
