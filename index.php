<?php
session_start();
require 'dbConnection.php';

// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);

// Enhanced routing system using parse_url
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'] ?? '/';

// Remove script name from path if present
$script_name = $_SERVER['SCRIPT_NAME'];
if (strpos($path, $script_name) === 0) {
    $path = substr($path, strlen($script_name));
}

// Clean up the path
$path = trim($path, '/');

// Check for query parameter routing (backward compatibility)
if (isset($_GET['page'])) {
    $path = $_GET['page'];
}

// Define routing table
$routes = [
    '' => null, // Home page - no routing needed
    'login' => 'login.php',
    'register' => 'register.php',
    'logout' => 'logout.php',
    'otp-verify' => 'otp-verify.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',
    'dashboard' => 'user-dashboard.php',
    'profile' => 'user_profile.php',
    'snake-sighting' => 'snake-sighting-form.php',
    'sighting-summary' => 'sighting-summary.php',
    'notifications' => 'notifications.php',
    'user-log' => 'user_log.php',
    'partner-register' => 'partner-register.php',
    'partner-dashboard' => 'partner-dashboard.php',
    'admin/dashboard' => 'admin-dashboard.php',
    'admin/users' => 'admin-users.php',
    'admin/settings' => 'admin-settings.php',
    'admin/profile' => 'admin-profile.php',
    'video-progress' => 'video-progress.php',
    'extend-session' => 'extend_session.php',
    'resend-otp' => 'resend_otp.php',
    'submit-contact' => 'submit-contact.php',
    'submit-sighting' => 'submit-sighting.php',
    'update-profile' => 'update_profile.php',
    'get-user-details' => 'get-user-details.php',
    'get-sighting-stats' => 'get_sighting_stats.php',
    'mark-notification-read' => 'mark_notification_read.php',
    'export-csv' => 'export-csv.php',
    'update-maintenance-check' => 'update_maintenance_check.php',
    'google-auth-callback' => 'google-auth-callback.php',
    'gemini-proxy' => 'gemini-proxy.php'
];

// Handle routing
if (!empty($path) && isset($routes[$path])) {
    $target_file = $routes[$path];
    if ($target_file && file_exists($target_file)) {
        include $target_file;
        exit;
    } else {
        // Invalid page, show 404
        http_response_code(404);
        include '404.php';
        exit;
    }
} elseif (!empty($path) && !isset($routes[$path])) {
    // Path not found in routes, show 404
    http_response_code(404);
    include '404.php';
    exit;
}

// If no routing needed, show normal index.php content
// Prefill contact form fields if user is logged in
$user_name = $user_email = $user_phone = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT u.first_name, u.last_name, u.email, p.alternate_phone
        FROM users u
        LEFT JOIN user_profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_name = trim($row['first_name'] . ' ' . $row['last_name']);
        $user_email = $row['email'];
        $user_phone = $row['alternate_phone'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARPA - Professional Snake Rescue & Wildlife Conservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Video.js CSS -->
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
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
    
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="relative py-20 md:py-32 overflow-hidden">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1551969014-7d2c4cddf0b6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                     alt="Snake Background" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-60"></div>
            </div>
            
            <!-- Hero Content -->
            <div class="container mx-auto px-4 relative z-10">
                <div class="max-w-3xl">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                        Professional Snake Rescue Services
                    </h1>
                    <p class="text-xl text-gray-200 mb-8">
                        Safe, humane, and expert snake rescue services for homes and businesses. 
                        Our licensed professionals are available 24/7 to handle any snake emergency.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="snake-sighting-form.php" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300 transform hover:-translate-y-1">
                            Report Snake Sighting
                        </a>
                        <a href="register.php" class="px-6 py-3 bg-white hover:bg-gray-100 text-blue-600 font-medium rounded-lg transition duration-300 transform hover:-translate-y-1">
                            Register
                        </a>
                        <a href="login.php" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-300 transform hover:-translate-y-1">
                            Login
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-16 bg-white dark:bg-gray-800">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Our Services</h2>
                    <div class="w-20 h-1 bg-blue-600 mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Service Card 1 -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="https://images.unsplash.com/photo-1551969014-7d2c4cddf0b6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                             alt="Emergency Snake Removal" 
                             class="w-full h-56 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Emergency Removal</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                24/7 rapid response for snake emergencies in your home or business. 
                                We'll safely remove the snake and relocate it to its natural habitat.
                            </p>
                            <a href="#" class="inline-block text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                Learn More →
                            </a>
                        </div>
                    </div>
                    
                    <!-- Service Card 2 -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="https://images.unsplash.com/photo-1573869909165-e7c0cdf44a8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                             alt="Snake Prevention" 
                             class="w-full h-56 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Snake Prevention</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Professional snake-proofing services to keep snakes out of your property. 
                                We identify and seal potential entry points.
                            </p>
                            <a href="#" class="inline-block text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                Learn More →
                            </a>
                        </div>
                    </div>
                    
                    <!-- Service Card 3 -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:transform hover:scale-105">
                        <img src="https://images.unsplash.com/photo-1560575193-c2c9e886aefe?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" 
                             alt="Educational Programs" 
                             class="w-full h-56 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Educational Programs</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Learn about local snake species, their habitats, and how to coexist safely. 
                                Perfect for schools and community groups.
                            </p>
                            <a href="#" class="inline-block text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                Learn More →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-16 bg-gray-100 dark:bg-gray-900">
            <div class="container mx-auto px-4">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2">
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">About Our Snake Catchers</h2>
                        <p class="text-gray-600 dark:text-gray-300 mb-6 text-lg">
                            With over 15 years of experience, our team of licensed and insured professionals provides 
                            safe, humane snake removal services across Kerala. We are committed to wildlife conservation 
                            and educating the public about the importance of snakes in our ecosystem.
                        </p>
                        <p class="text-gray-600 dark:text-gray-300 mb-8 text-lg">
                            Our team includes herpetologists and wildlife experts who are trained to identify all snake 
                            species found in Kerala and handle them with care and respect.
                        </p>
                        <a href="#contact" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300">
                            Contact Our Team
                        </a>
                    </div>
                    
                    <div class="lg:w-1/2 mt-10 lg:mt-0">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="overflow-hidden rounded-lg">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Team Member" class="w-full h-auto transform hover:scale-110 transition duration-500">
                            </div>
                            <div class="overflow-hidden rounded-lg">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Team Member" class="w-full h-auto transform hover:scale-110 transition duration-500">
                            </div>
                            <div class="overflow-hidden rounded-lg">
                                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Team Member" class="w-full h-auto transform hover:scale-110 transition duration-500">
                            </div>
                            <div class="overflow-hidden rounded-lg">
                                <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="Team Member" class="w-full h-auto transform hover:scale-110 transition duration-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-16 bg-gray-100 dark:bg-gray-900">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Contact Us</h2>
                    <div class="w-20 h-1 bg-blue-600 mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Contact Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Get in Touch</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Emergency Hotline</h4>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                                        <a href="tel:+1234567890" class="text-blue-600 dark:text-blue-400 hover:underline">(123) 456-7890</a> (24/7)
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Email</h4>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                                        <a href="mailto:info@sarpa.com" class="text-blue-600 dark:text-blue-400 hover:underline">info@sarpa.com</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Service Area</h4>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                                        Serving all districts in Kerala and surrounding regions
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Business Hours</h4>
                                    <p class="mt-1 text-gray-600 dark:text-gray-300">
                                        24 hours a day, 7 days a week
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">Send a Message</h3>
                        
                        <form method="POST" action="submit-contact.php">
                            <div class="mb-6">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" required>
                            </div>
                            
                            <div class="mb-6">
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" required>
                            </div>
                            
                            <div class="mb-6">
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" required>
                            </div>
                            
                            <div class="mb-6">
                                <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Message</label>
                                <textarea id="message" name="message" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                            </div>
                            
                            <button type="submit" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
