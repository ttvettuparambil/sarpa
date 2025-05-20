<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARPA - Site Maintenance</title>
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
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200">
    <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <i class="ri-tools-fill text-6xl text-yellow-500 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Site Under Maintenance</h1>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    We're currently performing scheduled maintenance on our site. 
                    We'll be back online shortly. Thank you for your patience.
                </p>
            </div>
            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <p class="text-gray-700 dark:text-gray-300">
                    Expected completion time: Soon
                </p>
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-2 bg-white dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400">
                                SARPA Team
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
