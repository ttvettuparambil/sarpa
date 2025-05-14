<?php
session_start();

// Log the user logout if user is logged in
if (isset($_SESSION['user_id'])) {
    // Log the logout directly with SQL
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
    
    // Log the logout activity
    $user_id = $_SESSION['user_id'];
    $action_type = "LOGOUT";
    $action_description = "User logged out";
    
    $stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $action_type, $action_description, $ip_address, $browser, $device_type);
    $stmt->execute();
    $stmt->close();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
