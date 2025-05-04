<?php
/**
 * User Activity Logging System
 * 
 * This file contains functions to log various user activities in the SARPA platform.
 * It tracks user journey events such as login, logout, profile updates, and snake sighting activities.
 */

// Include database connection
require_once 'dbConnection.php';

// Display user activity logs if this file is accessed directly

    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $activities = getUserRecentActivity($user_id, 50); // Get up to 50 recent activities
    
    // Get user details
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();


/**
 * Log a user login event
 * 
 * @param int $user_id The ID of the user who logged in
 * @return bool True if logging was successful, false otherwise
 */
function logUserLogin($user_id) {
    global $conn;
    
    $action_type = "LOGIN";
    $action_description = "User logged in";
    
    return logUserActivity($user_id, $action_type, $action_description);
}

/**
 * Log a user logout event
 * 
 * @param int $user_id The ID of the user who logged out
 * @return bool True if logging was successful, false otherwise
 */
function logUserLogout($user_id) {
    global $conn;
    
    $action_type = "LOGOUT";
    $action_description = "User logged out";
    
    return logUserActivity($user_id, $action_type, $action_description);
}

/**
 * Log a profile update event
 * 
 * @param int $user_id The ID of the user who updated their profile
 * @return bool True if logging was successful, false otherwise
 */
function logProfileUpdate($user_id) {
    global $conn;
    
    $action_type = "PROFILE_UPDATE";
    $action_description = "Profile updated";
    
    return logUserActivity($user_id, $action_type, $action_description);
}

/**
 * Log when a user starts a snake sighting form
 * 
 * @param int $user_id The ID of the user who started the snake sighting form
 * @return bool True if logging was successful, false otherwise
 */
function logSnakeSightingStarted($user_id) {
    global $conn;
    
    $action_type = "SNAKE_SIGHTING_STARTED";
    $action_description = "Snake sighting form started";
    
    return logUserActivity($user_id, $action_type, $action_description);
}

/**
 * Log when a user submits a snake sighting
 * 
 * @param int $user_id The ID of the user who submitted the sighting
 * @param string $sighting_id The ID of the submitted snake sighting
 * @return bool True if logging was successful, false otherwise
 */
function logSnakeSightingSubmitted($user_id, $sighting_id) {
    global $conn;
    
    $action_type = "SNAKE_SIGHTING_SUBMITTED";
    $action_description = "Snake sighting submitted (ID: $sighting_id)";
    
    return logUserActivity($user_id, $action_type, $action_description);
}

/**
 * Generic function to log user activity
 * 
 * @param int $user_id The ID of the user performing the action
 * @param string $action_type The type of action being performed
 * @param string $action_description A description of the action
 * @return bool True if logging was successful, false otherwise
 */
function logUserActivity($user_id, $action_type, $action_description) {
    global $conn;
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        error_log("Invalid user_id provided to logUserActivity: $user_id");
        return false;
    }
    
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
    
    // Prepare and execute the query
    try {
        $stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $action_type, $action_description, $ip_address, $browser, $device_type);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error logging user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get recent activity for a specific user
 * 
 * @param int $user_id The ID of the user
 * @param int $limit Maximum number of records to return (default: 10)
 * @return array|false Array of activity records or false on failure
 */
function getUserRecentActivity($user_id, $limit = 10) {
    global $conn;
    
    // Validate user_id
    if (!is_numeric($user_id) || $user_id <= 0) {
        error_log("Invalid user_id provided to getUserRecentActivity: $user_id");
        return false;
    }
    
    // Validate limit
    $limit = max(1, min(100, (int)$limit)); // Ensure limit is between 1 and 100
    
    try {
        $stmt = $conn->prepare("SELECT * FROM account_activity WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        $stmt->close();
        return $activities;
    } catch (Exception $e) {
        error_log("Error retrieving user activity: " . $e->getMessage());
        return false;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>User Activity Log - SARPA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .activity-log {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .activity-log th, .activity-log td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .activity-log th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .activity-log tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .activity-log tr:hover {
            background-color: #f1f1f1;
        }
        .user-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f8f8;
            border-radius: 5px;
        }
        .no-activity {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        .action-type {
            font-weight: bold;
        }
        .timestamp {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <h2>User Activity Log</h2>
    
    <div class="user-info">
        <p><strong>User:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    </div>
    
    <?php if ($activities && count($activities) > 0): ?>
        <table class="activity-log">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Activity Type</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Browser</th>
                    <th>Device</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td class="timestamp"><?php echo date('d M Y, h:i A', strtotime($activity['timestamp'])); ?></td>
                        <td class="action-type"><?php echo htmlspecialchars($activity['action_type']); ?></td>
                        <td><?php echo htmlspecialchars($activity['action_description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['ip_address'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($activity['browser'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($activity['device_type'] ?? 'Unknown'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-activity">
            <p>No activity records found for this user.</p>
        </div>
    <?php endif; ?>
    
    <p><a href="user-dashboard.php">Back to Dashboard</a></p>
</body>
</html>

