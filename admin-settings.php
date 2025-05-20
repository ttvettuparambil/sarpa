<?php
session_start();
require 'dbConnection.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

// Handle form submission to toggle maintenance mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_maintenance'])) {
    // Get current maintenance mode status
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Toggle the value (0 to 1 or 1 to 0)
    $new_value = ($row['setting_value'] == '1') ? '0' : '1';
    
    // Update the setting
    $update_stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
    $update_stmt->bind_param("s", $new_value);
    
    if ($update_stmt->execute()) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Maintenance mode ' . ($new_value == '1' ? 'enabled' : 'disabled') . ' successfully.'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Failed to update maintenance mode setting.'
        ];
    }
    
    // Redirect to avoid form resubmission
    header('Location: admin-settings.php');
    exit;
}

// Get current maintenance mode status
$stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
$stmt->execute();
$result = $stmt->get_result();
$maintenance_mode = $result->fetch_assoc()['setting_value'];
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARPA Admin Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'components/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Site Settings</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage site-wide settings and configurations</p>
            </div>

            <!-- Alerts -->
            <?php include 'components/alerts.php'; ?>

            <!-- Settings Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Maintenance Mode Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Maintenance Mode</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $maintenance_mode == '1' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'; ?>">
                            <?php echo $maintenance_mode == '1' ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        When maintenance mode is enabled, regular users will see a maintenance message and won't be able to access the site. Super admins can still access all pages.
                    </p>
                    <form method="POST">
                        <button type="submit" name="toggle_maintenance" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white <?php echo $maintenance_mode == '1' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'; ?> focus:outline-none focus:ring-2 focus:ring-offset-2 <?php echo $maintenance_mode == '1' ? 'focus:ring-green-500' : 'focus:ring-red-500'; ?>">
                            <?php echo $maintenance_mode == '1' ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
