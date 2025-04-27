<?php
session_start();
require 'dbConnection.php';
require_once 'config.php';

// Initialize complaint_id variable
$complaint_id = null;

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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Snake Sighting Summary</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .summary-container {
      max-width: 700px;
      margin: 40px auto;
      padding: 20px;
      border: 2px solid #eee;
      border-radius: 10px;
      background-color: #fafafa;
      position: relative;
    }
    .summary-container h2 {
      color: #2a7d46;
    }
    .summary-container p {
      margin: 10px 0;
    }
    .summary-container img {
      max-width: 100%;
      margin-top: 10px;
    }
    .ai-analysis-container {
      margin-top: 30px;
      padding: 20px;
      border: 2px solid #d1e7dd;
      border-radius: 10px;
      background-color: #f8f9fa;
    }
    .ai-analysis-container h3 {
      color: #2a7d46;
      margin-bottom: 15px;
    }
    .analysis-section {
      margin-bottom: 15px;
    }
    .loading-spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-top-color: #2a7d46;
      animation: spin 1s ease-in-out infinite;
      margin-right: 10px;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .venomous-tag {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: bold;
      margin-left: 5px;
    }
    .venomous-yes {
      background-color: #f8d7da;
      color: #721c24;
    }
    .venomous-no {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    .precautions-list {
      padding-left: 20px;
    }
    .precautions-list li {
      margin-bottom: 5px;
    }
    .summary-text {
      line-height: 1.5;
      margin-top: 5px;
      padding: 10px;
      background-color: #f8f9fa;
      border-left: 3px solid #2a7d46;
      border-radius: 3px;
    }
    .api-error {
      padding: 10px;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      border-radius: 5px;
      color: #721c24;
    }
    
    /* Snake Handlers Cards Styling */
    .handlers-container {
      margin-top: 30px;
      padding: 20px;
      border: 2px solid #d1e7dd;
      border-radius: 10px;
      background-color: #f8f9fa;
    }
    .handlers-container h3 {
      color: #2a7d46;
      margin-bottom: 15px;
    }
    .handlers-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 15px;
    }
    .handler-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      background-color: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }
    .handler-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .handler-card h4 {
      color: #2a7d46;
      margin-top: 0;
      margin-bottom: 10px;
      border-bottom: 1px solid #eee;
      padding-bottom: 8px;
    }
    .handler-card p {
      margin: 5px 0;
      font-size: 14px;
    }
    .handler-card .phone {
      font-weight: bold;
      color: #0056b3;
    }
    .handler-card .type {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      margin-top: 10px;
    }
    .handler-card .staff {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    .handler-card .volunteer {
      background-color: #cfe2ff;
      color: #084298;
    }
    .no-handlers {
      padding: 15px;
      background-color: #f8f9fa;
      border-left: 3px solid #6c757d;
      border-radius: 3px;
      color: #6c757d;
    }
    
    /* Share Button Styling */
    .share-button {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: #2a7d46;
      color: white;
      border: none;
      border-radius: 4px;
      padding: 8px 12px;
      font-size: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      transition: background-color 0.3s;
    }
    
    .share-button:hover {
      background-color: #1e5e34;
    }
    
    .share-button svg {
      margin-right: 6px;
      width: 16px;
      height: 16px;
    }
    
    .share-tooltip {
      position: absolute;
      top: -30px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #333;
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      opacity: 0;
      transition: opacity 0.3s;
      pointer-events: none;
      white-space: nowrap;
    }
    
    .share-button:focus .share-tooltip,
    .share-tooltip.show {
      opacity: 1;
    }
  </style>
  <!-- No external API script needed as we're using fetch API -->
</head>
<body>

<div class="summary-container">
  <?php if (!isset($_GET['complaint_id'])): ?>
  <button id="shareButton" class="share-button">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="18" cy="5" r="3"></circle>
      <circle cx="6" cy="12" r="3"></circle>
      <circle cx="18" cy="19" r="3"></circle>
      <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
      <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
    </svg>
    Copy Sharable Link
    <span class="share-tooltip" id="shareTooltip">Link copied!</span>
  </button>
  <?php endif; ?>
  <h2>Complaint Submitted Successfully</h2>
  <p><strong>Complaint ID:</strong> <?= htmlspecialchars($data['complaint_id']) ?></p>
  <hr>

  <h3>Submitted Details:</h3>
  <p><strong>District:</strong> <?= htmlspecialchars($data['district']) ?></p>
  <p><strong>City:</strong> <?= htmlspecialchars($data['city']) ?></p>
  <p><strong>Postcode:</strong> <?= htmlspecialchars($data['postcode']) ?></p>
  <p><strong>Address Line 1:</strong> <?= htmlspecialchars($data['address_line1']) ?></p>
  <p><strong>Address Line 2:</strong> <?= htmlspecialchars($data['address_line2']) ?></p>
  <p><strong>Landmark:</strong> <?= htmlspecialchars($data['landmark']) ?></p>
  <p><strong>Sighting Time:</strong> <?= $data['datetime'] ?></p>

  <?php if (!empty($data['description'])): ?>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($data['description'])) ?></p>
  <?php endif; ?>

  <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
    <p><strong>Image:</strong><br>
    <img src="<?= htmlspecialchars($data['image_path']) ?>" alt="Snake Image" style="max-width: 100%; height: auto;"></p>
  <?php endif; ?>

  <hr>
  <p><strong>Submitted by:</strong> 
    <?= htmlspecialchars($data['user_name'] ?? 'N/A') ?> 
    (<?= htmlspecialchars($data['user_email'] ?? 'N/A') ?> / <?= htmlspecialchars($data['user_phone'] ?? 'N/A') ?>)
  </p>
  
  <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
  <!-- AI Analysis Section -->
  <div class="ai-analysis-container" id="aiAnalysisContainer">
    <h3>AI Snake Analysis</h3>
    <div id="loadingAnalysis">
      <p><span class="loading-spinner"></span> Analyzing snake image...</p>
    </div>
    <div id="analysisResults" style="display:none;">
      <div class="analysis-section">
        <p><strong>Snake Species:</strong> <span id="snakeSpecies">-</span></p>
      </div>
      <div class="analysis-section">
        <p><strong>Venomous:</strong> <span id="snakeVenomous">-</span></p>
      </div>
      <div class="analysis-section" id="snakeSummary">
        <p><strong>Summary:</strong></p>
        <p id="summaryText" class="summary-text"></p>
      </div>
    </div>
    <div id="analysisError" style="display:none;" class="api-error">
      <p>AI analysis is currently unavailable. Please consult with a snake expert for identification.</p>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Snake Handlers Section -->
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
  
  <div class="handlers-container">
    <h3>Snake Handlers in <?= htmlspecialchars($district) ?></h3>
    
    <?php if (empty($districtHandlers)): ?>
      <p class="no-handlers">No certified snake handlers found in your district. Please contact the Forest Department for assistance.</p>
    <?php else: ?>
      <p>Below are certified snake handlers in your district who can help with snake rescue:</p>
      
      <div class="handlers-grid">
        <?php foreach ($districtHandlers as $handler): ?>
          <div class="handler-card">
            <h4><?= htmlspecialchars($handler['name'] ?? 'Unknown') ?></h4>
            <p><strong>Designation/Address:</strong><br>
              <?= htmlspecialchars($handler['designation_address'] ?? 'Not available') ?>
            </p>
            <p class="phone">
              <strong>Phone:</strong> 
              <?php if (!empty($handler['mobile_number'])): ?>
                <a href="tel:<?= preg_replace('/[^0-9]/', '', $handler['mobile_number']) ?>">
                  <?= htmlspecialchars($handler['mobile_number']) ?>
                </a>
              <?php else: ?>
                Not available
              <?php endif; ?>
            </p>
            <p><strong>Certification ID:</strong> <?= htmlspecialchars($handler['certification_id'] ?? 'Not available') ?></p>
            <?php 
              $type = $handler['type'] ?? '';
              $typeClass = (strpos(strtolower($type), 'staff') !== false) ? 'staff' : 'volunteer';
            ?>
            <span class="type <?= $typeClass ?>"><?= htmlspecialchars($type) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
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
    shareTooltip.classList.add('show');
    
    // Hide the tooltip after 2 seconds
    setTimeout(function() {
      shareTooltip.classList.remove('show');
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
      venomousElement.innerHTML = "Yes <span class='venomous-tag venomous-yes'>VENOMOUS</span>";
    } else {
      venomousElement.innerHTML = "No <span class='venomous-tag venomous-no'>NON-VENOMOUS</span>";
    }
    
    // Update summary
    const summaryTextElement = document.getElementById('summaryText');
    summaryTextElement.textContent = result.summary;
    
    // Hide loading, show results
    loadingElement.style.display = "none";
    resultsElement.style.display = "block";
  }
  
  // Function to show error
  function showError() {
    loadingElement.style.display = "none";
    resultsElement.style.display = "none";
    errorElement.style.display = "block";
  }
  
  // Start analysis when page loads
  window.onload = function() {
    analyzeSnakeImage();
  };
</script>

</body>
</html>
