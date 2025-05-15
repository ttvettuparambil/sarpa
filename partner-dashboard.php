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
// Redirect if not logged in as partner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'partner') {
    header("Location: login.php");
    exit;
}

// Fetch partner info
$user_id = $_SESSION['user_id'];

$sql = "SELECT first_name, last_name, email, district FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$partner = mysqli_fetch_assoc($result);

// Handle complaint acceptance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accept_complaint'])) {
    $complaint_id = $_POST['complaint_id'];
    
    // Check if complaint is already assigned
    $check_sql = "SELECT id FROM complaint_assignments WHERE complaint_id = ? AND status != 'rejected'";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $complaint_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    if (mysqli_stmt_num_rows($check_stmt) == 0) {
        // Assign complaint to partner
        $assign_sql = "INSERT INTO complaint_assignments (complaint_id, partner_id, status) VALUES (?, ?, 'accepted')";
        $assign_stmt = mysqli_prepare($conn, $assign_sql);
        mysqli_stmt_bind_param($assign_stmt, "si", $complaint_id, $user_id);
        
        if (mysqli_stmt_execute($assign_stmt)) {
            // Get user email from snake sighting
            $user_sql = "SELECT user_email FROM snake_sightings WHERE complaint_id = ?";
            $user_stmt = mysqli_prepare($conn, $user_sql);
            mysqli_stmt_bind_param($user_stmt, "s", $complaint_id);
            mysqli_stmt_execute($user_stmt);
            $user_result = mysqli_stmt_get_result($user_stmt);
            $sighting = mysqli_fetch_assoc($user_result);
            
          // Create notification for user with partner's name
$partner_full_name = htmlspecialchars($partner['first_name']) . ' ' . htmlspecialchars($partner['last_name']);
$notification_message = "Your snake sighting complaint has been accepted by $partner_full_name. They will contact you soon.";

$notify_sql = "INSERT INTO notifications (user_id, title, message) 
              SELECT id, 'Complaint Accepted', ?
              FROM users WHERE email = ?";
$notify_stmt = mysqli_prepare($conn, $notify_sql);
mysqli_stmt_bind_param($notify_stmt, "ss", $notification_message, $sighting['user_email']);
mysqli_stmt_execute($notify_stmt);

            
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Complaint accepted successfully.'
            ];
        } else {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Error accepting complaint.'
            ];
        }
        mysqli_stmt_close($assign_stmt);
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'This complaint has already been assigned to another partner.'
        ];
    }
    mysqli_stmt_close($check_stmt);
    
    // Redirect to refresh the page
    header("Location: partner-dashboard.php");
    exit;
}

// Fetch available complaints in partner's district
$complaints_sql = "SELECT s.*, 
                  CASE WHEN ca.id IS NOT NULL THEN ca.status ELSE 'unassigned' END as assignment_status
                  FROM snake_sightings s 
                  LEFT JOIN complaint_assignments ca ON s.complaint_id = ca.complaint_id
                  WHERE s.district = ? 
                  AND (ca.id IS NULL OR ca.status = 'rejected')
                  ORDER BY s.datetime DESC";
$complaints_stmt = mysqli_prepare($conn, $complaints_sql);
mysqli_stmt_bind_param($complaints_stmt, "s", $partner['district']);
mysqli_stmt_execute($complaints_stmt);
$complaints_result = mysqli_stmt_get_result($complaints_stmt);

// Fetch partner's accepted complaints
$accepted_sql = "SELECT s.*, ca.status, ca.assigned_at 
                 FROM snake_sightings s 
                 JOIN complaint_assignments ca ON s.complaint_id = ca.complaint_id 
                 WHERE ca.partner_id = ? AND ca.status = 'accepted'
                 ORDER BY ca.assigned_at DESC";
$accepted_stmt = mysqli_prepare($conn, $accepted_sql);
mysqli_stmt_bind_param($accepted_stmt, "i", $user_id);
mysqli_stmt_execute($accepted_stmt);
$accepted_result = mysqli_stmt_get_result($accepted_stmt);

// Count total accepted complaints
$count_sql = "SELECT COUNT(*) as total FROM complaint_assignments WHERE partner_id = ? AND status = 'accepted'";
$count_stmt = mysqli_prepare($conn, $count_sql);
mysqli_stmt_bind_param($count_stmt, "i", $user_id);
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_accepted = mysqli_fetch_assoc($count_result)['total'];

// Check if there are any available complaints
$has_available_complaints = mysqli_num_rows($complaints_result) > 0;

// Check if there are any accepted complaints
$has_accepted_complaints = mysqli_num_rows($accepted_result) > 0;
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard - SARPA</title>
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
    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
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
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Welcome to SARPA, <?= htmlspecialchars($partner['first_name']) ?> 👋
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage snake sighting complaints in your district
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                        <a href="user_profile.php" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            View Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Partner ID</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= $_SESSION['user_id'] ?></p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">District</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($partner['district']) ?></p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 flex items-center">
                        <div class="rounded-full bg-blue-100 dark:bg-blue-800 p-3 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Accepted Complaints</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white"><?= $total_accepted ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Available Complaints -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Available Complaints</h2>
            </div>
            <div class="p-6">
                <?php if ($has_available_complaints): ?>
                    <div class="overflow-x-auto">
                        <table id="availableComplaintsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Complaint ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($complaint = mysqli_fetch_assoc($complaints_result)): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($complaint['complaint_id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($complaint['address_line1']) ?>
                                            <?= $complaint['address_line2'] ? ', ' . htmlspecialchars($complaint['address_line2']) : '' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars(date('M d, Y H:i', strtotime($complaint['datetime']))) ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($complaint['description']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">
                                                <button type="submit" name="accept_complaint" 
                                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                                    Accept
                                                </button>
                                            </form>
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
                        <p class="text-gray-600 dark:text-gray-400">No available complaints in your district at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Accepted Complaints -->
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Your Accepted Complaints</h2>
            </div>
            <div class="p-6">
                <?php if ($has_accepted_complaints): ?>
                    <div class="overflow-x-auto">
                        <table id="acceptedComplaintsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Complaint ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assigned At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($complaint = mysqli_fetch_assoc($accepted_result)): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($complaint['complaint_id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($complaint['address_line1']) ?>
                                            <?= $complaint['address_line2'] ? ', ' . htmlspecialchars($complaint['address_line2']) : '' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars(date('M d, Y H:i', strtotime($complaint['datetime']))) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars(date('M d, Y H:i', strtotime($complaint['assigned_at']))) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="sighting-summary.php?complaint_id=<?= $complaint['complaint_id'] ?>" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">View Details</a>
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
                        <p class="text-gray-600 dark:text-gray-400">You haven't accepted any complaints yet.</p>
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
    // Initialize DataTables
    $(document).ready(function() {
        $('#availableComplaintsTable').DataTable({
            responsive: true,
            order: [[2, 'desc']], // Sort by date column
            pageLength: 5,
            lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]]
        });
        
        $('#acceptedComplaintsTable').DataTable({
            responsive: true,
            order: [[3, 'desc']], // Sort by assigned date column
            pageLength: 5,
            lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]]
        });
    });
    
    // Session timeout management
    var sessionTimeout = 600000; // 10 minutes in milliseconds
    var warningTime = 480000; // 8 minutes in milliseconds (show warning 2 minutes before timeout)
    var inactivityTimer;
    var warningTimer;

    // Function to reset timers when user is active
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

    // Add event listeners to reset timers based on user activity
    window.onload = function() {
        resetTimers();
    };
    document.onmousemove = resetTimers;
    document.onclick = resetTimers;
    document.onkeypress = resetTimers;
</script>

