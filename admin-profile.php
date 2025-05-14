<?php
session_start();
require 'dbConnection.php';
require 'components/alerts.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = $_POST['gender'] ?? null;
    $occupation = $_POST['occupation'] ?? null;
    $education_level = $_POST['education_level'] ?? null;
    $bio = $_POST['bio'] ?? null;
    $alternate_email = $_POST['alternate_email'] ?? null;
    $alternate_phone = $_POST['alternate_phone'] ?? null;

    // Check if profile exists
    $checkStmt = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("
            UPDATE user_profiles 
            SET dob = ?, gender = ?, occupation = ?, education_level = ?, 
                bio = ?, alternate_email = ?, alternate_phone = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("sssssssi", $dob, $gender, $occupation, $education_level, 
                         $bio, $alternate_email, $alternate_phone, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("
            INSERT INTO user_profiles 
            (user_id, dob, gender, occupation, education_level, bio, alternate_email, alternate_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssss", $user_id, $dob, $gender, $occupation, 
                         $education_level, $bio, $alternate_email, $alternate_phone);
    }

    if ($stmt->execute()) {
        set_alert('success', 'Profile updated successfully!');
    } else {
        set_alert('error', 'Error updating profile: ' . $conn->error);
    }
}

// Fetch current profile data
$stmt = $conn->prepare("
    SELECT u.first_name, u.last_name, u.email, p.* 
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - SARPA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#1E40AF'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'components/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">My Profile</h2>
                    <p class="text-gray-600 dark:text-gray-400">Manage your personal information</p>
                </div>

                <?php require 'components/alerts.php'; ?>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <form method="POST" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    First Name
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                       disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Last Name
                                </label>
                                <input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                       disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Primary Email
                                </label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                       disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Date of Birth
                                </label>
                                <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Gender
                                </label>
                                <select name="gender" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Occupation
                                </label>
                                <input type="text" name="occupation" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Education Level
                                </label>
                                <input type="text" name="education_level" value="<?php echo htmlspecialchars($user['education_level'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bio
                            </label>
                            <textarea name="bio" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Alternate Email
                                </label>
                                <input type="email" name="alternate_email" value="<?php echo htmlspecialchars($user['alternate_email'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Alternate Phone
                                </label>
                                <input type="tel" name="alternate_phone" value="<?php echo htmlspecialchars($user['alternate_phone'] ?? ''); ?>" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html> 