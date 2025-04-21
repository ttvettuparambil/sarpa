<?php
session_start();
include 'dbConnection.php';

// Timeout duration in seconds (10 minutes)
$timeout_duration = 600; // 10 * 60
// Check for inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Fetch user info
$user_id = $_SESSION['user_id'];

$sql = "SELECT first_name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - SARPA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Welcome to SARPA, <?= htmlspecialchars($user['first_name']) ?> 👋</h2>

    <p><strong>Session User ID:</strong> <?= $_SESSION['user_id'] ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

    <hr>
    <a href="snake-sighting-form.php">📢 Report Snake Sighting</a><br>
    <a href="logout.php">🔓 Logout</a>

      <!-- Session Expiry Warning Message -->
      <div id="session-expiry-warning">
        <p>Your session is about to expire due to inactivity. Do you want to stay logged in?</p>
        <button onclick="extendSession()">Stay Logged In</button>
    </div>

    <script>
        var sessionTimeout = 600000; // 10 minutes in milliseconds
        var warningTime = 480000; // 8 minutes in milliseconds (show warning 2 minutes before timeout)
        var inactivityTimer;
        var warningTimer;

        // Function to reset timers when user is active (mouse movement, clicks, etc.)
        function resetTimers() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);

            // Reset inactivity timer
            inactivityTimer = setTimeout(logOut, sessionTimeout);

            // Show warning 2 minutes before session expires
            warningTimer = setTimeout(showSessionWarning, warningTime);
        }

        // Function to show session expiry warning
        function showSessionWarning() {
            document.getElementById('session-expiry-warning').style.display = 'block';
        }

        // Function to extend session
        function extendSession() {
            // Make a simple AJAX request to reset session timeout on the server
            fetch('extend_session.php')
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    // Reset timers after extending session
                    resetTimers();
                    document.getElementById('session-expiry-warning').style.display = 'none';
                });
        }

        // Function to log out the user
        function logOut() {
            window.location.href = 'logout.php';
        }

        // Add event listeners to reset timers based on user activity
        window.onload = resetTimers;
        document.onmousemove = resetTimers;
        document.onclick = resetTimers;
        document.onkeypress = resetTimers;

    </script>
</body>
</html>
