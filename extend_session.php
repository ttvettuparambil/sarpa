<?php
session_start();

// Reset last activity time to extend the session
$_SESSION['last_activity'] = time();

echo "Session extended successfully.";
?>
