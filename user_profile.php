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
    echo '<div class="message">' . $_SESSION['profile_msg'] . '</div>';
    unset($_SESSION['profile_msg']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
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
        .current-profile-pic {
            margin-bottom: 15px;
            text-align: center;
        }
        .current-profile-pic img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            border: 3px solid #ddd;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background-color: #e8f5e9;
            border-left: 5px solid #4CAF50;
            color: #333;
        }
    </style>
</head>
<body>
    <h3>Update Profile Information</h3>
    <form method="POST" action="update_profile.php" enctype="multipart/form-data">
        <div class="profile-picture-section">
            <label>Profile Picture:</label><br>
            
            <!-- Current profile picture display -->
            <?php if (!empty($profile['profile_picture']) && file_exists('profile_pics/' . $profile['profile_picture'])): ?>
                <div class="current-profile-pic">
                    <img src="profile_pics/<?= htmlspecialchars($profile['profile_picture']) ?>" alt="Profile Picture">
                    <p>Current profile picture</p>
                </div>
            <?php endif; ?>
            
            <!-- Drag & Drop Zone -->
            <div id="dropzone" class="dropzone">
                <div class="icon">📁</div>
                <p>Drag & drop an image here or click to select</p>
                <p class="small">Only image files allowed (JPG, PNG, GIF, etc.)</p>
                <div class="error-message" id="error-message"></div>
                <img id="image-preview" class="file-preview" src="" alt="Image preview">
                <div id="file-info" class="file-info"></div>
            </div>
            <div class="file-input-container">
                <input type="file" id="file-input" name="profile_picture" accept="image/*">
            </div>
        </div>

        <label>Date of Birth:</label><br>
        <input type="date" name="dob" value="<?= htmlspecialchars($profile['dob']) ?>"><br>

        <label>Gender:</label><br>
        <select name="gender">
            <option value="">Select</option>
            <option value="Male" <?= $profile['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $profile['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $profile['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
        </select><br>

        <label>Occupation:</label><br>
        <input type="text" name="occupation" value="<?= htmlspecialchars($profile['occupation']) ?>"><br>

        <label>Education Level:</label><br>
        <input type="text" name="education_level" value="<?= htmlspecialchars($profile['education_level']) ?>"><br>

        <label>Short Bio:</label><br>
        <textarea name="bio"><?= htmlspecialchars($profile['bio']) ?></textarea><br>

        <label>Alternate Email:</label><br>
        <input type="email" name="alternate_email" value="<?= htmlspecialchars($profile['alternate_email']) ?>"><br>

        <label>Alternate Phone:</label><br>
        <input type="text" name="alternate_phone" value="<?= htmlspecialchars($profile['alternate_phone']) ?>"><br><br>

        <input type="submit" value="Update Profile">
    </form>

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
</body>
</html>
