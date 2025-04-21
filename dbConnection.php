<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$dbname = "sarpa";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
