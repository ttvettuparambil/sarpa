<?php
session_start();
require 'dbConnection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Save progress
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['video_id']) || !isset($_POST['timestamp'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $video_id = $_POST['video_id'];
    $timestamp = (int)$_POST['timestamp'];
    
    $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, timestamp) 
                           VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           timestamp = ?, last_updated = CURRENT_TIMESTAMP");
    $stmt->bind_param("isii", $user_id, $video_id, $timestamp, $timestamp);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save progress']);
    }
}

// Get progress
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['video_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing video_id parameter']);
        exit;
    }

    $video_id = $_GET['video_id'];
    
    $stmt = $conn->prepare("SELECT timestamp FROM user_video_progress 
                           WHERE user_id = ? AND video_id = ?");
    $stmt->bind_param("is", $user_id, $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $progress = $result->fetch_assoc();
    
    echo json_encode([
        'timestamp' => $progress['timestamp'] ?? 0
    ]);
}
?> 