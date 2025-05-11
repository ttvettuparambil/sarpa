<?php
/**
 * Display alert messages with consistent styling
 * 
 * Usage:
 * $_SESSION['alert'] = [
 *   'type' => 'success', // or 'error', 'warning', 'info'
 *   'message' => 'Your message here'
 * ];
 */

// Check if there's an alert message in the session
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    $type = $alert['type'] ?? 'info';
    $message = $alert['message'] ?? '';
    
    // Define classes based on alert type
    $classes = [
        'success' => [
            'bg' => 'bg-green-100 dark:bg-green-900/30',
            'text' => 'text-green-800 dark:text-green-200',
            'border' => 'border-green-200 dark:border-green-800',
            'icon' => '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
        ],
        'error' => [
            'bg' => 'bg-red-100 dark:bg-red-900/30',
            'text' => 'text-red-800 dark:text-red-200',
            'border' => 'border-red-200 dark:border-red-800',
            'icon' => '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'
        ],
        'warning' => [
            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'text' => 'text-yellow-800 dark:text-yellow-200',
            'border' => 'border-yellow-200 dark:border-yellow-800',
            'icon' => '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>'
        ],
        'info' => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-800 dark:text-blue-200',
            'border' => 'border-blue-200 dark:border-blue-800',
            'icon' => '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
        ]
    ];
    
    // Get classes for the current alert type
    $alertClasses = $classes[$type] ?? $classes['info'];
    
    // Display the alert
    echo '<div class="flex items-center p-4 mb-6 border rounded ' . $alertClasses['bg'] . ' ' . $alertClasses['text'] . ' ' . $alertClasses['border'] . '" role="alert">';
    echo $alertClasses['icon'];
    echo '<div>' . $message . '</div>';
    echo '<button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 p-1.5 inline-flex h-8 w-8 ' . $alertClasses['bg'] . ' ' . $alertClasses['text'] . ' hover:opacity-75" data-dismiss-target="#alert" aria-label="Close">';
    echo '<span class="sr-only">Close</span>';
    echo '<svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
    echo '</button>';
    echo '</div>';
    
    // Clear the alert from the session
    unset($_SESSION['alert']);
}

// Function to set an alert message
if (!function_exists('set_alert')) {
    function set_alert($type, $message) {
        $_SESSION['alert'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}
?>

<script>
    // Add event listeners to close buttons for alerts
    document.addEventListener('DOMContentLoaded', function() {
        const closeButtons = document.querySelectorAll('[data-dismiss-target="#alert"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.closest('[role="alert"]');
                alert.classList.add('opacity-0');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            });
        });
        
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });
</script>
