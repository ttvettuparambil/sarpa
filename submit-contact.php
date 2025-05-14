<?php
session_start();
require 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && $phone && $message) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $message);
        if ($stmt->execute()) {
            $_SESSION['contact_success'] = "Your message has been sent successfully!";
        } else {
            $_SESSION['contact_error'] = "There was an error submitting your message. Please try again.";
        }
    } else {
        $_SESSION['contact_error'] = "Please fill in all fields with valid information.";
    }
} else {
    $_SESSION['contact_error'] = "Invalid request.";
}

header('Location: index.php#contact');
exit; 