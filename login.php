<?php
include 'dbConnection.php';
session_start();

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
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    echo "<p style='color: red;'>You were automatically logged out due to inactivity. Please log in again.</p>";
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
                        
                        $wait_seconds = 60;
                        
                        // Set a meta refresh tag to reload the page when the lockout expires
                        $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
                        
                        // No need to refresh the page immediately
                    } else {
                        $message = "Invalid password.";
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
                    
                    // Calculate seconds until unlock for meta refresh
                    $wait_seconds = 60; // 1 minute in seconds
                    
                    // Set a meta refresh tag to reload the page when the lockout expires
                    $meta_refresh = "<meta http-equiv='refresh' content='$wait_seconds; url=" . $_SERVER['PHP_SELF'] . "'>";
                    
                    // No need to refresh the page immediately
                } else {
                    $message = "User not found.";
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
<html>
<head>
    <title>User Login - SARPA</title>
    <link rel="stylesheet" href="style.css">
    <?php if (isset($meta_refresh)) echo $meta_refresh; ?>
    <?php if (isset($add_error_refresh) && $add_error_refresh): ?>
    <!-- Fallback meta refresh for error messages -->
    <meta http-equiv="refresh" content="60; url=<?php echo $_SERVER['PHP_SELF']; ?>">
    <?php endif; ?>
    <style>
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 15px;
            margin: 15px 0;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            max-width: 250px;
        }
        .google-btn:hover {
            background-color: #f5f5f5;
        }
        .google-btn img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .or-divider {
            display: flex;
            align-items: center;
            margin: 15px 0;
            color: #666;
        }
        .or-divider::before, .or-divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .or-divider::before {
            margin-right: 10px;
        }
        .or-divider::after {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <h2>Login</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="submit" value="Login">
    </form>
    
    <div class="or-divider">OR</div>
    
    <button type="button" id="google-signin" class="google-btn">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
        Sign in with Google
    </button>
    
    <p><?= $message ?></p>
    <a href="/forgot_password.php">Forgot password?</a>
    
    <?php if (isset($add_error_refresh) && $add_error_refresh): ?>
        <script>
            // Auto-refresh the page after 1 minute to clear error messages
            setTimeout(function() {
                window.location.href = window.location.href; // More reliable than location.reload()
            }, 60000); // 60000 ms = 1 minute
        </script>
    <?php endif; ?>
    
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
    </script>
</body>
</html>
