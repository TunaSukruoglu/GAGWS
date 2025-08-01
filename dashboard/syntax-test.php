<?php
// Minimal Syntax Test
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "Syntax Test Başladı...<br>";

// 1. Basic PHP Test
echo "1. PHP Çalışıyor: " . phpversion() . "<br>";

// 2. Include Test
try {
    echo "2. DB include test...<br>";
    include '../db.php';
    echo "   ✅ DB OK<br>";
} catch (Exception $e) {
    echo "   ❌ DB Error: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Cloudflare Config Test
try {
    echo "3. Cloudflare config test...<br>";
    include '../includes/cloudflare-images-config.php';
    echo "   ✅ Cloudflare Config OK<br>";
} catch (Exception $e) {
    echo "   ❌ Cloudflare Config Error: " . $e->getMessage() . "<br>";
}

// 4. Multi-Domain Test
try {
    echo "4. Multi-domain class test...<br>";
    include '../includes/cloudflare-images-multi-domain.php';
    echo "   ✅ Multi-Domain Class OK<br>";
} catch (Exception $e) {
    echo "   ❌ Multi-Domain Error: " . $e->getMessage() . "<br>";
}

echo "<br><strong>Test tamamlandı!</strong><br>";
echo "<a href='add-property.php'>add-property.php'ye git</a>";
?>
