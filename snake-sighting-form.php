<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Snake Sighting Form</title>
    <link rel="stylesheet" href="style.css">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('toggleDescription').addEventListener('click', function() {
            const description = document.getElementById('snakeDescriptionContainer');
            description.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>
</head>
<body>
    <h2>🐍 Report Snake Sighting</h2>
    <form method="POST" action="submit-sighting.php" enctype="multipart/form-data">
        <fieldset>
            <legend>Address Details</legend>
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
            <input type="text" name="name"><br>

            <label for="phone">Phone:</label>
            <input type="text" name="phone"><br>

            <label for="email">Email:</label>
            <input type="email" name="email"><br>
        </fieldset>

        <button type="submit">📤 Submit Sighting</button>

    </form>
  
</body>
</html>
