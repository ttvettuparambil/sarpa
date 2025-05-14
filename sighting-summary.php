<?php
session_start();
require 'dbConnection.php';
require_once 'config.php';

// Initialize complaint_id variable
$complaint_id = null;

// Check if user is logged in (except for super_admin who can view any complaint)
if (!isset($_SESSION['user_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin')) {
    header("Location: login.php");
    exit;
}

// First check if complaint_id exists in URL query parameter
if (isset($_GET['complaint_id']) && !empty($_GET['complaint_id'])) {
    // Sanitize the input to prevent XSS attacks
    $complaint_id = htmlspecialchars($_GET['complaint_id'], ENT_QUOTES, 'UTF-8');
    
    // Validate complaint_id format (assuming it's alphanumeric)
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $complaint_id)) {
        echo "Invalid complaint ID format.";
        exit;
    }
    
    // Ensure the complaint_id isn't unreasonably long
    if (strlen($complaint_id) > 50) {
        echo "Invalid complaint ID length.";
        exit;
    }
} 
// If not in URL, check if it exists in session (for logged-in users)
elseif (isset($_SESSION['complaint_id'])) {
    $complaint_id = $_SESSION['complaint_id'];
}

// If complaint_id is still not found, show error
if ($complaint_id === null) {
    echo "No complaint data found. Please provide a valid complaint ID.";
    exit;
}

// Fetch full complaint details from DB
$stmt = $conn->prepare("SELECT * FROM snake_sightings WHERE complaint_id = ?");
$stmt->bind_param("s", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Complaint not found.";
    exit;
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Snake Sighting Summary</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            // Using Tailwind's default blue palette
          }
        }
      }
    }
    
    // Check for dark mode preference in localStorage
    if (localStorage.getItem('darkMode') === 'true') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <!-- Add jsPDF from CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <style>
    /* Skeleton Loading Animation */
    @keyframes shimmer {
      0% {
        background-position: -1000px 0;
      }
      100% {
        background-position: 1000px 0;
      }
    }

    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 1000px 100%;
      animation: shimmer 2s infinite linear;
    }

    .dark .skeleton {
      background: linear-gradient(90deg, #2d3748 25%, #1a202c 50%, #2d3748 75%);
      background-size: 1000px 100%;
      animation: shimmer 2s infinite linear;
    }

    .skeleton-text {
      height: 1em;
      margin-bottom: 0.5em;
      border-radius: 4px;
    }

    .skeleton-title {
      height: 2em;
      margin-bottom: 1em;
      border-radius: 4px;
    }

    .skeleton-image {
      height: 200px;
      border-radius: 8px;
      margin-bottom: 1em;
    }

    .skeleton-button {
      height: 2.5em;
      width: 150px;
      border-radius: 6px;
      margin-bottom: 1em;
    }

    #skeleton-loader {
      display: none;
    }

    .loading #skeleton-loader {
      display: block;
    }

    .loading #main-content {
      display: none;
    }
  </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen flex flex-col transition-colors duration-200 loading">
  <?php include 'components/header.php'; ?>
  
  <main class="flex-grow py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Skeleton Loader -->
      <div id="skeleton-loader" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 md:p-8 relative mb-8">
        <!-- Skeleton Buttons -->
        <div class="flex flex-wrap gap-3 mb-6">
          <div class="skeleton skeleton-button"></div>
          <div class="skeleton skeleton-button"></div>
        </div>

        <!-- Skeleton Title -->
        <div class="skeleton skeleton-title w-3/4 mb-4"></div>
        
        <!-- Skeleton Complaint ID -->
        <div class="skeleton skeleton-text w-1/2 mb-4"></div>
        <hr class="my-4 border-gray-200 dark:border-gray-700">

        <!-- Skeleton Details -->
        <div class="skeleton skeleton-title w-1/3 mb-4"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <div class="skeleton skeleton-text w-3/4 mb-2"></div>
            <div class="skeleton skeleton-text w-2/3 mb-2"></div>
            <div class="skeleton skeleton-text w-1/2 mb-2"></div>
            <div class="skeleton skeleton-text w-3/4 mb-2"></div>
          </div>
          <div>
            <div class="skeleton skeleton-text w-2/3 mb-2"></div>
            <div class="skeleton skeleton-text w-3/4 mb-2"></div>
            <div class="skeleton skeleton-text w-1/2 mb-2"></div>
          </div>
        </div>

        <!-- Skeleton Description -->
        <div class="mb-6">
          <div class="skeleton skeleton-text w-1/4 mb-2"></div>
          <div class="skeleton skeleton-text w-full mb-2"></div>
          <div class="skeleton skeleton-text w-full mb-2"></div>
          <div class="skeleton skeleton-text w-3/4 mb-2"></div>
        </div>

        <!-- Skeleton Image -->
        <div class="mb-6">
          <div class="skeleton skeleton-text w-1/4 mb-2"></div>
          <div class="skeleton skeleton-image"></div>
        </div>

        <!-- Skeleton AI Analysis -->
        <div class="mt-8 bg-white dark:bg-gray-800 border-2 border-blue-100 dark:border-blue-900 rounded-lg p-6">
          <div class="skeleton skeleton-title w-1/3 mb-4"></div>
          <div class="skeleton skeleton-text w-full mb-2"></div>
          <div class="skeleton skeleton-text w-3/4 mb-2"></div>
          <div class="skeleton skeleton-text w-full mb-2"></div>
        </div>
      </div>

      <!-- Main Content -->
      <div id="main-content">
        <!-- Summary Container -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 md:p-8 relative mb-8">
          <!-- Download & Export Buttons -->
          <div class="flex flex-wrap gap-3 mb-6">
            <button onclick="downloadPDF()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              Download as PDF
            </button>

            <form method="post" action="export-csv.php">
              <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Export My Sightings (CSV)
              </button>
            </form>
          </div>

          <?php if (!isset($_GET['complaint_id'])): ?>
          <button id="shareButton" class="absolute top-6 right-6 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2">
              <circle cx="18" cy="5" r="3"></circle>
              <circle cx="6" cy="12" r="3"></circle>
              <circle cx="18" cy="19" r="3"></circle>
              <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
              <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg>
            Copy Sharable Link
            <span id="shareTooltip" class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 transition-opacity duration-300 pointer-events-none whitespace-nowrap">Link copied!</span>
          </button>
          <?php endif; ?>

          <h2 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-4">Complaint Submitted Successfully</h2>
          <p class="mb-2"><strong>Complaint ID:</strong> <?= htmlspecialchars($data['complaint_id']) ?></p>
          <hr class="my-4 border-gray-200 dark:border-gray-700">

          <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-400 mb-4">Submitted Details:</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <p class="mb-2"><strong>District:</strong> <?= htmlspecialchars($data['district']) ?></p>
              <p class="mb-2"><strong>City:</strong> <?= htmlspecialchars($data['city']) ?></p>
              <p class="mb-2"><strong>Postcode:</strong> <?= htmlspecialchars($data['postcode']) ?></p>
              <p class="mb-2"><strong>Address Line 1:</strong> <?= htmlspecialchars($data['address_line1']) ?></p>
            </div>
            <div>
              <p class="mb-2"><strong>Address Line 2:</strong> <?= htmlspecialchars($data['address_line2']) ?></p>
              <p class="mb-2"><strong>Landmark:</strong> <?= htmlspecialchars($data['landmark']) ?></p>
              <p class="mb-2"><strong>Sighting Time:</strong> <?= $data['datetime'] ?></p>
            </div>
          </div>

          <?php if (!empty($data['description'])): ?>
            <div class="mb-6">
              <p class="mb-2"><strong>Description:</strong></p>
              <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <?= nl2br(htmlspecialchars($data['description'])) ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
            <div class="mb-6">
              <p class="mb-2"><strong>Image:</strong></p>
              <img src="<?= htmlspecialchars($data['image_path']) ?>" alt="Snake Image" class="w-full max-w-2xl rounded-lg shadow-md">
            </div>
          <?php endif; ?>

          <hr class="my-4 border-gray-200 dark:border-gray-700">
          <p class="mb-2"><strong>Submitted by:</strong> 
            <?= htmlspecialchars($data['user_name'] ?? 'N/A') ?> 
            (<?= htmlspecialchars($data['user_email'] ?? 'N/A') ?> / <?= htmlspecialchars($data['user_phone'] ?? 'N/A') ?>)
          </p>
          
          <?php if (isset($_SESSION['user_id']) && !empty($data['image_path']) && file_exists($data['image_path'])): ?>
          <!-- AI Analysis Section -->
          <div class="mt-8 bg-white dark:bg-gray-800 border-2 border-blue-100 dark:border-blue-900 rounded-lg p-6" id="aiAnalysisContainer">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-400 mb-4 flex items-center">
              AI Snake Analysis
              <div class="relative ml-2 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400 cursor-help" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="absolute left-1/2 transform -translate-x-1/2 bottom-full mb-2 w-64 p-2 bg-gray-800 text-white text-xs rounded shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity duration-300 z-10">
                  This analysis is AI-generated using Gemini 2.5 Flash. Results may be wildly inaccurate. Always consult with a snake expert for proper identification.
                  <div class="absolute left-1/2 transform -translate-x-1/2 top-full w-2 h-2 bg-gray-800 rotate-45"></div>
                </div>
              </div>
            </h3>
            <div id="loadingAnalysis" class="flex items-center">
              <div class="inline-block w-5 h-5 mr-3 border-2 border-gray-200 dark:border-gray-700 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
              <p>Analyzing snake image...</p>
            </div>
            <div id="analysisResults" class="hidden space-y-4">
              <div>
                <p><strong>Snake Species:</strong> <span id="snakeSpecies">-</span></p>
              </div>
              <div>
                <p><strong>Venomous:</strong> <span id="snakeVenomous">-</span></p>
              </div>
              <div id="snakeSummary">
                <p><strong>Summary:</strong></p>
                <p id="summaryText" class="mt-2 p-4 bg-gray-50 dark:bg-gray-700 border-l-4 border-blue-500 rounded-r-lg"></p>
              </div>
            </div>
            <div id="analysisError" class="hidden p-4 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg">
              <p>AI analysis is currently unavailable. Please consult with a snake expert for identification.</p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Snake Handlers Section -->
          <?php if (!isset($_GET['complaint_id'])): ?>
          <?php
          // Load snake handlers data
          $handlersData = [];
          $jsonFile = 'snakeHandlers.json';
          
          if (file_exists($jsonFile)) {
            $handlersJson = file_get_contents($jsonFile);
            $handlersData = json_decode($handlersJson, true);
          }
          
          // Filter handlers by district
          $districtHandlers = [];
          $district = $data['district'] ?? '';
          
          if (!empty($handlersData) && !empty($district)) {
            foreach ($handlersData as $handler) {
              if (isset($handler['district']) && $handler['district'] === $district) {
                $districtHandlers[] = $handler;
              }
            }
          }
          ?>
          
          <div class="mt-8 bg-white dark:bg-gray-800 border-2 border-blue-100 dark:border-blue-900 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-400 mb-4">Snake Handlers in <?= htmlspecialchars($district) ?></h3>
            
            <?php if (empty($districtHandlers)): ?>
              <p class="p-4 bg-gray-50 dark:bg-gray-700 border-l-4 border-gray-500 rounded-r-lg text-gray-700 dark:text-gray-300">No certified snake handlers found in your district. Please contact the Forest Department for assistance.</p>
            <?php else: ?>
              <p class="mb-4">Below are certified snake handlers in your district who can help with snake rescue:</p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($districtHandlers as $handler): ?>
                  <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 p-4">
                    <h4 class="text-lg font-semibold text-blue-600 dark:text-blue-400 pb-2 mb-2 border-b border-gray-200 dark:border-gray-600"><?= htmlspecialchars($handler['name'] ?? 'Unknown') ?></h4>
                    <p class="mb-2 text-sm"><strong>Designation/Address:</strong><br>
                      <?= htmlspecialchars($handler['designation_address'] ?? 'Not available') ?>
                    </p>
                    <p class="mb-2 text-sm font-medium text-blue-600 dark:text-blue-400">
                      <strong>Phone:</strong> 
                      <?php if (!empty($handler['mobile_number'])): ?>
                        <a href="tel:<?= preg_replace('/[^0-9]/', '', $handler['mobile_number']) ?>" class="hover:underline">
                          <?= htmlspecialchars($handler['mobile_number']) ?>
                        </a>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $handler['mobile_number']) ?>?text=<?= urlencode("Hello, I have a snake sighting to report. Please check this link: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" 
                           class="inline-block ml-2 text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors" 
                           target="_blank" 
                           title="Share on WhatsApp">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                          </svg>
                        </a>
                      <?php else: ?>
                        Not available
                      <?php endif; ?>
                    </p>
                    <p class="mb-3 text-sm"><strong>Certification ID:</strong> <?= htmlspecialchars($handler['certification_id'] ?? 'Not available') ?></p>
                    <?php 
                      $type = $handler['type'] ?? '';
                      $typeClass = (strpos(strtolower($type), 'staff') !== false) ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200';
                    ?>
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $typeClass ?>"><?= htmlspecialchars($type) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
  
  <?php include 'components/footer.php'; ?>
  
  <script>
    // Function to handle page load
    window.addEventListener('load', function() {
        // Simulate loading time (remove this in production)
        setTimeout(function() {
            document.body.classList.remove('loading');
        }, 1000);
    });

    // Share button functionality
    const shareButton = document.getElementById('shareButton');
    
    // Only run this code if the button exists (not shown for shared links)
    if (shareButton) {
      const shareTooltip = document.getElementById('shareTooltip');
      
      shareButton.addEventListener('click', function() {
        // Get the complaint ID
        const complaintId = "<?= htmlspecialchars($data['complaint_id']) ?>";
        
        // Create the sharable URL
        const currentUrl = window.location.href.split('?')[0]; // Get base URL without query params
        const sharableUrl = `${currentUrl}?complaint_id=${complaintId}`;
        console.log(`shareable url is:::>`,sharableUrl);
        
        // Create temporary input element
        const tempInput = document.createElement('input');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        tempInput.value = sharableUrl;
        document.body.appendChild(tempInput);
        
        // Select and copy
        tempInput.select();
        document.execCommand('copy');
        
        // Remove the temporary input
        document.body.removeChild(tempInput);
        
        // Show the tooltip
        shareTooltip.classList.add('opacity-100');
        
        // Hide the tooltip after 2 seconds
        setTimeout(function() {
          shareTooltip.classList.remove('opacity-100');
        }, 2000);
      });
    }
    
    // Elements
    const loadingElement = document.getElementById('loadingAnalysis');
    const resultsElement = document.getElementById('analysisResults');
    const errorElement = document.getElementById('analysisError');
    const speciesElement = document.getElementById('snakeSpecies');
    const venomousElement = document.getElementById('snakeVenomous');
    
    // Function to analyze snake image
    async function analyzeSnakeImage() {
      <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
      const imagePath = "<?= htmlspecialchars($data['image_path']) ?>";
      
      try {
        // Load Gemini API
        await loadGeminiAPI();
        
        // Get image data
        const imageData = await getImageAsBase64(imagePath);
        
        // Call Gemini API
        const result = await callGeminiAPI(imageData);
        
        // Display results
        displayResults(result);
      } catch (error) {
        console.error("Error analyzing snake image:", error);
        showError();
      }
      <?php else: ?>
      showError();
      <?php endif; ?>
    }
    
    // Function to load Gemini API - no need for explicit loading as we're using REST API
    function loadGeminiAPI() {
      return Promise.resolve(); // API is accessed via REST, no client-side library needed
    }
    
    // Function to get image as base64
    function getImageAsBase64(imagePath) {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = "Anonymous";
        img.onload = function() {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;
          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);
          try {
            const dataURL = canvas.toDataURL("image/jpeg");
            resolve(dataURL.split(",")[1]);
          } catch (e) {
            reject(e);
          }
        };
        img.onerror = function() {
          reject(new Error("Failed to load image"));
        };
        img.src = imagePath;
      });
    }
    
    // Function to call Gemini API
    async function callGeminiAPI(imageData) {
      try {
        // Use the server-side proxy to make the API call
        console.log("Sending request to Gemini API via proxy...");
        
        // Create form data for the proxy request
        const formData = new FormData();
        formData.append('image_data', imageData);
        
        // Send request to proxy
        const proxyResponse = await fetch('gemini-proxy.php', {
          method: 'POST',
          body: formData
        });
        
        if (!proxyResponse.ok) {
          throw new Error(`Proxy request failed with status ${proxyResponse.status}`);
        }
        
        console.log("Proxy request successful");
        return await handleApiResponse(proxyResponse);
        
      } catch (error) {
        console.error("Error calling Gemini API:", error);
        
        // Return fallback data if API call fails
        return {
          species: "Analysis failed - please consult an expert",
          venomous: true, // Assume venomous for safety
          summary: "Unable to analyze the snake image. This could be a potentially dangerous snake. Please consult with a local snake expert for proper identification and handling advice."
        };
      }
    }
    
    // Helper function to handle API response
    async function handleApiResponse(response) {
      if (!response.ok) {
        throw new Error(`API request failed with status ${response.status}`);
      }
      
      const responseData = await response.json();
      
      // Extract the text response from Gemini
      const textResponse = responseData.candidates[0].content.parts[0].text;
      
      // Parse the JSON response
      // The API might return the JSON embedded in markdown code blocks, so we need to extract it
      let jsonStr = textResponse;
      
      // If the response is wrapped in markdown code blocks, extract just the JSON
      const jsonMatch = textResponse.match(/```json\n([\s\S]*?)\n```/) || 
                        textResponse.match(/```\n([\s\S]*?)\n```/) ||
                        textResponse.match(/{[\s\S]*?}/);
                        
      if (jsonMatch) {
        jsonStr = jsonMatch[0];
      }
      
      // Clean up the string to ensure it's valid JSON
      jsonStr = jsonStr.replace(/```json\n|```\n|```/g, '').trim();
      
      // Parse the JSON
      let parsedData;
      try {
        parsedData = JSON.parse(jsonStr);
      } catch (e) {
        console.error("Failed to parse JSON response:", e);
        console.log("Raw response:", textResponse);
        
        // If JSON parsing fails, try to extract the information manually
        const speciesMatch = textResponse.match(/species["\s:]+([^"]+)/i);
        const venomousMatch = textResponse.match(/venomous["\s:]+([^"]+)/i);
        const summaryMatch = textResponse.match(/summary["\s:]+([^"]+)/i);
        
        parsedData = {
          species: speciesMatch ? speciesMatch[1].trim() : "Unknown species",
          venomous: venomousMatch ? venomousMatch[1].toLowerCase().includes("true") : false,
          summary: summaryMatch ? summaryMatch[1].trim() : "This snake requires expert identification. Please consult with a local herpetologist for more information."
        };
      }
      
      return {
        species: parsedData.species || "Unknown species",
        venomous: !!parsedData.venomous,
        summary: parsedData.summary || "No additional information available about this snake species."
      };
    }
    
    // Function to display results
    function displayResults(result) {
      // Update species
      speciesElement.textContent = result.species;
      
      // Update venomous status
      if (result.venomous) {
        venomousElement.innerHTML = "Yes <span class='inline-block px-2 py-1 ml-2 text-xs font-semibold rounded bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'>VENOMOUS</span>";
      } else {
        venomousElement.innerHTML = "No <span class='inline-block px-2 py-1 ml-2 text-xs font-semibold rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'>NON-VENOMOUS</span>";
      }
      
      // Update summary
      const summaryTextElement = document.getElementById('summaryText');
      summaryTextElement.textContent = result.summary;
      
      // Hide loading, show results
      loadingElement.classList.add('hidden');
      resultsElement.classList.remove('hidden');
    }
    
    // Function to show error
    function showError() {
      loadingElement.classList.add('hidden');
      resultsElement.classList.add('hidden');
      errorElement.classList.remove('hidden');
    }
    
    // Start analysis when page loads
    window.onload = function() {
      analyzeSnakeImage();
      
      // Toggle tooltip on mobile
      const infoIcon = document.querySelector('#aiAnalysisContainer .cursor-help');
      if (infoIcon) {
        infoIcon.addEventListener('click', function(e) {
          e.preventDefault();
          const tooltip = this.nextElementSibling;
          tooltip.classList.toggle('opacity-0');
          tooltip.classList.toggle('invisible');
          
          // Hide tooltip after 3 seconds
          if (!tooltip.classList.contains('opacity-0')) {
            setTimeout(() => {
              tooltip.classList.add('opacity-0', 'invisible');
            }, 3000);
          }
        });
      }
    };

    // PDF download function
    async function downloadPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({
        orientation: "portrait",
        unit: "mm",
        format: "a4"
      });

      let y = 15;

      // Title
      doc.setFont("helvetica", "bold");
      doc.setFontSize(16);
      doc.text("Snake Sighting Summary", 105, y, { align: "center" });
      y += 15;

      // Extracted PHP data
      const data = {
        complaint_id: "<?= htmlspecialchars($data['complaint_id']) ?>",
        district: "<?= htmlspecialchars($data['district']) ?>",
        city: "<?= htmlspecialchars($data['city']) ?>",
        postcode: "<?= htmlspecialchars($data['postcode']) ?>",
        address1: "<?= htmlspecialchars($data['address_line1']) ?>",
        address2: "<?= htmlspecialchars($data['address_line2']) ?>",
        landmark: "<?= htmlspecialchars($data['landmark']) ?>",
        datetime: "<?= htmlspecialchars($data['datetime']) ?>",
        description: `<?= nl2br(htmlspecialchars($data['description'] ?? '')) ?>`,
        name: "<?= htmlspecialchars($data['user_name'] ?? 'N/A') ?>",
        email: "<?= htmlspecialchars($data['user_email'] ?? 'N/A') ?>",
        phone: "<?= htmlspecialchars($data['user_phone'] ?? 'N/A') ?>",
        image_path: "<?= htmlspecialchars($data['image_path'] ?? '') ?>"
      };

      // Fields to render
      const fields = [
        ["Complaint ID:", data.complaint_id, true],
        ["District:", data.district],
        ["City:", data.city],
        ["Postcode:", data.postcode],
        ["Address 1:", data.address1],
        ["Address 2:", data.address2],
        ["Landmark:", data.landmark],
        ["Sighting Time:", data.datetime],
        ["Description:", data.description],
        ["Submitted by:", `${data.name} (${data.email} / ${data.phone})`],
      ];

      // Loop and render
      doc.setFontSize(12);
      for (const [label, value, bold] of fields) {
        if (bold) {
          doc.setFont("helvetica", "bold");
        } else {
          doc.setFont("helvetica", "normal");
        }
        doc.text(`${label}`, 10, y);
        doc.setFont("helvetica", "normal");
        doc.text(`${value}`, 50, y);
        y += 8;
      }

      // Add image if available
      if (data.image_path) {
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.src = data.image_path;

        img.onload = function () {
          const canvas = document.createElement("canvas");
          canvas.width = img.width;
          canvas.height = img.height;

          const ctx = canvas.getContext("2d");
          ctx.drawImage(img, 0, 0);
          const imgData = canvas.toDataURL("image/jpeg");

          doc.addPage();
          doc.setFontSize(14);
          doc.text("Uploaded Image", 10, 20);
          doc.addImage(imgData, 'JPEG', 10, 30, 180, 120); // Resize as needed
          doc.save(`SARPA_Complaint_${data.complaint_id}.pdf`);
        };

        img.onerror = function () {
          alert("Unable to load image for PDF.");
        };
      } else {
        doc.save(`SARPA_Complaint_${data.complaint_id}.pdf`);
      }
    }
  </script>
</body>
</html>
