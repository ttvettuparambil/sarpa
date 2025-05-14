<?php
session_start();
include 'dbConnection.php';
// Timeout duration in seconds (10 minutes)
$timeout_duration = 600; // 10 * 60
// Check for inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Fetch user info
$user_id = $_SESSION['user_id'];

$sql = "SELECT first_name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

// Fetch user's past snake sightings
$sightings_sql = "SELECT s.id, s.complaint_id, s.district, s.city, 
                 CONCAT(s.address_line1, IF(s.address_line2 IS NOT NULL, CONCAT(', ', s.address_line2), '')) AS location,
                 s.datetime AS sighting_date, s.description, s.image_path
                 FROM snake_sightings s 
                 WHERE s.user_email = ? 
                 ORDER BY s.datetime DESC";
$sightings_stmt = mysqli_prepare($conn, $sightings_sql);
mysqli_stmt_bind_param($sightings_stmt, "s", $user['email']);
mysqli_stmt_execute($sightings_stmt);
$sightings_result = mysqli_stmt_get_result($sightings_stmt);

// Check if there are any sightings
$has_sightings = mysqli_num_rows($sightings_result) > 0;
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - SARPA</title>
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
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- Driver.js for guided tour -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@0.9.8/dist/driver.min.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@0.9.8/dist/driver.min.js"></script>
    
    <!-- Custom CSS for DataTables in Dark Mode -->
    <style>
        /* Dark mode styles for DataTables */
        .dark .dataTables_wrapper .dataTables_length,
        .dark .dataTables_wrapper .dataTables_filter,
        .dark .dataTables_wrapper .dataTables_info,
        .dark .dataTables_wrapper .dataTables_processing,
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #d1d5db; /* gray-300 */
        }
        
        .dark .dataTables_wrapper .dataTables_length select {
            background-color: #374151; /* gray-700 */
            color: #d1d5db; /* gray-300 */
            border-color: #4b5563; /* gray-600 */
        }
        
        .dark .dataTables_wrapper .dataTables_filter input {
            background-color: #374151; /* gray-700 */
            color: #d1d5db; /* gray-300 */
            border-color: #4b5563; /* gray-600 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #d1d5db !important; /* gray-300 */
            border-color: #4b5563; /* gray-600 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            color: white !important;
            background: #2563eb !important; /* blue-600 */
            border-color: #2563eb !important; /* blue-600 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            color: white !important;
            background: #3b82f6 !important; /* blue-500 */
            border-color: #3b82f6 !important; /* blue-500 */
        }
        
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            color: #6b7280 !important; /* gray-500 */
            background: transparent !important;
            border-color: #4b5563 !important; /* gray-600 */
        }
        
        .dark table.dataTable tbody tr {
            background-color: #1f2937; /* gray-800 */
            color: #f9fafb; /* gray-50 */
        }
        
        .dark table.dataTable.stripe tbody tr.odd {
            background-color: #111827; /* gray-900 */
        }
        
        .dark table.dataTable.hover tbody tr:hover,
        .dark table.dataTable.hover tbody tr.odd:hover {
            background-color: #374151 !important; /* gray-700 */
        }
        
        .dark table.dataTable thead th,
        .dark table.dataTable thead td,
        .dark table.dataTable tfoot th,
        .dark table.dataTable tfoot td {
            color: #f9fafb; /* gray-50 */
            background-color: #1f2937; /* gray-800 */
            border-color: #4b5563; /* gray-600 */
        }
        
        .dark table.dataTable.row-border tbody th, 
        .dark table.dataTable.row-border tbody td, 
        .dark table.dataTable.display tbody th, 
        .dark table.dataTable.display tbody td {
            border-color: #4b5563; /* gray-600 */
        }
        
        .dark .dataTables_wrapper .dataTables_length, 
        .dark .dataTables_wrapper .dataTables_filter, 
        .dark .dataTables_wrapper .dataTables_info, 
        .dark .dataTables_wrapper .dataTables_processing, 
        .dark .dataTables_wrapper .dataTables_paginate {
            color: #d1d5db; /* gray-300 */
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between" id="welcome-section">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Welcome to SARPA, <?= htmlspecialchars($user['first_name']) ?> 👋
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage your snake sightings and account information
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                        <button id="start-tour" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Take Tour
                        </button>
                        <a href="snake-sighting-form.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Report Snake Sighting
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="user-info-cards">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">User ID</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= $_SESSION['user_id'] ?></p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Account Status</p>
                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">Active</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6" id="user-actions">
                    <div>
                        <a href="user_profile.php" class="block w-full py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-center transition-colors">
                            <span class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                View Profile
                            </span>
                        </a>
                    </div>
                    <div>
                        <a href="user_log.php" class="block w-full py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-center transition-colors">
                            <span class="flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                View Activity Log
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Snake Sightings Chart -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8" id="chart-section">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Snake Sightings Over Time</h2>
            </div>
            <div class="p-6">
                <div class="flex justify-center mb-6">
                    <div class="inline-flex rounded-md shadow-sm" role="group" id="time-filters">
                        <button id="week-btn" class="py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-l-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-10 focus:ring-2 focus:ring-blue-500 focus:text-blue-600 dark:focus:text-blue-400 transition-colors">
                            Week
                        </button>
                        <button id="month-btn" class="py-2 px-4 text-sm font-medium text-white bg-blue-600 border border-blue-600 hover:bg-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-500 transition-colors">
                            Month
                        </button>
                        <button id="year-btn" class="py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-r-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:z-10 focus:ring-2 focus:ring-blue-500 focus:text-blue-600 dark:focus:text-blue-400 transition-colors">
                            Year
                        </button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="sightingsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Past Sightings Table -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden" id="sightings-table-section">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Past Sightings</h2>
            </div>
            <div class="p-6">
                <?php if ($has_sightings): ?>
                    <div class="overflow-x-auto">
                        <table id="sightingsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Complaint ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">District</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($sighting = mysqli_fetch_assoc($sightings_result)): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($sighting['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($sighting['complaint_id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($sighting['district']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($sighting['location']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars(date('M d, Y H:i', strtotime($sighting['sighting_date']))) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="sighting-summary.php?complaint_id=<?= $sighting['complaint_id'] ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400">No past sightings found. When you report snake sightings, they will appear here.</p>
                        <a href="snake-sighting-form.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Report Your First Sighting
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <!-- Session Expiry Warning Message -->
    <div id="session-expiry-warning" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-yellow-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Session Expiring Soon</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Your session is about to expire due to inactivity. Do you want to stay logged in?</p>
                <button onclick="extendSession()" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Stay Logged In
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Driver.js tour configuration
        function initializeDriverTour() {
            // Initialize driver.js
            const driver = new Driver({
                    animate: true,
                    opacity: 0.75,
                    padding: 10,
                    showButtons: true,
                    showProgress: true,
                    closeButtonText: "Skip",
                    nextButtonText: "Next",
                    prevButtonText: "Previous",
                    doneBtnText: "Done",
                    stagePadding: 5,
                    allowClose: true,
                    overlayClickNext: false,
                    stageBackground: '#ffffff',
                    overlayColor: 'rgba(0, 0, 0, 0.7)',
                    onReset: () => {
                        console.log('Tour was closed');
                    }
                });

                // Define tour steps
                const steps = [
                    {
                        element: '#welcome-section',
                        popover: {
                            title: 'Welcome to Your Dashboard',
                            description: 'This is your personal dashboard where you can manage all your snake sighting reports and account information.',
                            position: 'bottom'
                        }
                    },
                    {
                        element: '#user-info-cards',
                        popover: {
                            title: 'Your Information',
                            description: 'Here you can see your user ID, email, and account status at a glance.',
                            position: 'bottom'
                        }
                    },
                    {
                        element: '#user-actions',
                        popover: {
                            title: 'Quick Actions',
                            description: 'Access your profile settings or view your activity log with these shortcuts.',
                            position: 'top'
                        }
                    },
                    {
                        element: '#chart-section',
                        popover: {
                            title: 'Sightings Chart',
                            description: 'This chart shows your snake sightings over time, helping you visualize your reporting activity.',
                            position: 'top'
                        }
                    },
                    {
                        element: '#time-filters',
                        popover: {
                            title: 'Time Period Filters',
                            description: 'Switch between different time periods to view your sighting history by week, month, or year.',
                            position: 'bottom'
                        }
                    },
                    {
                        element: '#sightings-table-section',
                        popover: {
                            title: 'Past Sightings',
                            description: 'View all your past snake sighting reports in this table. Click on "View" to see the full details of any report.',
                            position: 'top'
                        }
                    },
                    {
                        element: '#start-tour',
                        popover: {
                            title: 'Take the Tour Again',
                            description: 'You can restart this tour anytime by clicking this button.',
                            position: 'bottom'
                        }
                    }
                ];

                // Define the steps
                driver.defineSteps(steps);
                
                // Return the driver instance for potential use elsewhere
                return driver;
            }
        
        // Initialize the tour when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the tour
            const driverObj = initializeDriverTour();
            
            // Add event listener to start tour button
            const startTourBtn = document.getElementById('start-tour');
            if (startTourBtn) {
                startTourBtn.addEventListener('click', function() {
                    driverObj.start();
                });
            }
            
            // Check if first visit
            const hasSeenTour = localStorage.getItem('hasSeenDashboardTour');
            if (!hasSeenTour) {
                // Wait a bit for the page to fully render before starting the tour
                setTimeout(() => {
                    driverObj.start();
                    localStorage.setItem('hasSeenDashboardTour', 'true');
                }, 1000);
            }
        });

        // Session timeout management
        var sessionTimeout = 600000; // 10 minutes in milliseconds
        var warningTime = 480000; // 8 minutes in milliseconds (show warning 2 minutes before timeout)
        var inactivityTimer;
        var warningTimer;

        // Function to reset timers when user is active (mouse movement, clicks, etc.)
        function resetTimers() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);

            // Reset inactivity timer
            inactivityTimer = setTimeout(logOut, sessionTimeout);

            // Show warning 2 minutes before session expires
            warningTimer = setTimeout(showSessionWarning, warningTime);
        }

        // Function to show session expiry warning
        function showSessionWarning() {
            document.getElementById('session-expiry-warning').classList.remove('hidden');
        }

        // Function to extend session
        function extendSession() {
            // Make a simple AJAX request to reset session timeout on the server
            fetch('extend_session.php')
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    // Reset timers after extending session
                    resetTimers();
                    document.getElementById('session-expiry-warning').classList.add('hidden');
                });
        }

        // Function to log out the user
        function logOut() {
            window.location.href = 'logout.php';
        }

        // Chart.js functionality
        let sightingsChart;
        let currentPeriod = 'month'; // Default period

        // Function to initialize the chart
        function initChart() {
            const ctx = document.getElementById('sightingsChart').getContext('2d');
            
            sightingsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Snake Sightings',
                        data: [],
                        borderColor: '#2563eb', // blue-600
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0, // Only show whole numbers
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            },
                            title: {
                                display: true,
                                text: 'Number of Sightings',
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            }
                        },
                        x: {
                            ticks: {
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            },
                            title: {
                                display: true,
                                text: 'Date',
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return `Sightings: ${context.parsed.y}`;
                                }
                            }
                        },
                        legend: {
                            labels: {
                                color: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            }
                        }
                    }
                }
            });
            
            // Load initial data (month view by default)
            loadChartData('month');
        }

        // Function to load chart data based on selected period
        function loadChartData(period) {
            // Get user email from PHP session
            const userEmail = '<?= htmlspecialchars($user['email']) ?>';
            
            fetch(`get_sighting_stats.php?period=${period}&user_email=${encodeURIComponent(userEmail)}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart data
                    sightingsChart.data.labels = data.labels;
                    sightingsChart.data.datasets[0].data = data.values;
                    
                    // Update the chart
                    sightingsChart.update();
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }

        // Add event listeners for filter buttons
        document.addEventListener('DOMContentLoaded', function() {
            const weekBtn = document.getElementById('week-btn');
            const monthBtn = document.getElementById('month-btn');
            const yearBtn = document.getElementById('year-btn');
            
            weekBtn.addEventListener('click', function() {
                setActivePeriod('week');
            });
            
            monthBtn.addEventListener('click', function() {
                setActivePeriod('month');
            });
            
            yearBtn.addEventListener('click', function() {
                setActivePeriod('year');
            });
            
            // Function to set active period and update UI
            function setActivePeriod(period) {
                // Update button styles
                weekBtn.classList.remove('text-white', 'bg-blue-600', 'border-blue-600');
                monthBtn.classList.remove('text-white', 'bg-blue-600', 'border-blue-600');
                yearBtn.classList.remove('text-white', 'bg-blue-600', 'border-blue-600');
                
                weekBtn.classList.add('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                monthBtn.classList.add('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                yearBtn.classList.add('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                
                // Set active button
                if (period === 'week') {
                    weekBtn.classList.remove('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                    weekBtn.classList.add('text-white', 'bg-blue-600', 'border-blue-600');
                } else if (period === 'month') {
                    monthBtn.classList.remove('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                    monthBtn.classList.add('text-white', 'bg-blue-600', 'border-blue-600');
                } else if (period === 'year') {
                    yearBtn.classList.remove('text-gray-700', 'dark:text-gray-300', 'bg-white', 'dark:bg-gray-700', 'border-gray-300', 'dark:border-gray-600');
                    yearBtn.classList.add('text-white', 'bg-blue-600', 'border-blue-600');
                }
                
                // Update current period and reload data
                currentPeriod = period;
                loadChartData(period);
            }
            
            // Initialize DataTable for sightings
            if (document.getElementById('sightingsTable')) {
                $('#sightingsTable').DataTable({
                    responsive: true,
                    order: [[4, 'desc']], // Sort by date column (index 4) in descending order
                    language: {
                        emptyTable: "No past sightings found"
                    },
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]]
                });
            }
            
            // Initialize chart
            if (document.getElementById('sightingsChart')) {
                initChart();
            }
        });

        // Add event listeners to reset timers based on user activity
        window.onload = function() {
            resetTimers();
        };
        document.onmousemove = resetTimers;
        document.onclick = resetTimers;
        document.onkeypress = resetTimers;
        
        // Update chart colors when dark mode changes
        document.addEventListener('darkModeChanged', function() {
            if (sightingsChart) {
                sightingsChart.options.scales.y.ticks.color = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563';
                sightingsChart.options.scales.y.grid.color = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                sightingsChart.options.scales.y.title.color = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563';
                
                sightingsChart.options.scales.x.ticks.color = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563';
                sightingsChart.options.scales.x.grid.color = document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                sightingsChart.options.scales.x.title.color = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563';
                
                sightingsChart.options.plugins.legend.labels.color = document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563';
                
                sightingsChart.update();
            }
        });
    </script>
</body>
</html>
