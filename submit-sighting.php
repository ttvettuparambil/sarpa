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

    $sighting_time = $_POST['sighting_time'];
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
        header("Location: sighting-summary.php");
        exit;
    } else {
        echo "Something went wrong. Please try again.";
    }
}
?>
