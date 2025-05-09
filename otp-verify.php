<?php
session_start();
if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Set OTP expiry time in seconds (e.g., 5 minutes = 300 seconds)
$otp_expiry_time = 300;

// Check if OTP has expired
$current_time = time();
$otp_generated_at = $_SESSION['otp_generated_at'];

if (($current_time - $otp_generated_at) > $otp_expiry_time) {
    unset($_SESSION['otp'], $_SESSION['otp_generated_at']);
    $message = "Your OTP has expired. Please request a new one.";
} else {
    $message = "";
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
        
        // Ensure no output before redirection
        ob_end_clean();
        header("Location: " . $redirect_url);
        exit;
    } else {
        $message = "Incorrect OTP. Try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter OTP - SARPA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Enter OTP</h2>
    <p>User ID: <?=$_SESSION['user_temp_id'] ?></p>
    <p>For demo purposes, your OTP is: <strong><?= $_SESSION['otp'] ?></strong></p>

    <form method="POST" class="otp-form" id="otpForm">
    <div class="otp-container">
        <input 
            type="text" 
            name="otp" 
            id="otpInput"
            class="otp-input" 
            maxlength="4" 
            pattern="\d{4}" 
            required 
            autocomplete="off"
        >
    </div>
    <button type="submit" class="submit-btn">Verify OTP</button>
</form>

    <p style="color:red;"><?= $message ?></p>
    <div id="message-container"></div>
    <p>Time remaining: <span id="otp-timer"></span></p>
    <p id="resend-info">Didn't receive the code? <span id="resend-timer">Resend in 30s</span></p>
<p id="resend-link" style="display: none;">
    <a href="#" onclick="resendOTP()">Resend OTP</a>
</p>

    <script>
    const otpInput = document.getElementById('otpInput');
    const otpForm = document.getElementById('otpForm');

    const otpGeneratedAt = <?php echo isset($_SESSION['otp_generated_at']) ? $_SESSION['otp_generated_at'] : '0'; ?>;
    const otpExpiryTime = 300; // 5 minutes in seconds
    const currentTime = Math.floor(Date.now() / 1000);
    let remainingTime = otpExpiryTime - (currentTime - otpGeneratedAt);
    const timerDisplay = document.getElementById('otp-timer');
    const messageContainer = document.getElementById('message-container'); // Ensure this element exists in your HTML


    otpInput.addEventListener('input', () => {
        if (otpInput.value.length === 4) {
            otpForm.submit();
        }
    });

    let seconds = 30;
    const timerSpan = document.getElementById('resend-timer');
    const resendLink = document.getElementById('resend-link');

    const countdown = setInterval(() => {
        seconds--;
        timerSpan.textContent = `Resend in ${seconds}s`;

        if (seconds <= 0) {
            clearInterval(countdown);
            timerSpan.style.display = 'none';
            resendLink.style.display = 'inline';
        }
    }, 1000);

    function resendOTP() {
        fetch('resend_otp.php')
            .then(res => res.text())
            .then(msg => {
                alert(msg); // show feedback
                location.reload(); // reload to get new OTP or re-trigger timer
            });
    }

    function startCountdown() {
    if (remainingTime <= 0) {
        timerDisplay.textContent = '00:00';
        messageContainer.innerHTML = "<p style='color:red;'>Your OTP has expired. Please request a new one.</p>";
        document.getElementById("otpForm").style.display = 'none'; // Hide OTP form if expired
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
