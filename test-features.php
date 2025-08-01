<?php
require_once 'config/cloudflare.php';

echo "<h2>Cloudflare Test</h2>";
echo "<p>Current Domain: " . getCurrentDomain() . "</p>";

$test = testCloudflareConnection();
echo "<p>API Test: " . ($test['success'] ? '✅' : '❌') . " " . $test['message'] . "</p>";

$config = getDomainWatermarkConfig();
echo "<h3>Domain Config:</h3>";
echo "<pre>" . json_encode($config, JSON_PRETTY_PRINT) . "</pre>";
?>