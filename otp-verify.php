<?php
session_start();
require_once 'dbConnection.php';

// Check if site is in maintenance mode but allow super_admin to proceed
if (isset($_SESSION['user_temp_role']) && $_SESSION['user_temp_role'] === 'super_admin') {
    // Allow super_admin to proceed with login
} else {
    // Check maintenance mode for non-super_admin users
    require_once 'maintenance_check.php';
    checkMaintenanceMode($conn);
}

if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit;
}

// Set OTP expiry time in seconds (e.g., 5 minutes = 300 seconds)
$otp_expiry_time = 300;

// Check if OTP has expired
$current_time = time();
$otp_generated_at = $_SESSION['otp_generated_at'];

if (($current_time - $otp_generated_at) > $otp_expiry_time) {
    unset($_SESSION['otp'], $_SESSION['otp_generated_at']);
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => "Your OTP has expired. Please request a new one."
    ];
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // OTP matches – proceed to login
        $_SESSION['user_id'] = $_SESSION['user_temp_id'];
        $_SESSION['role'] = $_SESSION['user_temp_role'];

        // Log the successful login directly with SQL
        require_once 'dbConnection.php';
        
        // Get IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Get browser and device information
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser = '';
        $device_type = '';
        
        // Parse user agent to determine browser
        if (strpos($user_agent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($user_agent, 'Chrome') !== false && strpos($user_agent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (strpos($user_agent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($user_agent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'MSIE') !== false || strpos($user_agent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } else {
            $browser = 'Other';
        }
        
        // Parse user agent to determine device type
        if (strpos($user_agent, 'Mobile') !== false || strpos($user_agent, 'Android') !== false) {
            $device_type = 'Mobile';
        } elseif (strpos($user_agent, 'Tablet') !== false || strpos($user_agent, 'iPad') !== false) {
            $device_type = 'Tablet';
        } else {
            $device_type = 'Desktop';
        }
        
        // Log the login activity
        $action_type = "LOGIN";
        $action_description = "User logged in";
        
        $stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $action_type, $action_description, $ip_address, $browser, $device_type);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['otp'], $_SESSION['user_temp_id'], $_SESSION['user_temp_role']);

        // Redirect based on user role
        $redirect_url = '';
        if ($_SESSION['role'] == 'user') {
            $redirect_url = 'user-dashboard.php';
        } elseif ($_SESSION['role'] == 'partner') {
            $redirect_url = 'partner-dashboard.php';
        } elseif ($_SESSION['role'] == 'super_admin') {
            $redirect_url = 'admin-dashboard.php';
        }
        
        // Set success message
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => "Login successful. Welcome back!"
        ];
        
        // Ensure no output before redirection
        if (ob_get_length()) ob_end_clean();
        header("Location: " . $redirect_url);
        exit;
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => "Incorrect OTP. Please try again."
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - SARPA</title>
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
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">Verify Your Identity</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter the 4-digit code sent to your email
                </p>
            </div>
            
            <?php include 'components/alerts.php'; ?>
            
            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- For demo purposes only -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 rounded-md">
                    <p class="text-blue-800 dark:text-blue-200 text-sm">
                        <span class="font-semibold">Demo Mode:</span> Your OTP is <span class="font-mono font-bold"><?= $_SESSION['otp'] ?></span>
                    </p>
                </div>
                
                <form method="POST" id="otpForm" class="space-y-6">
                    <div>
                        <label for="otpInput" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            One-Time Password
                        </label>
                        <div class="mt-2 flex justify-center">
                            <input 
                                type="text" 
                                name="otp" 
                                id="otpInput"
                                maxlength="4" 
                                pattern="\d{4}" 
                                required 
                                autocomplete="off"
                                class="appearance-none block w-40 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 
                                       focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-center font-mono text-2xl tracking-widest
                                       dark:bg-gray-700 dark:text-white sm:text-sm"
                                placeholder="••••"
                            >
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Time remaining: <span id="otp-timer" class="font-mono font-medium text-blue-600 dark:text-blue-400">05:00</span>
                        </p>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            Verify OTP
                        </button>
                    </div>
                    
                    <div class="text-center" id="resend-container">
                        <p class="text-sm text-gray-600 dark:text-gray-400" id="resend-info">
                            Didn't receive the code? <span id="resend-timer" class="font-medium">Resend in 30s</span>
                        </p>
                        <p id="resend-link" style="display: none;" class="text-sm">
                            <a href="#" onclick="resendOTP()" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                Resend OTP
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
        const otpInput = document.getElementById('otpInput');
        const otpForm = document.getElementById('otpForm');
        const otpGeneratedAt = <?php echo isset($_SESSION['otp_generated_at']) ? $_SESSION['otp_generated_at'] : '0'; ?>;
        const otpExpiryTime = 300; // 5 minutes in seconds
        const currentTime = Math.floor(Date.now() / 1000);
        let remainingTime = otpExpiryTime - (currentTime - otpGeneratedAt);
        const timerDisplay = document.getElementById('otp-timer');
        
        // Auto-submit when 4 digits are entered
        otpInput.addEventListener('input', () => {
            // Only allow digits
            otpInput.value = otpInput.value.replace(/[^0-9]/g, '');
            
            if (otpInput.value.length === 4) {
                otpForm.submit();
            }
        });
        
        // Resend OTP timer
        let seconds = 30;
        const timerSpan = document.getElementById('resend-timer');
        const resendLink = document.getElementById('resend-link');
        const resendInfo = document.getElementById('resend-info');
        
        const countdown = setInterval(() => {
            seconds--;
            timerSpan.textContent = `Resend in ${seconds}s`;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                resendInfo.style.display = 'none';
                resendLink.style.display = 'block';
            }
        }, 1000);
        
        function resendOTP() {
            fetch('resend_otp.php')
                .then(res => res.text())
                .then(msg => {
                    // Create alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'flex items-center p-4 mb-6 border rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800';
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.innerHTML = `
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>${msg}</div>
                    `;
                    
                    // Insert before the form
                    const form = document.getElementById('otpForm');
                    form.parentNode.insertBefore(alertDiv, form);
                    
                    // Reload after 2 seconds
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                });
        }
        
        function startCountdown() {
            if (remainingTime <= 0) {
                timerDisplay.textContent = '00:00';
                document.getElementById("otpForm").style.display = 'none'; // Hide OTP form if expired
                
                // Create expired message
                const expiredDiv = document.createElement('div');
                expiredDiv.className = 'flex items-center p-4 mb-6 border rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800';
                expiredDiv.setAttribute('role', 'alert');
                expiredDiv.innerHTML = `
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>Your OTP has expired. Please return to login and try again.</div>
                `;
                
                // Insert before the form
                const form = document.getElementById('otpForm');
                form.parentNode.insertBefore(expiredDiv, form);
                
                // Redirect after 3 seconds
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
                
                return;
            }
            
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            remainingTime--;
            
            setTimeout(startCountdown, 1000);
        }
        
        startCountdown();
    </script>
</body>
</html>
