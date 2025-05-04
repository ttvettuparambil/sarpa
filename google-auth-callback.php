<?php
// google-auth-callback.php - Handles the OAuth callback from Google

session_start();
require_once 'dbConnection.php';

// Google API client library should be installed via Composer
// composer require google/apiclient:^2.0

// Load the Google API PHP Client Library
require_once 'vendor/autoload.php';

// Configure Google Client
$client = new Google_Client();
$client->setClientId('YOUR_GOOGLE_CLIENT_ID'); // Replace with your client ID
$client->setClientSecret('YOUR_GOOGLE_CLIENT_SECRET'); // Replace with your client secret
$client->setRedirectUri('https://yourdomain.com/google-auth-callback.php'); // Replace with your domain
$client->addScope('email');
$client->addScope('profile');

// Error handling
if (isset($_GET['error'])) {
    // Handle authentication error
    $_SESSION['auth_error'] = $_GET['error'];
    header('Location: login.php');
    exit;
}

// Exchange authorization code for access token
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    
    // Get user profile information
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    // Extract profile data
    $google_id = $google_account_info->id;
    $email = $google_account_info->email;
    $name = $google_account_info->name;
    $given_name = $google_account_info->givenName;
    $family_name = $google_account_info->familyName;
    
    // Check if user exists with this Google ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE auth_provider = 'google' AND provider_user_id = ?");
    $stmt->bind_param("s", $google_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // User exists, update their information if needed
        $stmt = $conn->prepare("UPDATE users SET 
                               email = ?, 
                               first_name = ?, 
                               last_name = ?,
                               last_login = NOW() 
                               WHERE id = ?");
        $stmt->bind_param("sssi", $email, $given_name, $family_name, $user['id']);
        $stmt->execute();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
    } else {
        // Check if user exists with this email but different auth method
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_user = $result->fetch_assoc();
        
        if ($existing_user) {
            // Link Google account to existing user
            $stmt = $conn->prepare("UPDATE users SET 
                                   auth_provider = 'google', 
                                   provider_user_id = ?,
                                   last_login = NOW() 
                                   WHERE id = ?");
            $stmt->bind_param("si", $google_id, $existing_user['id']);
            $stmt->execute();
            
            // Set session variables
            $_SESSION['user_id'] = $existing_user['id'];
            $_SESSION['role'] = $existing_user['role'];
            
        } else {
            // Create new user
            $role = 'user'; // Default role for new users
            $stmt = $conn->prepare("INSERT INTO users 
                                   (role, first_name, last_name, email, auth_provider, provider_user_id, created_at) 
                                   VALUES (?, ?, ?, ?, 'google', ?, NOW())");
            $stmt->bind_param("sssss", $role, $given_name, $family_name, $email, $google_id);
            $stmt->execute();
            
            // Get the new user ID
            $user_id = $conn->insert_id;
            
            // Set session variables
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
        }
    }
    
    // Redirect based on user role
    if ($_SESSION['role'] == 'user') {
        header("Location: user-dashboard.php");
    } elseif ($_SESSION['role'] == 'partner') {
        header("Location: partner-dashboard.php");
    } elseif ($_SESSION['role'] == 'super_admin') {
        header("Location: admin-dashboard.php");
    } else {
        // Fallback
        header("Location: index.php");
    }
    exit;
} else {
    // No authorization code, redirect to login page
    header('Location: login.php');
    exit;
}
?>
