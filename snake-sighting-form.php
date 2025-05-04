<?php
session_start();

$userData = [];
$userLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $userLoggedIn = true;
    require 'dbConnection.php'; // your DB connection
    
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
<html>
<head>
    <title>Snake Sighting Form</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    <style>
        .dropzone {
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 25px;
            text-align: center;
            margin: 10px 0;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .dropzone.dragover {
            border-color: #4CAF50;
            background-color: rgba(76, 175, 80, 0.1);
        }
        .dropzone.error {
            border-color: #f44336;
            background-color: rgba(244, 67, 54, 0.1);
        }
        .dropzone p {
            margin: 5px 0;
        }
        .dropzone .icon {
            font-size: 32px;
            color: #666;
        }
        .file-info {
            margin-top: 10px;
            display: none;
        }
        .file-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
            border-radius: 5px;
        }
        .error-message {
            color: #f44336;
            margin-top: 5px;
            display: none;
        }
        .file-input-container {
            display: none;
        }
    </style>
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
        flatpickr("input[name='sighting_time']", {
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
            const dateTimeInput = document.querySelector('input[name="sighting_time"]');
            if (!dateTimeInput.value) {
                event.preventDefault();
                alert('Please select a date and time for the snake sighting.');
            }
        });
        
        // Toggle description section
        document.getElementById('toggleDescription').addEventListener('click', function() {
            const description = document.getElementById('snakeDescriptionContainer');
            description.style.display = this.checked ? 'block' : 'none';
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
        const districtSelect = document.querySelector('select[name="district"]');
        if (districtSelect && userData.district) {
            for (let i = 0; i < districtSelect.options.length; i++) {
                if (districtSelect.options[i].value === userData.district) {
                    districtSelect.selectedIndex = i;
                    break;
                }
            }
        }
        
        // Set text input fields
        document.querySelector('input[name="city"]').value = userData.city || '';
        document.querySelector('input[name="postcode"]').value = userData.postcode || '';
        document.querySelector('input[name="address1"]').value = userData.address_line1 || '';
        document.querySelector('input[name="address2"]').value = userData.address_line2 || '';
        document.querySelector('input[name="landmark"]').value = userData.landmark || '';
    }
    
    // Function to clear address fields
    function clearAddressFields() {
        document.querySelector('select[name="district"]').selectedIndex = 0;
        document.querySelector('input[name="city"]').value = '';
        document.querySelector('input[name="postcode"]').value = '';
        document.querySelector('input[name="address1"]').value = '';
        document.querySelector('input[name="address2"]').value = '';
        document.querySelector('input[name="landmark"]').value = '';
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
            dropzone.classList.add('dragover');
        }
        
        function unhighlight() {
            dropzone.classList.remove('dragover');
            dropzone.classList.remove('error');
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
            errorMessage.style.display = 'none';
            dropzone.classList.remove('error');
            
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
            fileInfo.style.display = 'block';
            
            // Show image preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
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
            errorMessage.style.display = 'block';
            dropzone.classList.add('error');
            
            // Clear file input
            fileInput.value = '';
            
            // Hide preview and file info
            imagePreview.style.display = 'none';
            fileInfo.style.display = 'none';
        }
    }
</script>
</head>
<body>
    <h2>🐍 Report Snake Sighting</h2>
    <form method="POST" action="submit-sighting.php" enctype="multipart/form-data">
        <fieldset>
            <legend>Address Details</legend>
            <?php if ($userLoggedIn): ?>
            <div style="margin-bottom: 15px;">
                <label>
                    <input type="checkbox" id="populateAddress"> Populate address from account
                </label>
            </div>
            <?php endif; ?>
            <label for="district">District (Kerala):</label>
            <select name="district" required>
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
            </select><br>

            <label for="city">City/Town:</label>
            <input type="text" name="city" required><br>

            <label for="postcode">Postcode:</label>
            <input type="text" name="postcode"><br>

            <label for="address1">Address Line 1:</label>
            <input type="text" name="address1" required><br>

            <label for="address2">Address Line 2:</label>
            <input type="text" name="address2"><br>

            <label for="landmark">Landmark:</label>
            <input type="text" name="landmark"><br>
        </fieldset>

        <fieldset>
            <legend>Snake Details</legend>
            <label for="sighting_time">Date and Time of Sighting:</label>
            <input type="datetime-local" name="sighting_time" required><br>

            <label>Upload Snake Image (Max 5MB):</label>
            <div id="dropzone" class="dropzone">
                <div class="icon">📁</div>
                <p>Drag & drop an image here or click to select</p>
                <p class="small">Only image files allowed (JPG, PNG, GIF, etc.)</p>
                <div class="error-message" id="error-message"></div>
                <img id="image-preview" class="file-preview" src="" alt="Image preview">
                <div id="file-info" class="file-info"></div>
            </div>
            <div class="file-input-container">
                <input type="file" id="file-input" name="image" accept="image/*">
            </div><br>

            <label>
                <input type="checkbox" id="toggleDescription"> Add Snake Description
            </label>

            <div id="snakeDescriptionContainer" style="display:none;">
                <label for="description">Snake Description:</label><br>
                <textarea name="description" rows="4" cols="50"></textarea>
            </div>
        </fieldset>

        <fieldset>
            <legend>Reporter Info (Optional)</legend>
            <label for="name">Your Name:</label>
            <input type="text" name="name" value="<?php echo isset($userData['first_name']) && isset($userData['last_name']) ? htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']) : ''; ?>"><br>

            <label for="phone">Phone:</label>
            <input type="text" name="phone" value="<?php echo isset($userData['phone']) ? htmlspecialchars($userData['phone']) : ''; ?>"><br>

            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>"><br>
        </fieldset>

        <button type="submit">📤 Submit Sighting</button>

    </form>
  
</body>
</html>
