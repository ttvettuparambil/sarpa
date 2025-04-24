<?php
// Gemini API Proxy
// This script acts as a proxy between the client and the Gemini API to avoid CORS issues

// Include configuration file
require_once 'config.php';

// Allow cross-origin requests from the same domain
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Always use the API key from config, never from client
$apiKey = GEMINI_API_KEY;

// Get the image data from the request
$imageData = $_POST['image_data'] ?? '';

if (empty($imageData)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Image data is required']);
    exit;
}

// Prepare the request to the Gemini API
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

$requestData = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => "Analyze this snake image. Identify the species, determine if it's venomous, and provide a summary of the snake. Format your response as JSON with these fields: species (string), venomous (boolean), summary (string)."
                ],
                [
                    'inline_data' => [
                        'mime_type' => 'image/jpeg',
                        'data' => $imageData
                    ]
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.4,
        'topK' => 32,
        'topP' => 1,
        'maxOutputTokens' => 4096
    ]
];

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Execute the cURL request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if (curl_errno($ch)) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'cURL error: ' . curl_error($ch),
        'code' => curl_errno($ch)
    ]);
    curl_close($ch);
    exit;
}

// Close cURL session
curl_close($ch);

// If the API request was not successful, return an error
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'Gemini API request failed',
        'http_code' => $httpCode,
        'response' => json_decode($response, true)
    ]);
    exit;
}

// Forward the API response to the client
echo $response;
