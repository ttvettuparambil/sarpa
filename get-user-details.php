<?php
session_start();
require 'dbConnection.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

$user_id = $_GET['id'];

// Fetch user details with profile information
$stmt = $conn->prepare("
    SELECT u.*, p.dob, p.gender, p.occupation, p.education_level, p.bio, 
           p.alternate_email, p.alternate_phone
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    WHERE u.id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Return user details as JSON
header('Content-Type: application/json');
echo json_encode($user); 