<?php
session_start();
require 'dbConnection.php';

if (!isset($_SESSION['complaint_id'])) {
    echo "No complaint data found.";
    exit;
}

$complaint_id = $_SESSION['complaint_id'];

// Fetch full complaint details from DB
$stmt = $conn->prepare("SELECT * FROM snake_sightings WHERE complaint_id = ?");
$stmt->bind_param("s", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Complaint not found.";
    exit;
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Snake Sighting Summary</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .summary-container {
      max-width: 700px;
      margin: 40px auto;
      padding: 20px;
      border: 2px solid #eee;
      border-radius: 10px;
      background-color: #fafafa;
    }
    .summary-container h2 {
      color: #2a7d46;
    }
    .summary-container p {
      margin: 10px 0;
    }
    .summary-container img {
      max-width: 100%;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="summary-container">
  <h2>Complaint Submitted Successfully</h2>
  <p><strong>Complaint ID:</strong> <?= htmlspecialchars($data['complaint_id']) ?></p>
  <hr>

  <h3>Submitted Details:</h3>
  <p><strong>District:</strong> <?= htmlspecialchars($data['district']) ?></p>
  <p><strong>City:</strong> <?= htmlspecialchars($data['city']) ?></p>
  <p><strong>Postcode:</strong> <?= htmlspecialchars($data['postcode']) ?></p>
  <p><strong>Address Line 1:</strong> <?= htmlspecialchars($data['address_line1']) ?></p>
  <p><strong>Address Line 2:</strong> <?= htmlspecialchars($data['address_line2']) ?></p>
  <p><strong>Landmark:</strong> <?= htmlspecialchars($data['landmark']) ?></p>
  <p><strong>Sighting Time:</strong> <?= $data['datetime'] ?></p>

  <?php if (!empty($data['description'])): ?>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($data['description'])) ?></p>
  <?php endif; ?>

  <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
    <p><strong>Image:</strong><br>
    <img src="<?= htmlspecialchars($data['image_path']) ?>" alt="Snake Image" style="max-width: 100%; height: auto;"></p>
  <?php endif; ?>

  <hr>
  <p><strong>Submitted by:</strong> 
    <?= htmlspecialchars($data['user_name'] ?? 'N/A') ?> 
    (<?= htmlspecialchars($data['user_email'] ?? 'N/A') ?> / <?= htmlspecialchars($data['user_phone'] ?? 'N/A') ?>)
  </p>
</div>


</body>
</html>
