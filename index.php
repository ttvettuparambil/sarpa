<?php
session_start();
require 'dbConnection.php';

// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);

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

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-16 bg-white dark:bg-gray-800">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">What Our Clients Say</h2>
                    <div class="w-20 h-1 bg-blue-600 mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4 italic">
                            "I found a snake in my garage and panicked. The snake catcher arrived within 30 minutes, 
                            safely removed it, and even showed me how to snake-proof my property. Excellent service!"
                        </p>
                        <div class="flex items-center">
                            <div class="font-medium text-gray-900 dark:text-white">Sarah J.</div>
                            <span class="mx-2 text-gray-400">|</span>
                            <div class="text-gray-500 dark:text-gray-400">Homeowner</div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 2 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4 italic">
                            "As a school administrator, I was impressed with their educational program. 
                            The students loved learning about snakes in a safe, controlled environment."
                        </p>
                        <div class="flex items-center">
                            <div class="font-medium text-gray-900 dark:text-white">Michael T.</div>
                            <span class="mx-2 text-gray-400">|</span>
                            <div class="text-gray-500 dark:text-gray-400">School Principal</div>
                        </div>
                    </div>
                    
                    <!-- Testimonial 3 -->
                    <div class="bg-gray-50 dark:bg-gray-700 p-8 rounded-lg shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400 flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 mb-4 italic">
                            "Quick, professional, and knowledgeable. They removed a venomous snake from our warehouse 
                            without any issues. Highly recommend their services for businesses."
                        </p>
                        <div class="flex items-center">
                            <div class="font-medium text-gray-900 dark:text-white">Lisa R.</div>
                            <span class="mx-2 text-gray-400">|</span>
                            <div class="text-gray-500 dark:text-gray-400">Business Owner</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Educational Videos Section -->
        <section id="educational-videos" class="py-16 bg-gray-100 dark:bg-gray-900">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Educational Videos</h2>
                    <div class="w-20 h-1 bg-blue-600 mx-auto"></div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Video Card 1 -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                        <div class="relative pb-[56.25%] h-0">
                            <video
                                id="player1"
                                class="video-js vjs-default-skin vjs-big-play-centered absolute top-0 left-0 w-full h-full"
                                controls
                                preload="auto"
                                data-setup='{"techOrder": ["youtube"], "sources": [{"type": "video/youtube", "src": "https://www.youtube.com/watch?v=1gxf6flnvNA"}]}'
                            >
                            </video>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Snake Safety and Awareness</h3>
                            <p class="text-gray-600 dark:text-gray-300">Learn essential tips about snake safety and how to handle snake encounters in your area.</p>
                        </div>
                    </div>

                    <!-- Video Card 2 -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                        <div class="relative pb-[56.25%] h-0">
                            <video
                                id="player2"
                                class="video-js vjs-default-skin vjs-big-play-centered absolute top-0 left-0 w-full h-full"
                                controls
                                preload="auto"
                                data-setup='{"techOrder": ["youtube"], "sources": [{"type": "video/youtube", "src": "https://www.youtube.com/watch?v=VQRLujxTm3c"}]}'
                            >
                            </video>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Snake Rescue Techniques</h3>
                            <p class="text-gray-600 dark:text-gray-300">Professional snake rescue techniques and best practices for handling different snake species.</p>
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

    <!-- Add Video.js and YouTube tech -->
    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
    <script>
        class VideoTracker {
            constructor(player, videoId) {
                this.player = player;
                this.videoId = videoId;
                this.lastSavedTime = 0;
                this.isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
                this.trackingInterval = null;
                this.isInitialLoad = true;
                
                this.initialize();
            }
            
            initialize() {
                if (this.isLoggedIn) {
                    this.setupEventListeners();
                    this.loadProgress();
                }
            }
            
            async loadProgress() {
                try {
                    const response = await fetch(`video-progress.php?video_id=${this.videoId}`);
                    const data = await response.json();
                    if (data.timestamp > 0) {
                        // Store the timestamp to use when the video is ready
                        this.savedTimestamp = data.timestamp;
                    }
                } catch (error) {
                    console.error('Error loading video progress:', error);
                }
            }
            
            setupEventListeners() {
                // Listen for when the YouTube source is ready
                this.player.on('loadedmetadata', () => {
                    if (this.isInitialLoad && this.savedTimestamp) {
                        this.player.currentTime(this.savedTimestamp);
                        this.isInitialLoad = false;
                    }
                });

                // Start tracking when video starts playing
                this.player.on('play', () => {
                    this.startTracking();
                });

                this.player.on('pause', () => {
                    this.stopTracking();
                    // Save progress on pause
                    const currentTime = Math.floor(this.player.currentTime());
                    this.saveProgress(currentTime);
                });

                this.player.on('ended', () => {
                    this.stopTracking();
                    // Save progress when video ends
                    const currentTime = Math.floor(this.player.currentTime());
                    this.saveProgress(currentTime);
                });
            }

            startTracking() {
                // Clear any existing interval
                this.stopTracking();
                
                // Start new tracking interval
                this.trackingInterval = setInterval(() => {
                    const currentTime = Math.floor(this.player.currentTime());
                    if (currentTime - this.lastSavedTime >= 30) {
                        this.saveProgress(currentTime);
                        this.lastSavedTime = currentTime;
                    }
                }, 1000);
            }

            stopTracking() {
                if (this.trackingInterval) {
                    clearInterval(this.trackingInterval);
                    this.trackingInterval = null;
                }
            }
            
            async saveProgress(timestamp) {
                try {
                    const formData = new FormData();
                    formData.append('video_id', this.videoId);
                    formData.append('timestamp', timestamp);
                    
                    const response = await fetch('video-progress.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to save progress');
                    }
                    
                    console.log('Progress saved for video', this.videoId, 'at', timestamp);
                } catch (error) {
                    console.error('Error saving video progress:', error);
                }
            }
        }

        // Function to extract YouTube video ID from URL
        function getYouTubeId(url) {
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = url.match(regExp);
            return (match && match[2].length === 11) ? match[2] : null;
        }

        // Initialize Video.js player
        document.addEventListener('DOMContentLoaded', function() {
            // Get all video elements
            const videoElements = document.querySelectorAll('.video-js');
            
            videoElements.forEach((element, index) => {
                // Get the data-setup attribute
                const setupData = JSON.parse(element.getAttribute('data-setup'));
                // Extract video ID from the YouTube URL
                const videoId = getYouTubeId(setupData.sources[0].src);
                
                if (videoId) {
                    // Initialize the player
                    const player = videojs(element.id, {
                        youtube: {
                            ytControls: 2,
                            rel: 0
                        }
                    });
                    
                    // Initialize tracker with the extracted video ID
                    new VideoTracker(player, videoId);
                }
            });
        });
    </script>
</body>
</html>
