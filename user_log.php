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
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Log - SARPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        // Using Tailwind's default blue palette
                    }
                }
            }
        }
        
        // Check for dark mode preference in localStorage
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <?php include 'components/alerts.php'; ?>
        
        <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Activity Log</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">View your recent account activity and security events</p>
            </div>
            
            <div class="p-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <a href="user-dashboard.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if ($activities && count($activities) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Activity Type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Description
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Browser
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Device
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($activities as $activity): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('d M Y, h:i A', strtotime($activity['timestamp'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php 
                                            $badgeClass = '';
                                            switch ($activity['action_type']) {
                                                case 'LOGIN':
                                                    $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                    break;
                                                case 'LOGOUT':
                                                    $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                    break;
                                                case 'PROFILE_UPDATE':
                                                    $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                                    break;
                                                case 'SNAKE_SIGHTING_STARTED':
                                                    $badgeClass = 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
                                                    break;
                                                case 'SNAKE_SIGHTING_SUBMITTED':
                                                    $badgeClass = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                            }
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($activity['action_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($activity['action_description']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($activity['ip_address'] ?? 'Unknown'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($activity['browser'] ?? 'Unknown'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <?php 
                                            $deviceIcon = '';
                                            switch ($activity['device_type']) {
                                                case 'Mobile':
                                                    $deviceIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>';
                                                    break;
                                                case 'Tablet':
                                                    $deviceIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm4 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>';
                                                    break;
                                                default:
                                                    $deviceIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd" /></svg>';
                                            }
                                            echo $deviceIcon . htmlspecialchars($activity['device_type'] ?? 'Unknown');
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Activity Records Found</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            There are no activity records available for your account yet.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
