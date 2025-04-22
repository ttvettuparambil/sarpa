<?php
session_start();
include 'dbConnection.php';

// Sanitize input
$location = htmlspecialchars(trim($_POST['location']));
$datetime = $_POST['datetime'];
$description = htmlspecialchars(trim($_POST['description']));
$reporter_name = !empty($_POST['reporter_name']) ? htmlspecialchars(trim($_POST['reporter_name'])) : null;
$reporter_contact = !empty($_POST['reporter_contact']) ? htmlspecialchars(trim($_POST['reporter_contact'])) : null;

// Image handling
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $tmp_name = $_FILES['image']['tmp_name'];
    $original_name = basename($_FILES['image']['name']);
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $new_name = uniqid('snake_', true) . '.' . $extension;

    // Check file size (limit 2MB)
    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        die("Image is too large. Maximum size is 2MB.");
    }

    $image_path = $upload_dir . $new_name;
    move_uploaded_file($tmp_name, $image_path);
}

// Insert into DB
$stmt = mysqli_prepare($conn, "INSERT INTO snake_sightings (location, datetime, description, image_path, reporter_name, reporter_contact) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssss", $location, $datetime, $description, $image_path, $reporter_name, $reporter_contact);

if (mysqli_stmt_execute($stmt)) {
    echo "<h2>✅ Snake sighting reported successfully!</h2>";
    echo "<p><a href='index.html'>Back to Home</a></p>";
} else {
    echo "<h2>❌ Failed to submit report. Please try again.</h2>";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
