<?php
session_start();
require 'dbConnection.php';

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
      <div class="analysis-section" id="snakePrecautions">
        <p><strong>Precautions:</strong></p>
        <ul class="precautions-list" id="precautionsList"></ul>
      </div>
    </div>
    <div id="analysisError" style="display:none;" class="api-error">
      <p>AI analysis is currently unavailable. Please consult with a snake expert for identification.</p>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
  // Google Gemini API Key
  const API_KEY = "AIzaSyAExRQcOKbuQGfZxrLqZ_ctS_2hE4L7pYs";
  
  // Elements
  const loadingElement = document.getElementById('loadingAnalysis');
  const resultsElement = document.getElementById('analysisResults');
  const errorElement = document.getElementById('analysisError');
  const speciesElement = document.getElementById('snakeSpecies');
  const venomousElement = document.getElementById('snakeVenomous');
  const precautionsListElement = document.getElementById('precautionsList');
  
  // Function to analyze snake image
  async function analyzeSnakeImage() {
    <?php if (!empty($data['image_path']) && file_exists($data['image_path'])): ?>
    const imagePath = "<?= htmlspecialchars($data['image_path']) ?>";
    
    try {
      // Check if API key is available
      if (!API_KEY || API_KEY === "") {
        throw new Error("API key not configured");
      }
      
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
      // First try using the proxy to avoid CORS issues
      console.log("Sending request to Gemini API via proxy...");
      
      // Create form data for the proxy request
      const formData = new FormData();
      formData.append('api_key', API_KEY);
      formData.append('image_data', imageData);
      
      // Try the proxy first
      try {
        const proxyResponse = await fetch('gemini-proxy.php', {
          method: 'POST',
          body: formData
        });
        
        if (proxyResponse.ok) {
          console.log("Proxy request successful");
          return await handleApiResponse(proxyResponse);
        } else {
          console.log("Proxy request failed, falling back to direct API call");
          // If proxy fails, fall back to direct API call
        }
      } catch (proxyError) {
        console.error("Error using proxy:", proxyError);
        console.log("Falling back to direct API call");
      }
      
      // Direct API call as fallback
      const apiUrl = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${API_KEY}`;
      
      const requestData = {
        contents: [
          {
            parts: [
              {
                text: "Analyze this snake image. Identify the species, determine if it's venomous, and provide safety precautions. Format your response as JSON with these fields: species (string), venomous (boolean), precautions (array of strings)."
              },
              {
                inline_data: {
                  mime_type: "image/jpeg",
                  data: imageData
                }
              }
            ]
          }
        ],
        generationConfig: {
          temperature: 0.4,
          topK: 32,
          topP: 1,
          maxOutputTokens: 4096
        }
      };
      
      console.log("Sending direct request to Gemini API...");
      
      const response = await fetch(apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
      });
      
      return await handleApiResponse(response);
      
    } catch (error) {
      console.error("Error calling Gemini API:", error);
      
      // Return fallback data if API call fails
      return {
        species: "Analysis failed - please consult an expert",
        venomous: true, // Assume venomous for safety
        precautions: [
          "Keep a safe distance from the snake",
          "Contact local snake handlers listed in the system",
          "Do not attempt to handle the snake yourself",
          "If bitten, seek immediate medical attention"
        ]
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
      
      parsedData = {
        species: speciesMatch ? speciesMatch[1].trim() : "Unknown species",
        venomous: venomousMatch ? venomousMatch[1].toLowerCase().includes("true") : false,
        precautions: [
          "Keep a safe distance from the snake",
          "Contact local wildlife authorities for assistance",
          "Do not attempt to handle the snake yourself",
          "If bitten, seek immediate medical attention"
        ]
      };
    }
    
    return {
      species: parsedData.species || "Unknown species",
      venomous: !!parsedData.venomous,
      precautions: Array.isArray(parsedData.precautions) ? parsedData.precautions : [
        "Keep a safe distance from the snake",
        "Contact local wildlife authorities for assistance",
        "Do not attempt to handle the snake yourself"
      ]
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
    
    // Update precautions
    precautionsListElement.innerHTML = "";
    result.precautions.forEach(precaution => {
      const li = document.createElement("li");
      li.textContent = precaution;
      precautionsListElement.appendChild(li);
    });
    
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
