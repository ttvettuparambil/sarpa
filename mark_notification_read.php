<?php
session_start();
include 'dbConnection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if notification_id is provided
if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];
    
    // Update notification to mark as read
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Notification marked as read.'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Error marking notification as read.'
        ];
    }
    
    mysqli_stmt_close($stmt);
}

// Redirect back to the appropriate page
$redirect_to = isset($_POST['redirect']) ? $_POST['redirect'] : 'user-dashboard.php';
header("Location: $redirect_to");
exit;
?>
