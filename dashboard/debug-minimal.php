<?php
// Minimal add-property test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Add Property Debug</title></head><body>";
echo "<h1>Add Property Debug Test</h1>";

// 1. Database test
try {
    include '../db.php';
    echo "<p style='color:green'>✅ Database OK</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Database Error: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Session test
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p style='color:green'>✅ Session OK</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Session Error: " . $e->getMessage() . "</p>";
}

// 3. Cloudflare test
try {
    require_once '../includes/cloudflare-images-config.php';
    echo "<p style='color:green'>✅ Cloudflare Config OK</p>";
} catch (Exception $e) {
    echo "<p style='color:orange'>⚠️ Cloudflare Config Error: " . $e->getMessage() . "</p>";
}

try {
    require_once '../includes/cloudflare-images-multi-domain.php';
    echo "<p style='color:green'>✅ Cloudflare Multi-Domain OK</p>";
} catch (Exception $e) {
    echo "<p style='color:orange'>⚠️ Cloudflare Multi-Domain Error: " . $e->getMessage() . "</p>";
}

// 4. CSRF test
try {
    require_once 'includes/csrf-manager.php';
    echo "<p style='color:green'>✅ CSRF Manager OK</p>";
} catch (Exception $e) {
    echo "<p style='color:orange'>⚠️ CSRF Manager Error: " . $e->getMessage() . "</p>";
}

// 5. Variable test
$images_string = '[]';
$main_image = '';
echo "<p style='color:green'>✅ Variables OK</p>";

echo "<p><strong>Tüm testler tamamlandı!</strong></p>";
echo "<p><a href='add-property.php'>Asıl add-property.php'ye git</a></p>";
echo "</body></html>";
?>
