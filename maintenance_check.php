<?php
// This file serves as middleware to check if the site is in maintenance mode
function checkMaintenanceMode($conn) {
    // Get maintenance mode status from database
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $maintenance_mode = $row['setting_value'];
        
        // If maintenance mode is enabled and user is not a super_admin
        if ($maintenance_mode == '1' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin')) {
            // Redirect to maintenance page
            require 'maintenance_page.php';
            exit;
        }
    }
    
    // If not in maintenance mode or user is super_admin, continue normally
    return;
}
?>
