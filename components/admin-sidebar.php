<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white dark:bg-gray-800 shadow-lg">
    <div class="p-4 border-b dark:border-gray-700">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">SARPA Admin</h1>
    </div>
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="admin-dashboard.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-dashboard.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-dashboard-line mr-3 text-xl"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="admin-sightings.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-sightings.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-map-pin-line mr-3 text-xl"></i>
                    Sightings
                </a>
            </li>
            <li>
                <a href="admin-users.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-users.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-user-line mr-3 text-xl"></i>
                    Users
                </a>
            </li>
            <li>
                <a href="admin-settings.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-settings.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-settings-line mr-3 text-xl"></i>
                    Settings
                </a>
            </li>
            <li>
                <a href="admin-reports.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-reports.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-file-list-line mr-3 text-xl"></i>
                    Reports
                </a>
            </li>
            <li>
                <a href="admin-profile.php" class="flex items-center p-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg <?php echo $current_page === 'admin-profile.php' ? 'bg-gray-100 dark:bg-gray-700' : ''; ?>">
                    <i class="ri-user-settings-line mr-3 text-xl"></i>
                    My Profile
                </a>
            </li>
            <li class="pt-4 mt-4 border-t dark:border-gray-700">
                <button onclick="confirmLogout()" class="w-full flex items-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg">
                    <i class="ri-logout-box-line mr-3 text-xl"></i>
                    Logout
                </button>
            </li>
        </ul>
    </nav>
</aside>

<script>
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}
</script> 