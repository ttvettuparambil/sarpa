<?php
session_start();
include 'dbConnection.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Snake Sighting - SARPA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { max-width: 600px; margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="datetime-local"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .preview-img {
            margin-top: 10px;
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h2>🐍 Report Snake Sighting</h2>

    <div class="form-container">
        <form action="submit-sighting.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="location">Location of Sighting</label>
                <input type="text" id="location" name="location" required>
                <button type="button" onclick="getLocation()">📍 Auto Detect Location</button>
            </div>

            <div class="form-group">
                <label for="datetime">Date & Time of Sighting</label>
                <input type="datetime-local" id="datetime" name="datetime" required>
            </div>

            <div class="form-group">
                <label for="description">Description of the Snake</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="image">Upload Image (max 2MB)</label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                <img id="preview" class="preview-img" style="display:none;">
            </div>

            <div class="form-group">
                <label for="reporter_name">Your Name (Optional)</label>
                <input type="text" id="reporter_name" name="reporter_name">
            </div>

            <div class="form-group">
                <label for="reporter_contact">Your Contact (Optional)</label>
                <input type="text" id="reporter_contact" name="reporter_contact">
            </div>

            <button type="submit">📢 Submit Report</button>
        </form>
    </div>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    document.getElementById('location').value =
                        `Lat: ${position.coords.latitude}, Lng: ${position.coords.longitude}`;
                }, () => {
                    alert('Location access denied.');
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        function previewImage(event) {
            const preview = document.getElementById('preview');
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = 'block';
        }
    </script>
</body>
</html>
