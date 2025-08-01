<?php
// Cloudflare API Test Script
echo "<h2>Cloudflare Images API Test</h2>";

// Load config
require_once 'includes/cloudflare-images-config.php';

echo "<p><strong>Account ID:</strong> " . CLOUDFLARE_ACCOUNT_ID . "</p>";
echo "<p><strong>API Token Length:</strong> " . strlen(CLOUDFLARE_API_TOKEN) . " chars</p>";

// Test API connection
$url = "https://api.cloudflare.com/client/v4/accounts/" . CLOUDFLARE_ACCOUNT_ID . "/images/v1";

$headers = [
    'Authorization: Bearer ' . CLOUDFLARE_API_TOKEN,
    'Content-Type: application/json'
];

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => 10
    ]
]);

echo "<h3>Testing API Connection...</h3>";
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "<div style='color: red;'>❌ API Connection Failed</div>";
    echo "<p>Error: " . error_get_last()['message'] . "</p>";
} else {
    $data = json_decode($response, true);
    if (isset($data['success']) && $data['success']) {
        echo "<div style='color: green;'>✅ API Connection Successful</div>";
        echo "<p>Images found: " . count($data['result']['images'] ?? []) . "</p>";
    } else {
        echo "<div style='color: red;'>❌ API Error</div>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

// Test upload endpoint specifically
echo "<h3>Testing Upload Endpoint...</h3>";
$upload_url = "https://api.cloudflare.com/client/v4/accounts/" . CLOUDFLARE_ACCOUNT_ID . "/images/v1";

$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => json_encode(['test' => true]),
        'timeout' => 10
    ]
]);

$upload_response = @file_get_contents($upload_url, false, $context2);
if ($upload_response !== false) {
    echo "<p>Upload endpoint response:</p>";
    echo "<pre>" . htmlspecialchars($upload_response) . "</pre>";
} else {
    echo "<p style='color: red;'>Upload endpoint test failed</p>";
}
?>
