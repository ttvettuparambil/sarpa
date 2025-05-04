<?php
session_start();
require 'dbConnection.php'; // Your DB connection

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$userId = $_SESSION['user_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="snake_sightings.csv"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, [
    'Complaint ID', 'District', 'City', 'Postcode', 
    'Address Line 1', 'Address Line 2', 'Landmark', 
    'Sighting Time', 'Description', 'Image Path'
],',', '"', '\\');

$stmt = $conn->prepare("SELECT complaint_id, district, city, postcode, address_line1, address_line2, landmark, datetime, description, image_path FROM snake_sightings WHERE user_email = (SELECT email FROM users WHERE id = ?)");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row, ',', '"', '\\');

}

fclose($output);
exit;
?>
