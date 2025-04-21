<?php
session_start();
include 'dbConnection.php';

$resetLink = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Get user by email
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id);
        mysqli_stmt_fetch($stmt);

        $token = bin2hex(random_bytes(16));
        $expires_at = time() + 3600; // valid for 1 hour

        // Insert token into password_resets table
        $stmt2 = mysqli_prepare($conn, "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "isi", $user_id, $token, $expires_at);
        mysqli_stmt_execute($stmt2);

        // Generate reset link
        $resetLink = "/reset_password.php?token=$token";
    } else {
        $message = "No user found with that email.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Send Reset Link</button>
    </form>

    <?php if ($resetLink): ?>
        <p style="color:green;">Reset Link (valid only for 1 hour) : <a href="<?= $resetLink ?>"><?= $resetLink ?></a></p>
    <?php elseif ($message): ?>
        <p style="color:red;"><?= $message ?></p>
    <?php endif; ?>
</body>
</html>
