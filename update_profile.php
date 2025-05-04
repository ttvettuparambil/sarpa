<?php
session_start();
require 'dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Sanitize input
$dob = empty($_POST['dob']) ? null : $_POST['dob'];
$gender = $_POST['gender'] ?? '';
$occupation = trim($_POST['occupation'] ?? '');
$education_level = trim($_POST['education_level'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$alternate_phone = trim($_POST['alternate_phone'] ?? '');
$alternate_email = trim($_POST['alternate_email'] ?? '');

// Handle profile picture upload
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file = $_FILES['profile_picture'];
    
    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['profile_msg'] = "Error: Only image files (JPEG, PNG, GIF, WEBP) are allowed.";
        header("Location: user_profile.php");
        exit;
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        $_SESSION['profile_msg'] = "Error: File size exceeds 2MB limit.";
        header("Location: user_profile.php");
        exit;
    }
    
    // Create profile_pics directory if it doesn't exist
    if (!file_exists('profile_pics')) {
        mkdir('profile_pics', 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $file_extension;
    $upload_path = 'profile_pics/' . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Delete old profile picture if exists
        $stmt = $conn->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $old_profile = $result->fetch_assoc();
            if (!empty($old_profile['profile_picture']) && file_exists('profile_pics/' . $old_profile['profile_picture'])) {
                unlink('profile_pics/' . $old_profile['profile_picture']);
            }
        }
        
        $profile_picture = $new_filename;
    } else {
        $_SESSION['profile_msg'] = "Error uploading file. Please try again.";
        header("Location: user_profile.php");
        exit;
    }
}

// Optional: Validate fields more thoroughly here
$stmt = $conn->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    if ($profile_picture) {
        $stmt = $conn->prepare("UPDATE user_profiles SET dob=?, gender=?, occupation=?, education_level=?, bio=?, alternate_phone=?, alternate_email=?, profile_picture=? WHERE user_id=?");
        $stmt->bind_param("ssssssssi", $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email, $profile_picture, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE user_profiles SET dob=?, gender=?, occupation=?, education_level=?, bio=?, alternate_phone=?, alternate_email=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email, $user_id);
    }
} else {
    // Insert
    if ($profile_picture) {
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, dob, gender, occupation, education_level, bio, alternate_phone, alternate_email, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $user_id, $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email, $profile_picture);
    } else {
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, dob, gender, occupation, education_level, bio, alternate_phone, alternate_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email);
    }
}

if ($stmt->execute()) {
    // Log the profile update directly with SQL
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
    
    // Log the profile update activity
    $action_type = "PROFILE_UPDATE";
    $action_description = "Profile updated";
    
    $log_stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
    $log_stmt->bind_param("isssss", $user_id, $action_type, $action_description, $ip_address, $browser, $device_type);
    $log_stmt->execute();
    $log_stmt->close();
    
    $_SESSION['profile_msg'] = "Profile updated successfully.";
} else {
    $_SESSION['profile_msg'] = "Error updating profile: " . $stmt->error;
}

header("Location: user_profile.php");
exit;
?>
