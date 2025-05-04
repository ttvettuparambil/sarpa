<?php
session_start();
include 'dbConnection.php';
// Timeout duration in seconds (10 minutes)
$timeout_duration = 600; // 10 * 60
// Check for inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
// Redirect if not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Fetch user info
$user_id = $_SESSION['user_id'];

$sql = "SELECT first_name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

// Fetch user's past snake sightings
$sightings_sql = "SELECT s.id, s.complaint_id, s.district, s.city, 
                 CONCAT(s.address_line1, IF(s.address_line2 IS NOT NULL, CONCAT(', ', s.address_line2), '')) AS location,
                 s.datetime AS sighting_date, s.description, s.image_path
                 FROM snake_sightings s 
                 WHERE s.user_email = ? 
                 ORDER BY s.datetime DESC";
$sightings_stmt = mysqli_prepare($conn, $sightings_sql);
mysqli_stmt_bind_param($sightings_stmt, "s", $user['email']);
mysqli_stmt_execute($sightings_stmt);
$sightings_result = mysqli_stmt_get_result($sightings_stmt);

// Check if there are any sightings
$has_sightings = mysqli_num_rows($sightings_result) > 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - SARPA</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .chart-title {
            text-align: center;
            margin-bottom: 15px;
            color: #2a7d46;
        }
        
        .filter-buttons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .filter-button {
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            padding: 8px 15px;
            margin: 0 5px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-button:hover {
            background-color: #e0e0e0;
        }
        
        .filter-button.active {
            background-color: #2a7d46;
            color: white;
            border-color: #2a7d46;
        }
        
        .table-container {
            width: 90%;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .section-title {
            text-align: center;
            margin-bottom: 20px;
            color: #2a7d46;
        }

        .no-data-message {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        .view-btn {
            display: inline-block;
            padding: 5px 10px;
            background-color: #2a7d46;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .view-btn:hover {
            background-color: #1e5d33;
        }

        #sightingsTable {
            width: 100%;
            border-collapse: collapse;
        }

        #sightingsTable th, #sightingsTable td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h2>Welcome to SARPA, <?= htmlspecialchars($user['first_name']) ?> 👋</h2>
    <a href="/user_profile.php">User Profile</a>
    <p><strong>Session User ID:</strong> <?= $_SESSION['user_id'] ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

    <hr>
    <a href="snake-sighting-form.php">📢 Report Snake Sighting</a><br>
    <a href="user_log.php">📋 View Activity Log</a><br>
    <a href="logout.php">🔓 Logout</a>

    <!-- Snake Sightings Chart -->
    <div class="chart-container">
        <h3 class="chart-title">Snake Sightings Over Time</h3>
        <div class="filter-buttons">
            <button id="week-btn" class="filter-button">Week</button>
            <button id="month-btn" class="filter-button active">Month</button>
            <button id="year-btn" class="filter-button">Year</button>
        </div>
        <canvas id="sightingsChart"></canvas>
    </div>
    
    <!-- Past Sightings Table -->
    <div class="table-container">
        <h3 class="section-title">Past Sightings</h3>
        
        <?php if ($has_sightings): ?>
            <table id="sightingsTable" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Complaint ID</th>
                        <th>District</th>
                        <th>Location</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sighting = mysqli_fetch_assoc($sightings_result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($sighting['id']) ?></td>
                            <td><?= htmlspecialchars($sighting['complaint_id']) ?></td>
                            <td><?= htmlspecialchars($sighting['district']) ?></td>
                            <td><?= htmlspecialchars($sighting['location']) ?></td>
                            <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($sighting['sighting_date']))) ?></td>
                            <td>
                                <a href="sighting-summary.php?complaint_id=<?= $sighting['complaint_id'] ?>" class="view-btn">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data-message">
                <p>No past sightings found. When you report snake sightings, they will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Session Expiry Warning Message -->
    <div id="session-expiry-warning">
        <p>Your session is about to expire due to inactivity. Do you want to stay logged in?</p>
        <button onclick="extendSession()">Stay Logged In</button>
    </div>

    <script>
        // Session timeout management
        var sessionTimeout = 600000; // 10 minutes in milliseconds
        var warningTime = 480000; // 8 minutes in milliseconds (show warning 2 minutes before timeout)
        var inactivityTimer;
        var warningTimer;

        // Function to reset timers when user is active (mouse movement, clicks, etc.)
        function resetTimers() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);

            // Reset inactivity timer
            inactivityTimer = setTimeout(logOut, sessionTimeout);

            // Show warning 2 minutes before session expires
            warningTimer = setTimeout(showSessionWarning, warningTime);
        }

        // Function to show session expiry warning
        function showSessionWarning() {
            document.getElementById('session-expiry-warning').style.display = 'block';
        }

        // Function to extend session
        function extendSession() {
            // Make a simple AJAX request to reset session timeout on the server
            fetch('extend_session.php')
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    // Reset timers after extending session
                    resetTimers();
                    document.getElementById('session-expiry-warning').style.display = 'none';
                });
        }

        // Function to log out the user
        function logOut() {
            window.location.href = 'logout.php';
        }

        // Chart.js functionality
        let sightingsChart;
        let currentPeriod = 'month'; // Default period

        // Function to initialize the chart
        function initChart() {
            const ctx = document.getElementById('sightingsChart').getContext('2d');
            
            sightingsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Snake Sightings',
                        data: [],
                        borderColor: '#2a7d46',
                        backgroundColor: 'rgba(42, 125, 70, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0 // Only show whole numbers
                            },
                            title: {
                                display: true,
                                text: 'Number of Sightings'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return `Sightings: ${context.parsed.y}`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Load initial data (month view by default)
            loadChartData('month');
        }

        // Function to load chart data based on selected period
        function loadChartData(period) {
            // Get user email from PHP session
            const userEmail = '<?= htmlspecialchars($user['email']) ?>';
            
            fetch(`get_sighting_stats.php?period=${period}&user_email=${encodeURIComponent(userEmail)}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart data
                    sightingsChart.data.labels = data.labels;
                    sightingsChart.data.datasets[0].data = data.values;
                    
                    // Check if there's any data
                    const hasData = data.values.some(value => value > 0);
                    
                    // Update chart title based on period
                    let titleText = 'Your Snake Sightings ';
                    switch(period) {
                        case 'week':
                            titleText += 'This Week';
                            break;
                        case 'month':
                            titleText += 'This Month';
                            break;
                        case 'year':
                            titleText += 'This Year';
                            break;
                    }
                    
                    // Add message if no data
                    if (!hasData) {
                        titleText += ' (No sightings reported)';
                    }
                    
                    document.querySelector('.chart-title').textContent = titleText;
                    
                    // Update the chart
                    sightingsChart.update();
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }

        // Add event listeners for filter buttons
        document.addEventListener('DOMContentLoaded', function() {
            const weekBtn = document.getElementById('week-btn');
            const monthBtn = document.getElementById('month-btn');
            const yearBtn = document.getElementById('year-btn');
            
            weekBtn.addEventListener('click', function() {
                setActivePeriod('week');
            });
            
            monthBtn.addEventListener('click', function() {
                setActivePeriod('month');
            });
            
            yearBtn.addEventListener('click', function() {
                setActivePeriod('year');
            });
            
            // Function to set active period and update UI
            function setActivePeriod(period) {
                // Update active button
                weekBtn.classList.toggle('active', period === 'week');
                monthBtn.classList.toggle('active', period === 'month');
                yearBtn.classList.toggle('active', period === 'year');
                
                // Update current period and reload data
                currentPeriod = period;
                loadChartData(period);
            }
            
            // Initialize DataTable for sightings
            if (document.getElementById('sightingsTable')) {
                $('#sightingsTable').DataTable({
                    responsive: true,
                    order: [[4, 'desc']], // Sort by date column (index 4) in descending order
                    language: {
                        emptyTable: "No past sightings found"
                    },
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]]
                });
            }
        });

        // Add event listeners to reset timers based on user activity
        window.onload = function() {
            resetTimers();
            // Initialize chart after DOM is fully loaded
            if (document.getElementById('sightingsChart')) {
                initChart();
            }
        };
        document.onmousemove = resetTimers;
        document.onclick = resetTimers;
        document.onkeypress = resetTimers;
    </script>
</body>
</html>
