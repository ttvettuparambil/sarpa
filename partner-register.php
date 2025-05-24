<?php
session_start();
include 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);

// Initialize form data variables
$first_name = $last_name = $email = $district = $phone = '';
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $district = htmlspecialchars(trim($_POST['district']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    
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
            // Hash the password
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            
            // Insert into users table
            $sql_user = "INSERT INTO users (role, first_name, last_name, district, email, phone, password, created_at) 
                    VALUES ('partner', ?, ?, ?, ?, ?, ?, NOW())";
            $stmt_user = mysqli_prepare($conn, $sql_user);
            
            if (!$stmt_user) {
            // Handle preparation error
            error_log("Partner registration error: " . mysqli_error($conn));
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => "Registration failed. Please try again."
            ];
            $form_error = true;
        } else {
            // Bind parameters and execute
            mysqli_stmt_bind_param($stmt_user, "ssssss", $first_name, $last_name, $district, $email, $phone, $password);
            
            if (mysqli_stmt_execute($stmt_user)) {
                // Success
                $_SESSION['alert'] = [
                    'type' => 'success',
                    'message' => "Registration successful. <a href='login.php' class='text-blue-600 dark:text-blue-400 hover:text-blue-500'>Login here</a>."
                ];
                mysqli_stmt_close($stmt_user);
                header("Location: login.php");
                exit;
            } else {
                // Execution error
                error_log("Partner registration error: " . mysqli_stmt_error($stmt_user));
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Registration failed. Please try again."
                ];
                $form_error = true;
            }
            mysqli_stmt_close($stmt_user);
        }
    }
}
            // Error handling code removed as it's now handled in the main logic
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Registration - SARPA</title>
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
    
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Register as Partner</h2>
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
                                       value="<?php echo htmlspecialchars($first_name); ?>"
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
                                       value="<?php echo htmlspecialchars($last_name); ?>"
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
                                   value="<?php echo htmlspecialchars($email); ?>"
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
                            <input id="phone" name="phone" type="tel" required 
                                   value="<?php echo htmlspecialchars($phone); ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            District
                        </label>
                        <div class="mt-1">
                            <select id="district" name="district" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                                <option value="" <?php echo empty($district) ? 'selected' : ''; ?>>Select a district</option>
                                <option value="Alappuzha" <?php echo $district === 'Alappuzha' ? 'selected' : ''; ?>>Alappuzha</option>
                                <option value="Ernakulam" <?php echo $district === 'Ernakulam' ? 'selected' : ''; ?>>Ernakulam</option>
                                <option value="Idukki" <?php echo $district === 'Idukki' ? 'selected' : ''; ?>>Idukki</option>
                                <option value="Kannur" <?php echo $district === 'Kannur' ? 'selected' : ''; ?>>Kannur</option>
                                <option value="Kasaragod" <?php echo $district === 'Kasaragod' ? 'selected' : ''; ?>>Kasaragod</option>
                                <option value="Kollam" <?php echo $district === 'Kollam' ? 'selected' : ''; ?>>Kollam</option>
                                <option value="Kottayam" <?php echo $district === 'Kottayam' ? 'selected' : ''; ?>>Kottayam</option>
                                <option value="Kozhikode" <?php echo $district === 'Kozhikode' ? 'selected' : ''; ?>>Kozhikode</option>
                                <option value="Malappuram" <?php echo $district === 'Malappuram' ? 'selected' : ''; ?>>Malappuram</option>
                                <option value="Palakkad" <?php echo $district === 'Palakkad' ? 'selected' : ''; ?>>Palakkad</option>
                                <option value="Pathanamthitta">Pathanamthitta</option>
                                <option value="Thiruvananthapuram">Thiruvananthapuram</option>
                                <option value="Thrissur">Thrissur</option>
                                <option value="Wayanad">Wayanad</option>
                            </select>
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

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            Register as Partner
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
                        <a href="register.php" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Register as User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
