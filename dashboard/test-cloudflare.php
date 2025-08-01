<?php
// Cloudflare API Test
$account_id = 'prdw3ANMyocSBJD-Do1EeQ';
$api_token = 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv';

echo "<h2>Cloudflare API Test</h2>";

// Test 1: List images endpoint
$url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/images/v1";

$headers = [
    'Authorization: Bearer ' . $api_token,
    'Content-Type: application/json'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "<h3>List Images Test</h3>";
echo "<p><strong>URL:</strong> $url</p>";
echo "<p><strong>HTTP Code:</strong> $http_code</p>";
echo "<p><strong>cURL Error:</strong> " . ($curl_error ?: 'None') . "</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

// Test 2: Account verification
$account_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}";

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $account_url);
curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);

$account_response = curl_exec($ch2);
$account_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "<h3>Account Verification Test</h3>";
echo "<p><strong>URL:</strong> $account_url</p>";
echo "<p><strong>HTTP Code:</strong> $account_http_code</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($account_response) . "</pre>";
?>
