<?php
include 'dbConnection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $latitude = !empty($_POST['latitude']) ? htmlspecialchars(trim($_POST['latitude'])) : 0;
    $longitude = !empty($_POST['longitude']) ? htmlspecialchars(trim($_POST['longitude'])) : 0;   

    // Use procedural MySQLi
    $sql = "INSERT INTO users (role, first_name, last_name, address, email, password, latitude, longitude) 
            VALUES ('user', ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssd", $first_name, $last_name, $address, $email, $password, $latitude, $longitude);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Registration successful. <a href='login.php'>Login here</a>.";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration - SARPA</title>
    <link rel="stylesheet" href="style.css">
    <script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
    </script>
</head>
<body onload="getLocation()">
    <h2>Register as User</h2>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br>
        <textarea name="address" placeholder="Address" required></textarea><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <input type="submit" value="Register">
    </form>
    <p><?= $message ?></p>
</body>
</html>
