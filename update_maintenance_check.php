<?php
// This is a utility script to add maintenance mode check to all public-facing pages
// Run this script once to update all necessary files

$files_to_update = [
    'user-dashboard.php',
    'partner-dashboard.php',
    'register.php',
    'forgot_password.php',
    'reset_password.php',
    'snake-sighting-form.php',
    'partner-register.php',
    'user_profile.php'
];

$maintenance_check_code = <<<'EOD'

// Check if site is in maintenance mode
require_once 'maintenance_check.php';
checkMaintenanceMode($conn);

EOD;

foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Find the position after require/include dbConnection.php
        $pattern = '/(require|include).*dbConnection\.php\';/';
        
        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            
            // Insert the maintenance check code after the database connection
            $new_content = substr($content, 0, $position) . $maintenance_check_code . substr($content, $position);
            
            // Write the modified content back to the file
            file_put_contents($file, $new_content);
            
            echo "Updated $file with maintenance mode check.\n";
        } else {
            echo "Could not find database connection in $file.\n";
        }
    } else {
        echo "File $file does not exist.\n";
    }
}

echo "Maintenance mode check has been added to all specified files.\n";
?>
