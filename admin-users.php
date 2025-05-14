<?php
session_start();
require 'dbConnection.php';
require 'components/alerts.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit;
}

// Handle search
$search = $_GET['search'] ?? '';
$search_condition = '';
$params = [];
$types = '';

if (!empty($search)) {
    $search_condition = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR role LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = 'ssss';
}

// Fetch users with their profiles
$query = "
    SELECT u.*, p.dob, p.gender, p.occupation, p.education_level, p.bio, 
           p.alternate_email, p.alternate_phone
    FROM users u
    LEFT JOIN user_profiles p ON u.id = p.user_id
    $search_condition
    ORDER BY u.created_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - SARPA</title>
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
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">User Management</h2>
                    <p class="text-gray-600 dark:text-gray-400">Search and manage system users</p>
                </div>

                <?php require 'components/alerts.php'; ?>

                <!-- Search Bar -->
                <div class="mb-6">
                    <form method="GET" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                   placeholder="Search by name, email, or role..." 
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($users as $user): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <span class="text-lg font-medium text-gray-600 dark:text-gray-300">
                                                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $user['role'] === 'super_admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                                ($user['role'] === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewUserDetails(<?php echo $user['id']; ?>)" 
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- User Details Modal -->
    <div id="userDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">User Details</h3>
                <div id="userDetailsContent" class="space-y-4">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="mt-4 flex justify-end">
                    <button onclick="closeUserDetails()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // User details modal functions
        function viewUserDetails(userId) {
            const modal = document.getElementById('userDetailsModal');
            const content = document.getElementById('userDetailsContent');
            
            // Show loading state
            content.innerHTML = '<div class="text-center">Loading...</div>';
            modal.classList.remove('hidden');
            
            // Fetch user details
            fetch(`get-user-details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    content.innerHTML = `
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.first_name} ${data.last_name}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.email}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.role}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Joined</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${new Date(data.created_at).toLocaleDateString()}</p>
                            </div>
                            ${data.dob ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.dob}</p>
                            </div>
                            ` : ''}
                            ${data.gender ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.gender}</p>
                            </div>
                            ` : ''}
                            ${data.occupation ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Occupation</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.occupation}</p>
                            </div>
                            ` : ''}
                            ${data.education_level ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Education Level</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.education_level}</p>
                            </div>
                            ` : ''}
                            ${data.bio ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.bio}</p>
                            </div>
                            ` : ''}
                            ${data.alternate_email ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alternate Email</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.alternate_email}</p>
                            </div>
                            ` : ''}
                            ${data.alternate_phone ? `
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alternate Phone</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">${data.alternate_phone}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                })
                .catch(error => {
                    content.innerHTML = '<div class="text-red-600">Error loading user details</div>';
                });
        }

        function closeUserDetails() {
            document.getElementById('userDetailsModal').classList.add('hidden');
        }
    </script>
</body>
</html> 