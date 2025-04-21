<?php
session_start();
include 'dbConnection.php';

if (!isset($_GET['token']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot_password.php");
    exit();
}


$token = $_GET['token'] ?? '';
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ?");
    $now = time();
    mysqli_stmt_bind_param($stmt, "si", $token, $now);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id);
        mysqli_stmt_fetch($stmt);

        // Update user's password
        $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($update, "si", $new_password, $user_id);
        mysqli_stmt_execute($update);

        // Delete used token
        $delete = mysqli_prepare($conn, "DELETE FROM password_resets WHERE token = ?");
        mysqli_stmt_bind_param($delete, "s", $token);
        mysqli_stmt_execute($delete);

        $success = true;
    } else {
        $error = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>

    <?php if ($success): ?>
        <p style="color:green;">Password updated. <a href="login.php">Login</a></p>
    <?php else: ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label>New Password:</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</body>
</html>
