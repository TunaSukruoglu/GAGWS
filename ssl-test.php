<?php
// SSL Test - Sunucu SSL durumunu kontrol et
header('Content-Type: text/plain');

echo "=== SSL Test ===\n";
echo "Server: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Protocol: " . (isset($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP') . "\n";
echo "Port: " . $_SERVER['SERVER_PORT'] . "\n";

if (isset($_SERVER['HTTPS'])) {
    echo "SSL Active: Yes\n";
    echo "SSL Protocol: " . ($_SERVER['SSL_PROTOCOL'] ?? 'Unknown') . "\n";
    echo "SSL Cipher: " . ($_SERVER['SSL_CIPHER'] ?? 'Unknown') . "\n";
} else {
    echo "SSL Active: No\n";
}

echo "\n=== PHP OpenSSL Info ===\n";
if (extension_loaded('openssl')) {
    echo "OpenSSL Extension: Loaded\n";
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
} else {
    echo "OpenSSL Extension: NOT LOADED\n";
}

echo "\n=== Server Info ===\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n=== Request Headers ===\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
?>
