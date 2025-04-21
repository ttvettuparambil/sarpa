<?php
session_start();
echo "Session User ID: " . ($_SESSION['user_temp_id'] ?? 'Not set');
if (!isset($_SESSION['user_temp_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Regenerate OTP
$otp = rand(1000, 9999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_generated_at'] = time();

// Show on frontend (for testing)
echo "New OTP: " . $otp;
?>
