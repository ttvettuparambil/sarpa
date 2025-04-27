<?php
session_start();

$userData = [];
$userLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $userLoggedIn = true;
    require 'dbConnection.php'; // your DB connection
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

            <label for="image">Upload Snake Image:</label>
            <input type="file" name="image" accept="image/*"><br>

            <label>
                <input type="checkbox" id="toggleDescription" onclick="toggleDescription()"> Add Snake Description
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
