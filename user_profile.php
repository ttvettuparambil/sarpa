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
    'alternate_phone' => ''
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
?>

<h3>Update Profile Information</h3>
<form method="POST" action="update_profile.php">
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
