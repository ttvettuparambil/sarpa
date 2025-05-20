<?php
session_start();

$userData = [];
$userLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $userLoggedIn = true;
    require 'dbConnection.php';
// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);
 // your DB connection
    
    // Log that user started a snake sighting form directly with SQL
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
    
    // Log the snake sighting started activity
    $user_id = $_SESSION['user_id'];
    $action_type = "SNAKE_SIGHTING_STARTED";
    $action_description = "Snake sighting form started";
    
    $log_stmt = $conn->prepare("INSERT INTO account_activity (user_id, action_type, action_description, ip_address, browser, device_type) VALUES (?, ?, ?, ?, ?, ?)");
    $log_stmt->bind_param("isssss", $user_id, $action_type, $action_description, $ip_address, $browser, $device_type);
    $log_stmt->execute();
    $log_stmt->close();
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone, district, city, postcode, address_line1, address_line2, landmark FROM users WHERE id = ?");

    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Snake Sighting - SARPA</title>
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <span class="text-3xl mr-2">🐍</span> Report Snake Sighting
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Fill out this form to report a snake sighting in your area. Our team will respond promptly.
                </p>
            </div>
            
            <form method="POST" action="submit-sighting.php" enctype="multipart/form-data" class="p-6">
                <!-- Address Details Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Address Details</h2>
                    
                    <?php if ($userLoggedIn): ?>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="populateAddress" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Populate address from account</span>
                        </label>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">District (Kerala)</label>
                            <select id="district" name="district" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">--Select--</option>
                                <option value="Thiruvananthapuram">Thiruvananthapuram</option>
                                <option value="Kollam">Kollam</option>
                                <option value="Pathanamthitta">Pathanamthitta</option>
                                <option value="Alappuzha">Alappuzha</option>
                                <option value="Kottayam">Kottayam</option>
                                <option value="Idukki">Idukki</option>
                                <option value="Ernakulam">Ernakulam</option>
                                <option value="Thrissur">Thrissur</option>
                                <option value="Palakkad">Palakkad</option>
                                <option value="Malappuram">Malappuram</option>
                                <option value="Kozhikode">Kozhikode</option>
                                <option value="Wayanad">Wayanad</option>
                                <option value="Kannur">Kannur</option>
                                <option value="Kasaragod">Kasaragod</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City/Town</label>
                            <input type="text" id="city" name="city" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Postcode</label>
                            <input type="text" id="postcode" name="postcode" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="address1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address Line 1</label>
                            <input type="text" id="address1" name="address1" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="address2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address Line 2</label>
                            <input type="text" id="address2" name="address2" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="landmark" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Landmark</label>
                            <input type="text" id="landmark" name="landmark" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                
                <!-- Snake Details Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Snake Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="sighting_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date and Time of Sighting</label>
                            <input type="datetime-local" id="sighting_time" name="sighting_time" required class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Snake Image (Max 5MB)</label>
                            
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
                                <input type="file" id="file-input" name="image" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="toggleDescription" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Add Snake Description</span>
                            </label>
                            
                            <div id="snakeDescriptionContainer" class="mt-4 hidden">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Snake Description</label>
                                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reporter Info Section -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Reporter Info (Optional)</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($userData['first_name']) && isset($userData['last_name']) ? htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo isset($userData['phone']) ? htmlspecialchars($userData['phone']) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-300 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        Submit Sighting
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'components/footer.php'; ?>
    
    <script>
    // Store user data in JavaScript variables
    <?php if ($userLoggedIn && $userData): ?>
    const userData = {
        district: <?php echo json_encode($userData['district'] ?? ''); ?>,
        city: <?php echo json_encode($userData['city'] ?? ''); ?>,
        postcode: <?php echo json_encode($userData['postcode'] ?? ''); ?>,
        address_line1: <?php echo json_encode($userData['address_line1'] ?? ''); ?>,
        address_line2: <?php echo json_encode($userData['address_line2'] ?? ''); ?>,
        landmark: <?php echo json_encode($userData['landmark'] ?? ''); ?>,
        name: <?php echo json_encode(isset($userData['first_name']) && isset($userData['last_name']) ? $userData['first_name'] . ' ' . $userData['last_name'] : ''); ?>,
        phone: <?php echo json_encode($userData['phone'] ?? ''); ?>,
        email: <?php echo json_encode($userData['email'] ?? ''); ?>
    };
    <?php else: ?>
    const userData = null;
    <?php endif; ?>
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize drag and drop functionality
        initDragAndDrop();
        
        // Set up date range constraints for snake sighting
        const now = new Date();
        const oneWeekAgo = new Date();
        oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
        
        // Initialize Flatpickr for date-time selection
        flatpickr("#sighting_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minDate: oneWeekAgo,
            maxDate: now,
            defaultDate: now, // Set default to current date
            disable: [
                function(date) {
                    // Disable future dates
                    return date > now;
                },
                function(date) {
                    // Disable dates older than 1 week
                    return date < oneWeekAgo;
                }
            ],
            // Show a message when hovering over disabled dates
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                if (date > now) {
                    dayElem.title = "Future dates are not allowed";
                } else if (date < oneWeekAgo) {
                    dayElem.title = "Dates older than 1 week are not allowed";
                }
            }
        });
        
        // Add form validation
        document.querySelector('form').addEventListener('submit', function(event) {
            const dateTimeInput = document.querySelector('#sighting_time');
            if (!dateTimeInput.value) {
                event.preventDefault();
                alert('Please select a date and time for the snake sighting.');
            }
        });
        
        // Toggle description section
        document.getElementById('toggleDescription').addEventListener('change', function() {
            const description = document.getElementById('snakeDescriptionContainer');
            description.classList.toggle('hidden', !this.checked);
        });
        
        // Handle populate address checkbox
        const populateAddressCheckbox = document.getElementById('populateAddress');
        if (populateAddressCheckbox) {
            populateAddressCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    populateAddressFields();
                } else {
                    clearAddressFields();
                }
            });
        }
    });
    
    // Function to populate address fields with user data
    function populateAddressFields() {
        if (!userData) return;
        
        // Set district dropdown
        const districtSelect = document.querySelector('#district');
        if (districtSelect && userData.district) {
            for (let i = 0; i < districtSelect.options.length; i++) {
                if (districtSelect.options[i].value === userData.district) {
                    districtSelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        // Set text input fields
        document.querySelector('#city').value = userData.city || '';
        document.querySelector('#postcode').value = userData.postcode || '';
        document.querySelector('#address1').value = userData.address_line1 || '';
        document.querySelector('#address2').value = userData.address_line2 || '';
        document.querySelector('#landmark').value = userData.landmark || '';
    }
    
    // Function to clear address fields
    function clearAddressFields() {
        document.querySelector('#district').selectedIndex = 0;
        document.querySelector('#city').value = '';
        document.querySelector('#postcode').value = '';
        document.querySelector('#address1').value = '';
        document.querySelector('#address2').value = '';
        document.querySelector('#landmark').value = '';
    }
    
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
            
            // Check file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                showError('File size exceeds 5MB limit.');
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
