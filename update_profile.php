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

// Optional: Validate fields more thoroughly here
$stmt = $conn->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE user_profiles SET dob=?, gender=?, occupation=?, education_level=?, bio=?, alternate_phone=?, alternate_email=? WHERE user_id=?");
    $stmt->bind_param("sssssssi", $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email, $user_id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, dob, gender, occupation, education_level, bio, alternate_phone, alternate_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $user_id, $dob, $gender, $occupation, $education_level, $bio, $alternate_phone, $alternate_email);
}

if ($stmt->execute()) {
    $_SESSION['profile_msg'] = "Profile updated successfully.";
} else {
    $_SESSION['profile_msg'] = "Error updating profile.";
}

header("Location: user-dashboard.php");
exit;
?>
