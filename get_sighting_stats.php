<?php
// Include database connection
require_once 'dbConnection.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get period parameter (default to month if not provided)
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Get user_email parameter (required)
$user_email = isset($_GET['user_email']) ? $_GET['user_email'] : '';

// Validate period parameter
if (!in_array($period, ['week', 'month', 'year'])) {
    echo json_encode(['error' => 'Invalid period parameter']);
    exit;
}

// Validate user_email parameter
if (empty($user_email)) {
    echo json_encode(['error' => 'User email is required']);
    exit;
}

// Initialize arrays for chart data
$labels = [];
$values = [];

// Prepare SQL query based on period
switch ($period) {
    case 'week':
        // Get data for the current week (last 7 days)
        $sql = "SELECT DATE(datetime) as date, COUNT(*) as count 
                FROM snake_sightings 
                WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND user_email = ? 
                GROUP BY DATE(datetime) 
                ORDER BY date ASC";
        
        // Generate labels for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D, M j', strtotime($date)); // Format: Mon, Jan 1
            $values[$date] = 0; // Initialize with zero
        }
        break;
        
    case 'month':
        // Get data for the current month (last 30 days)
        $sql = "SELECT DATE(datetime) as date, COUNT(*) as count 
                FROM snake_sightings 
                WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND user_email = ? 
                GROUP BY DATE(datetime) 
                ORDER BY date ASC";
        
        // Generate labels for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('M j', strtotime($date)); // Format: Jan 1
            $values[$date] = 0; // Initialize with zero
        }
        break;
        
    case 'year':
        // Get data for the current year (last 12 months)
        $sql = "SELECT DATE_FORMAT(datetime, '%Y-%m') as month, COUNT(*) as count 
                FROM snake_sightings 
                WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                AND user_email = ? 
                GROUP BY DATE_FORMAT(datetime, '%Y-%m') 
                ORDER BY month ASC";
        
        // Generate labels for the last 12 months
        $labels = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime($month)); // Format: Jan 2023
            $values[$month] = 0; // Initialize with zero
        }
        break;
}

// Prepare and execute the query with user_email parameter
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $user_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    exit;
}

// Process query results
while ($row = mysqli_fetch_assoc($result)) {
    if ($period == 'year') {
        // For year view, use month as key
        $values[$row['month']] = (int)$row['count'];
    } else {
        // For week and month views, use date as key
        $values[$row['date']] = (int)$row['count'];
    }
}

// Convert values array to indexed array in the same order as labels
$data_values = [];
if ($period == 'year') {
    // For year view
    foreach ($labels as $index => $label) {
        $month = date('Y-m', strtotime("-" . (11 - $index) . " months"));
        $data_values[] = $values[$month] ?? 0;
    }
} else {
    // For week and month views
    foreach ($labels as $index => $label) {
        $date = '';
        if ($period == 'week') {
            $date = date('Y-m-d', strtotime("-" . (6 - $index) . " days"));
        } else { // month
            $date = date('Y-m-d', strtotime("-" . (29 - $index) . " days"));
        }
        $data_values[] = $values[$date] ?? 0;
    }
}

// Prepare final response
$response = [
    'labels' => $labels,
    'values' => $data_values
];

// Return JSON response
echo json_encode($response);
?>
