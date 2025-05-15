<?php
// user_profile.php (included or part of user_dashboard.php)
session_start();
require 'dbConnection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$profile = [
    'dob' => '',
    'gender' => '',
    'occupation' => '',
    'education_level' => '',
    'bio' => '',
    'alternate_email' => '',
    'alternate_phone' => '',
    'profile_picture' => ''
];

// Fetch profile if exists
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
$stmt->close();

// Display profile update message if set
if (isset($_SESSION['profile_msg'])) {
    $_SESSION['alert'] = [
        'type' => 'success',
        'message' => $_SESSION['profile_msg']
    ];
    unset($_SESSION['profile_msg']);
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SARPA</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <?php include 'components/header.php'; ?>
    
    <main class="flex-grow container mx-auto px-4 py-8">
        <?php include 'components/alerts.php'; ?>
        
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Update Profile Information</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your personal information and preferences</p>
            </div>
            
            <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Picture Section -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile Picture</label>
                        
                        <!-- Current profile picture display -->
                        <?php if (!empty($profile['profile_picture']) && file_exists('profile_pics/' . $profile['profile_picture'])): ?>
                            <div class="flex flex-col items-center mb-4">
                                <img src="profile_pics/<?= htmlspecialchars($profile['profile_picture']) ?>" 
                                     alt="Profile Picture" 
                                     class="w-32 h-32 object-cover rounded-full border-4 border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Current profile picture</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Drag & Drop Zone -->
                        <div id="dropzone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <div class="text-3xl text-gray-400 dark:text-gray-500 mb-2">📁</div>
                            <p class="text-gray-700 dark:text-gray-300">Drag & drop an image here or click to select</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Only image files allowed (JPG, PNG, GIF, etc.)</p>
                            <div id="error-message" class="text-red-500 mt-2 hidden"></div>
                            <img id="image-preview" class="max-w-full max-h-48 mx-auto mt-4 rounded-lg hidden" src="" alt="Image preview">
                            <div id="file-info" class="text-sm text-gray-600 dark:text-gray-400 mt-2 hidden"></div>
                        </div>
                        <div class="hidden">
                            <input type="file" id="file-input" name="profile_picture" accept="image/*">
                        </div>
                    </div>
                    
                    <!-- Personal Information -->
                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($profile['dob']) ?>" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                        <select id="gender" name="gender" 
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select</option>
                            <option value="Male" <?= $profile['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $profile['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= $profile['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="occupation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Occupation</label>
                        <input type="text" id="occupation" name="occupation" value="<?= htmlspecialchars($profile['occupation']) ?>" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label for="education_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Education Level</label>
                        <input type="text" id="education_level" name="education_level" value="<?= htmlspecialchars($profile['education_level']) ?>" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Short Bio</label>
                        <textarea id="bio" name="bio" rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"><?= htmlspecialchars($profile['bio']) ?></textarea>
                    </div>
                    
                    <div>
                        <label for="alternate_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alternate Email</label>
                        <input type="email" id="alternate_email" name="alternate_email" value="<?= htmlspecialchars($profile['alternate_email']) ?>" 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    
                    <div>
                        <label for="alternate_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alternate Phone</label>
                        <input type="tel" id="alternate_phone" name="alternate_phone" value="<?= htmlspecialchars($profile['alternate_phone']) ?>"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end">
                    <a href="user-dashboard.php" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 mr-4">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize drag and drop functionality
            initDragAndDrop();
        });
        
        // Function to initialize drag and drop functionality
        function initDragAndDrop() {
            const dropzone = document.getElementById('dropzone');
            const fileInput = document.getElementById('file-input');
            const errorMessage = document.getElementById('error-message');
            const imagePreview = document.getElementById('image-preview');
            const fileInfo = document.getElementById('file-info');
            
            // Check if drag and drop is supported
            const dragDropSupported = Modernizr.draganddrop;
            
            // If drag and drop is not supported, show only the file input
            if (!dragDropSupported) {
                dropzone.style.display = 'none';
                document.querySelector('.file-input-container').style.display = 'block';
                return;
            }
            
            // Add click event to dropzone to trigger file input
            dropzone.addEventListener('click', function() {
                fileInput.click();
            });
            
            // File input change event
            fileInput.addEventListener('change', function(e) {
                handleFiles(this.files);
            });
            
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Highlight drop area when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropzone.classList.add('border-blue-500');
                dropzone.classList.add('bg-blue-50');
                dropzone.classList.add('dark:bg-blue-900/20');
            }
            
            function unhighlight() {
                dropzone.classList.remove('border-blue-500');
                dropzone.classList.remove('bg-blue-50');
                dropzone.classList.remove('dark:bg-blue-900/20');
                dropzone.classList.remove('border-red-500');
                dropzone.classList.remove('bg-red-50');
                dropzone.classList.remove('dark:bg-red-900/20');
            }
            
            // Handle dropped files
            dropzone.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }
            
            // Process the files
            function handleFiles(files) {
                // Reset error state
                errorMessage.classList.add('hidden');
                
                // Check if any files were selected
                if (files.length === 0) {
                    showError('No file selected.');
                    return;
                }
                
                // Check if more than one file was selected
                if (files.length > 1) {
                    showError('Please select only one image.');
                    return;
                }
                
                const file = files[0];
                
                // Check if the file is an image
                if (!file.type.match('image.*')) {
                    showError('Please select an image file (JPEG, PNG, GIF, etc.).');
                    return;
                }
                
                // Check file size (max 2MB)
                const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                if (file.size > maxSize) {
                    showError('File size exceeds 2MB limit.');
                    return;
                }
                
                // Update the file input
                fileInput.files = files;
                
                // Show file info
                fileInfo.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                fileInfo.classList.remove('hidden');
                
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
            
            // Helper function to format file size
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' bytes';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }
            
            // Show error message
            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.classList.remove('hidden');
                dropzone.classList.add('border-red-500');
                dropzone.classList.add('bg-red-50');
                dropzone.classList.add('dark:bg-red-900/20');
                
                // Clear file input
                fileInput.value = '';
                
                // Hide preview and file info
                imagePreview.classList.add('hidden');
                fileInfo.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
