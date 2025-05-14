<?php
session_start();
require 'dbConnection.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

// Get district-wise sighting statistics
$districtStats = [];
$stmt = $conn->prepare("
    SELECT 
        district,
        COUNT(*) as total_sightings
    FROM snake_sightings 
    GROUP BY district
    ORDER BY total_sightings DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $districtStats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARPA Admin Dashboard</title>
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
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard Overview</h2>
                <p class="text-gray-600 dark:text-gray-400">Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
            </div>

            <!-- District-wise Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($districtStats as $stat): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><?php echo htmlspecialchars($stat['district']); ?></h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Sightings</span>
                            <span class="font-semibold text-gray-800 dark:text-white"><?php echo $stat['total_sightings']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Sightings Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Sightings</h3>
                </div>
                <div class="p-6">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-600 dark:text-gray-400">
                                <th class="pb-4">Complaint ID</th>
                                <th class="pb-4">Location</th>
                                <th class="pb-4">District</th>
                                <th class="pb-4">Reported At</th>
                                <th class="pb-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("
                                SELECT * FROM snake_sightings 
                                ORDER BY created_at DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr class="border-t dark:border-gray-700">
                                <td class="py-4 text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['complaint_id']); ?></td>
                                <td class="py-4 text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['address_line1']); ?></td>
                                <td class="py-4 text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['district']); ?></td>
                                <td class="py-4 text-gray-800 dark:text-white"><?php echo date('M d, Y H:i', strtotime($row['datetime'])); ?></td>
                                <td class="py-4">
                                    <a href="sighting-summary.php?complaint_id=<?php echo $row['complaint_id']; ?>" class="text-blue-600 hover:text-blue-800">View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // Logout confirmation
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html> 