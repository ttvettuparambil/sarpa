<?php
session_start();
include 'dbConnection.php';

if (isset($_SESSION['role']) && $_SESSION['role'] == 'user') {
    header("Location: user-dashboard.php");
} elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'partner') {
    header("Location: partner-dashboard.php");
} elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'super_admin') {
    header("Location: admin-dashboard.php");
}

// Clean up expired lockouts from the database
$cleanupLocksStmt = mysqli_prepare($conn, "UPDATE login_attempts SET unlock_time = NULL WHERE unlock_time <= NOW()");
mysqli_stmt_execute($cleanupLocksStmt);
mysqli_stmt_close($cleanupLocksStmt);

// Check if the user was logged out due to inactivity
$timeout_message = '';
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $timeout_message = "You were automatically logged out due to inactivity. Please log in again.";
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => $timeout_message
    ];
}

// Set up initial message variable
$message = "";

// Handle POST request for login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password_input = $_POST['password'];
    $ip = $_SERVER['REMOTE_ADDR'];

    // Step 1: Check if the user is currently locked out
    $checkLockoutStmt = mysqli_prepare($conn, 
        "SELECT unlock_time FROM login_attempts 
         WHERE email = ? AND ip_address = ? AND unlock_time > NOW() 
         ORDER BY unlock_time DESC LIMIT 1");
    mysqli_stmt_bind_param($checkLockoutStmt, "ss", $email, $ip);
    mysqli_stmt_execute($checkLockoutStmt);
    mysqli_stmt_store_result($checkLockoutStmt);
    
    // If there's an active lockout
    if (mysqli_stmt_num_rows($checkLockoutStmt) > 0) {
        mysqli_stmt_bind_result($checkLockoutStmt, $db_unlock_time);
        mysqli_stmt_fetch($checkLockoutStmt);
        mysqli_stmt_close($checkLockoutStmt);
        
        $formatted_time = date('h:i:s A', strtotime($db_unlock_time));
        
        $unlock_time = strtotime($db_unlock_time);
        $wait_seconds = max(1, $unlock_time - time());
        
        // Set the lockout message with the formatted time
        $message = "Too many failed login attempts. Please try again after $formatted_time.";
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => $message
        ];
        
        // Set a meta refresh tag to reload the page when the lockout expires
        $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
    } else {
        // Close the lockout check statement before proceeding
        mysqli_stmt_close($checkLockoutStmt);
        
        // Check for recent failed login attempts BEFORE proceeding with validation
        $checkAttemptsStmt = mysqli_prepare($conn, 
            "SELECT COUNT(*) FROM login_attempts 
             WHERE email = ? AND ip_address = ? 
             AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
             AND (unlock_time IS NULL OR unlock_time <= NOW())");
        mysqli_stmt_bind_param($checkAttemptsStmt, "ss", $email, $ip);
        mysqli_stmt_execute($checkAttemptsStmt);
        mysqli_stmt_bind_result($checkAttemptsStmt, $recent_attempts);
        mysqli_stmt_fetch($checkAttemptsStmt);
        mysqli_stmt_close($checkAttemptsStmt);
        
        // If there are already 5 or more failed attempts, set a lockout immediately
        if ($recent_attempts >= 5) {

            $unlock_time = date('Y-m-d H:i:s', strtotime('+1 minute'));
            
            // First, update all recent attempts with the unlock time
            $setLockoutStmt = mysqli_prepare($conn, 
                "UPDATE login_attempts 
                 SET unlock_time = ? 
                 WHERE email = ? AND ip_address = ? 
                 AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                 AND (unlock_time IS NULL OR unlock_time <= NOW())");
            mysqli_stmt_bind_param($setLockoutStmt, "sss", $unlock_time, $email, $ip);
            mysqli_stmt_execute($setLockoutStmt);
            mysqli_stmt_close($setLockoutStmt);
            
            $formatted_time = date('h:i:s A', strtotime($unlock_time));
            
            $message = "Too many failed login attempts. Please try again after $formatted_time.";
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => $message
            ];
            
            $wait_seconds = 60; 
            
            $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
        } else {
            // Step 2: Proceed with login validation
            $userStmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
            mysqli_stmt_bind_param($userStmt, "s", $email);
            mysqli_stmt_execute($userStmt);
            
            // Get the result of the user query
            $result = mysqli_stmt_get_result($userStmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password_input, $user['password'])) {   
                        // OTP generation
                        $otp = rand(1000, 9999);
                        $_SESSION['otp'] = $otp;
                        $_SESSION['user_temp_id'] = $user['id'];
                        $_SESSION['user_temp_role'] = $user['role'];
                        $_SESSION['otp_generated_at'] = time(); // store UNIX timestamp
                        // Redirect to OTP screen
                        header("Location: otp-verify.php");
                        exit;
                    
                    
                } else {
                    // Log the failed attempt
                    $failedAttemptStmt = mysqli_prepare($conn, "INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, NOW())");
                    mysqli_stmt_bind_param($failedAttemptStmt, "ss", $email, $ip);
                    mysqli_stmt_execute($failedAttemptStmt);
                    mysqli_stmt_close($failedAttemptStmt);
                    
                    // Get the new count of attempts AFTER adding this one
                    $checkAttemptsStmt = mysqli_prepare($conn, 
                        "SELECT COUNT(*) FROM login_attempts 
                         WHERE email = ? AND ip_address = ? 
                         AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                         AND (unlock_time IS NULL OR unlock_time <= NOW())");
                    mysqli_stmt_bind_param($checkAttemptsStmt, "ss", $email, $ip);
                    mysqli_stmt_execute($checkAttemptsStmt);
                    mysqli_stmt_bind_result($checkAttemptsStmt, $new_attempt_count);
                    mysqli_stmt_fetch($checkAttemptsStmt);
                    mysqli_stmt_close($checkAttemptsStmt);
                    
                    // If this is the 5th attempt or more, set a lockout
                    if ($new_attempt_count >= 5) {
                        $unlock_time = date('Y-m-d H:i:s', strtotime('+1 minute'));
                        
                        // Update all recent attempts with the unlock time
                        $setLockoutStmt = mysqli_prepare($conn, 
                            "UPDATE login_attempts 
                             SET unlock_time = ? 
                             WHERE email = ? AND ip_address = ? 
                             AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                             AND (unlock_time IS NULL OR unlock_time <= NOW())");
                        mysqli_stmt_bind_param($setLockoutStmt, "sss", $unlock_time, $email, $ip);
                        mysqli_stmt_execute($setLockoutStmt);
                        mysqli_stmt_close($setLockoutStmt);
                        
                        // Format the unlock time for display
                        $formatted_time = date('h:i:s A', strtotime($unlock_time));
                        
                        $message = "Too many failed login attempts. Please try again after $formatted_time.";
                        $_SESSION['alert'] = [
                            'type' => 'error',
                            'message' => $message
                        ];
                        
                        $wait_seconds = 60;
                        
                        // Set a meta refresh tag to reload the page when the lockout expires
                        $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
                        
                        // No need to refresh the page immediately
                    } else {
                        $message = "Invalid password.";
                        $_SESSION['alert'] = [
                            'type' => 'error',
                            'message' => $message
                        ];
                        // Set a flag to indicate we should add the auto-refresh JavaScript for error message
                        $add_error_refresh = true;
                    }
                }
            } else {
                // Log the failed attempt for non-existent users too
                $failedAttemptStmt = mysqli_prepare($conn, "INSERT INTO login_attempts (email, ip_address, attempt_time) VALUES (?, ?, NOW())");
                mysqli_stmt_bind_param($failedAttemptStmt, "ss", $email, $ip);
                mysqli_stmt_execute($failedAttemptStmt);
                mysqli_stmt_close($failedAttemptStmt);
                
                // Get the new count of attempts AFTER adding this one
                $checkAttemptsStmt = mysqli_prepare($conn, 
                    "SELECT COUNT(*) FROM login_attempts 
                     WHERE email = ? AND ip_address = ? 
                     AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                     AND (unlock_time IS NULL OR unlock_time <= NOW())");
                mysqli_stmt_bind_param($checkAttemptsStmt, "ss", $email, $ip);
                mysqli_stmt_execute($checkAttemptsStmt);
                mysqli_stmt_bind_result($checkAttemptsStmt, $new_attempt_count);
                mysqli_stmt_fetch($checkAttemptsStmt);
                mysqli_stmt_close($checkAttemptsStmt);
                
                // If this is the 5th attempt or more, set a lockout
                if ($new_attempt_count >= 5) {
                    // Set unlock time to exactly 1 minute from now
                    $unlock_time = date('Y-m-d H:i:s', strtotime('+1 minute'));
                    
                    // Update all recent attempts with the unlock time
                    $setLockoutStmt = mysqli_prepare($conn, 
                        "UPDATE login_attempts 
                         SET unlock_time = ? 
                         WHERE email = ? AND ip_address = ? 
                         AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                         AND (unlock_time IS NULL OR unlock_time <= NOW())");
                    mysqli_stmt_bind_param($setLockoutStmt, "sss", $unlock_time, $email, $ip);
                    mysqli_stmt_execute($setLockoutStmt);
                    mysqli_stmt_close($setLockoutStmt);
                    
                    // Format the unlock time for display
                    $formatted_time = date('h:i:s A', strtotime($unlock_time));
                    
                    // Set the lockout message
                    $message = "Too many failed login attempts. Please try again after $formatted_time.";
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => $message
                    ];
                    
                    // Calculate seconds until unlock for meta refresh
                    $wait_seconds = 60; // 1 minute in seconds
                    
                    // Set a meta refresh tag to reload the page when the lockout expires
                    $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
                    
                    // No need to refresh the page immediately
                } else {
                    $message = "User not found.";
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => $message
                    ];
                    $add_error_refresh = true;
                }
            }

            // Close the statement for user validation
            mysqli_stmt_close($userStmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SARPA</title>
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
    <?php if (isset($meta_refresh)) echo $meta_refresh; ?>
    <?php if (isset($add_error_refresh) && $add_error_refresh): ?>
    <!-- Fallback meta refresh for error messages -->
    <meta http-equiv="refresh" content="60; url=<?php echo $_SERVER['PHP_SELF']; ?>">
    <?php endif; ?>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Sign in to your account</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Or <a href="register.php" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">create a new account</a>
                </p>
            </div>
            
            <?php include 'components/alerts.php'; ?>
            
            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" method="POST">
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
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                          focus:outline-none focus:ring-blue-500 focus:border-blue-500 
                                          dark:bg-gray-700 dark:text-white sm:text-sm">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <a href="forgot_password.php" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                Forgot your password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            Sign in
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
                                Or continue with
                            </span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" id="google-signin" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition duration-150">
                            <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                                <g transform="matrix(1, 0, 0, 1, 27.009001, -39.238998)">
                                    <path fill="#4285F4" d="M -3.264 51.509 C -3.264 50.719 -3.334 49.969 -3.454 49.239 L -14.754 49.239 L -14.754 53.749 L -8.284 53.749 C -8.574 55.229 -9.424 56.479 -10.684 57.329 L -10.684 60.329 L -6.824 60.329 C -4.564 58.239 -3.264 55.159 -3.264 51.509 Z"/>
                                    <path fill="#34A853" d="M -14.754 63.239 C -11.514 63.239 -8.804 62.159 -6.824 60.329 L -10.684 57.329 C -11.764 58.049 -13.134 58.489 -14.754 58.489 C -17.884 58.489 -20.534 56.379 -21.484 53.529 L -25.464 53.529 L -25.464 56.619 C -23.494 60.539 -19.444 63.239 -14.754 63.239 Z"/>
                                    <path fill="#FBBC05" d="M -21.484 53.529 C -21.734 52.809 -21.864 52.039 -21.864 51.239 C -21.864 50.439 -21.724 49.669 -21.484 48.949 L -21.484 45.859 L -25.464 45.859 C -26.284 47.479 -26.754 49.299 -26.754 51.239 C -26.754 53.179 -26.284 54.999 -25.464 56.619 L -21.484 53.529 Z"/>
                                    <path fill="#EA4335" d="M -14.754 43.989 C -12.984 43.989 -11.404 44.599 -10.154 45.789 L -6.734 42.369 C -8.804 40.429 -11.514 39.239 -14.754 39.239 C -19.444 39.239 -23.494 41.939 -25.464 45.859 L -21.484 48.949 C -20.534 46.099 -17.884 43.989 -14.754 43.989 Z"/>
                                </g>
                            </svg>
                            Sign in with Google
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
        document.getElementById('google-signin').addEventListener('click', function() {
            // Replace these values with your actual Google OAuth credentials
            const clientId = 'YOUR_GOOGLE_CLIENT_ID';
            const redirectUri = encodeURIComponent('https://yourdomain.com/google-auth-callback.php');
            const scope = encodeURIComponent('email profile');
            const responseType = 'code';
            const accessType = 'offline';
            const prompt = 'consent';
            
            // Construct the Google OAuth URL
            const googleAuthUrl = `https://accounts.google.com/o/oauth2/auth?client_id=${clientId}&redirect_uri=${redirectUri}&scope=${scope}&response_type=${responseType}&access_type=${accessType}&prompt=${prompt}`;
            
            // Redirect to Google's OAuth page
            window.location.href = googleAuthUrl;
        });
        
        <?php if (isset($add_error_refresh) && $add_error_refresh): ?>
        // Auto-refresh the page after 1 minute to clear error messages
        setTimeout(function() {
            window.location.href = window.location.href; // More reliable than location.reload()
        }, 60000); // 60000 ms = 1 minute
        <?php endif; ?>
    </script>
</body>
</html>
