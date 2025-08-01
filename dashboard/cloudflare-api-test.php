<?php
// Cloudflare API Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test credentials
$accountId = 'prdw3ANMyocSBJD-Do1EeQ';
$token = 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv';

// Test 1: Account verification
$testUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountId}";

echo "<h2>Cloudflare API Test</h2>";
echo "<p>Account ID: {$accountId}</p>";
echo "<p>Token: " . substr($token, 0, 10) . "...</p>";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $testUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

echo "<h3>Account Test Result:</h3>";
echo "<p>HTTP Code: {$httpCode}</p>";
echo "<p>cURL Error: " . ($error ?: 'None') . "</p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 2: Images API endpoint
$imagesUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/images/v1";

$curl2 = curl_init();
curl_setopt_array($curl2, [
    CURLOPT_URL => $imagesUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]
]);

$response2 = curl_exec($curl2);
$httpCode2 = curl_getinfo($curl2, CURLINFO_HTTP_CODE);
$error2 = curl_error($curl2);
curl_close($curl2);

echo "<h3>Images API Test Result:</h3>";
echo "<p>URL: {$imagesUrl}</p>";
echo "<p>HTTP Code: {$httpCode2}</p>";
echo "<p>cURL Error: " . ($error2 ?: 'None') . "</p>";
echo "<pre>" . htmlspecialchars($response2) . "</pre>";

// Test 3: Upload endpoint doğru format
$uploadUrl = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/images/v1";

echo "<h3>Upload Endpoint:</h3>";
echo "<p>URL: {$uploadUrl}</p>";
echo "<p>Bu endpoint POST ile resim upload için kullanılmalı</p>";
?>
