<?php
session_start();
require 'dbConnection.php'; // assumes db connection is here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $district = $_POST['district'];
    $city = $_POST['city'];
    $postcode = $_POST['postcode'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $landmark = $_POST['landmark'];

    // Validate sighting_time
    $sighting_time = $_POST['sighting_time'] ?? '';
    if (empty($sighting_time)) {
        echo "Error: Date and time of sighting is required.";
        exit;
    }
    
    $description = !empty($_POST['description']) ? $_POST['description'] : null;

    $name = $_POST['name'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $email = $_POST['email'] ?? null;

    $complaint_id = uniqid("C-");

    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image_path = $target_dir . time() . "-" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    // Prepare SQL insert
    $stmt = $conn->prepare("INSERT INTO snake_sightings 
        (complaint_id, district, city, postcode, address_line1, address_line2, landmark, datetime, description, image_path, user_name, user_phone, user_email) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssssss",
        $complaint_id,
        $district,
        $city,
        $postcode,
        $address1,
        $address2,
        $landmark,
        $sighting_time,
        $description,
        $image_path,
        $name,
        $phone,
        $email
    );

    if ($stmt->execute()) {
        $_SESSION['complaint_id'] = $complaint_id;
        
        // Log the snake sighting submission directly with SQL
        if (isset($_SESSION['user_id'])) {
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
            
            // Log the snake sighting submission activity
            $user_id = $_SESSION['user_id'];
            $action_type = "SNAKE_SIGHTING_SUBMITTED";
            $action_description = "Snake sighting submitted (ID: $complaint_id)";
            
            $log_stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
            $log_stmt->bind_param("isssss", $user_id, $action_type, $action_description, $ip_address, $browser, $device_type);
            $log_stmt->execute();
            $log_stmt->close();
        }
        
        header("Location: sighting-summary.php");
        exit;
    } else {
        echo "Something went wrong. Please try again.";
    }
}
?>
