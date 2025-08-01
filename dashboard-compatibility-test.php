<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Dashboard Compatibility Test</h2>";

// PHP Version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Memory limit
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";

// Extensions
$required_extensions = ['mysqli', 'json', 'session', 'curl'];
echo "<h3>Required Extensions:</h3>";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>$status $ext</p>";
}

// Include test
echo "<h3>Include Tests:</h3>";

try {
    require_once 'includes/db-config.php';
    echo "<p>✅ db-config.php</p>";
} catch (Exception $e) {
    echo "<p>❌ db-config.php: " . $e->getMessage() . "</p>";
}

try {
    require_once 'includes/common-functions.php';
    echo "<p>✅ common-functions.php</p>";
} catch (Exception $e) {
    echo "<p>❌ common-functions.php: " . $e->getMessage() . "</p>";
}

// Session test
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
echo "<p>✅ Session başlatıldı</p>";

// Database test
try {
    $connection = new mysqli($servername, $username, $password, $dbname);
    if ($connection->connect_error) {
        echo "<p>❌ Database: " . $connection->connect_error . "</p>";
    } else {
        echo "<p>✅ Database bağlantısı</p>";
        $connection->close();
    }
} catch (Exception $e) {
    echo "<p>❌ Database: " . $e->getMessage() . "</p>";
}

echo "<h3>File Permissions:</h3>";
$dirs_to_check = [
    __DIR__ . '/dashboard/',
    __DIR__ . '/uploads/properties/',
    __DIR__ . '/images/properties/'
];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '✅ Writable' : '❌ Not writable';
        echo "<p>$writable $dir</p>";
    } else {
        echo "<p>❌ Not found: $dir</p>";
    }
}

echo "<p><a href='dashboard/add-property.php'>Dashboard'ı tekrar dene</a></p>";
?>
