<?php
// Get current page filename for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
?>

<header class="bg-white dark:bg-gray-800 shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="index.php" class="flex items-center">
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">SARPA</span>
                <span class="ml-2 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">Snake Rescue</span>
            </a>
            
            <!-- Dark Mode Toggle -->
            <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <!-- Sun icon for dark mode (shown when in dark mode) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 dark:text-yellow-300 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Moon icon for light mode (shown when in light mode) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 dark:text-gray-300 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
            
            <!-- Mobile menu button -->
            <button id="mobileMenuBtn" class="md:hidden p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <?php if (!$is_logged_in): ?>
                    <a href="index.php" class="<?= $current_page == 'index.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Home</a>
                    <a href="login.php" class="<?= $current_page == 'login.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Login</a>
                    <a href="register.php" class="<?= $current_page == 'register.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Register</a>
                <?php elseif ($user_role == 'user'): ?>
                    <a href="user-dashboard.php" class="<?= $current_page == 'user-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Dashboard</a>
                    <a href="snake-sighting-form.php" class="<?= $current_page == 'snake-sighting-form.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Report Sighting</a>
                    <a href="user_profile.php" class="<?= $current_page == 'user_profile.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Profile</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400">Logout</a>
                <?php elseif ($user_role == 'partner'): ?>
                    <a href="partner-dashboard.php" class="<?= $current_page == 'partner-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Dashboard</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400">Logout</a>
                <?php elseif ($user_role == 'super_admin'): ?>
                    <a href="admin-dashboard.php" class="<?= $current_page == 'admin-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?>">Dashboard</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobileMenu" class="md:hidden hidden pb-4">
            <nav class="flex flex-col space-y-3">
                <?php if (!$is_logged_in): ?>
                    <a href="index.php" class="<?= $current_page == 'index.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Home</a>
                    <a href="login.php" class="<?= $current_page == 'login.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Login</a>
                    <a href="register.php" class="<?= $current_page == 'register.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Register</a>
                <?php elseif ($user_role == 'user'): ?>
                    <a href="user-dashboard.php" class="<?= $current_page == 'user-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                    <a href="snake-sighting-form.php" class="<?= $current_page == 'snake-sighting-form.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Report Sighting</a>
                    <a href="user_profile.php" class="<?= $current_page == 'user_profile.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                <?php elseif ($user_role == 'partner'): ?>
                    <a href="partner-dashboard.php" class="<?= $current_page == 'partner-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                <?php elseif ($user_role == 'super_admin'): ?>
                    <a href="admin-dashboard.php" class="<?= $current_page == 'admin-dashboard.php' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400' ?> py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                    <a href="logout.php" class="text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 py-2 px-3 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>

<!-- Emergency Banner -->
<div class="bg-red-600 dark:bg-red-800 text-white py-3">
    <div class="container mx-auto px-4 text-center">
        <p class="flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <strong>24/7 EMERGENCY SERVICE:</strong>
            <a href="tel:+1234567890" class="ml-2 underline hover:no-underline">(123) 456-7890</a>
            <span class="ml-2">for immediate snake removal!</span>
        </p>
    </div>
</div>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Dark mode toggle
    document.getElementById('darkModeToggle').addEventListener('click', function() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('darkMode', 'false');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('darkMode', 'true');
        }
    });
</script>
