<?php
session_start();
require 'dbConnection.php';
require_once 'config.php';

if (!isset($_SESSION['complaint_id'])) {
    echo "No complaint data found.";
    exit;
}

$complaint_id = $_SESSION['complaint_id'];

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
  </style>
  <!-- No external API script needed as we're using fetch API -->
</head>
<body>

<div class="summary-container">
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
</div>

<script>
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
