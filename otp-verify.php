<?php
session_start();
if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];

    if ($entered_otp == $_SESSION['otp']) {
        // OTP matches – proceed to login
        $_SESSION['user_id'] = $_SESSION['user_temp_id'];
        $_SESSION['role'] = $_SESSION['user_temp_role'];

        unset($_SESSION['otp'], $_SESSION['user_temp_id'], $_SESSION['user_temp_role']);

        if ($_SESSION['role'] == 'user') {
            header("Location: user-dashboard.php");
        } elseif ($_SESSION['role'] == 'partner') {
            header("Location: partner-dashboard.php");
        } elseif ($_SESSION['role'] == 'super_admin') {
            header("Location: admin-dashboard.php");
        }
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
    echo <?=$_SESSION['user_temp_id'] ?>
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
    <p id="resend-info">Didn't receive the code? <span id="resend-timer">Resend in 30s</span></p>
<p id="resend-link" style="display: none;">
    <a href="#" onclick="resendOTP()">Resend OTP</a>
</p>

    <script>
    const otpInput = document.getElementById('otpInput');
    const otpForm = document.getElementById('otpForm');

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
</script>
</body>
</html>
