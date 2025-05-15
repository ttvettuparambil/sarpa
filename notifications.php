<?php
session_start();
include 'dbConnection.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Fetch total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_notifications = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_notifications / $per_page);

// Fetch notifications with pagination
$notifications_sql = "SELECT id, title, message, created_at, is_read 
                     FROM notifications 
                     WHERE user_id = ? 
                     ORDER BY created_at DESC
                     LIMIT ?, ?";
$notifications_stmt = mysqli_prepare($conn, $notifications_sql);
mysqli_stmt_bind_param($notifications_stmt, "iii", $_SESSION['user_id'], $offset, $per_page);
mysqli_stmt_execute($notifications_stmt);
$notifications_result = mysqli_stmt_get_result($notifications_stmt);

// Count unread notifications
$unread_sql = "SELECT COUNT(*) as unread_count 
              FROM notifications 
              WHERE user_id = ? AND is_read = 0";
$unread_stmt = mysqli_prepare($conn, $unread_sql);
mysqli_stmt_bind_param($unread_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($unread_stmt);
$unread_result = mysqli_stmt_get_result($unread_stmt);
$unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];

// Handle mark all as read
if (isset($_POST['mark_all_read'])) {
    $mark_all_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $mark_all_stmt = mysqli_prepare($conn, $mark_all_sql);
    mysqli_stmt_bind_param($mark_all_stmt, "i", $_SESSION['user_id']);
    
    if (mysqli_stmt_execute($mark_all_stmt)) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'All notifications marked as read.'
        ];
        // Redirect to refresh the page
        header("Location: notifications.php");
        exit;
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Error marking notifications as read.'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - SARPA</title>
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
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-3 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        Notifications
                        <?php if ($unread_count > 0): ?>
                            <span class="ml-2 px-2 py-1 text-xs font-bold rounded-full bg-red-500 text-white"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </h1>
                </div>
                <div class="flex space-x-3">
                    <?php if ($unread_count > 0): ?>
                        <form method="POST" class="inline">
                            <button type="submit" name="mark_all_read" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mark All as Read
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="user-dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            </div>
            
            <?php include 'components/alerts.php'; ?>
            
            <div class="p-6">
                <?php if (mysqli_num_rows($notifications_result) > 0): ?>
                    <div class="space-y-4">
                        <?php while ($notification = mysqli_fetch_assoc($notifications_result)): ?>
                            <div class="p-4 rounded-lg border <?= $notification['is_read'] ? 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700' : 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' ?>">
                                <div class="flex justify-between">
                                    <h3 class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($notification['title']) ?></h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400"><?= date('M d, Y H:i', strtotime($notification['created_at'])) ?></span>
                                </div>
                                <p class="mt-1 text-gray-600 dark:text-gray-300"><?= htmlspecialchars($notification['message']) ?></p>
                                <?php if (!$notification['is_read']): ?>
                                    <form method="POST" action="mark_notification_read.php" class="mt-2">
                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                        <input type="hidden" name="redirect" value="notifications.php<?= isset($_GET['page']) ? '?page=' . $_GET['page'] : '' ?>">
                                        <button type="submit" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            Mark as read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 dark:bg-blue-900 text-sm font-medium text-blue-600 dark:text-blue-300">
                                            <?= $i ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="?page=<?= $i ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                            <?= $i ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?= $page + 1 ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Notifications</h3>
                        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">You don't have any notifications yet. When you receive notifications, they will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
</body>
</html>
