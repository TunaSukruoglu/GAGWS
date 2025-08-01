<?php
// Upload path test - Debug için

echo "<h2>Resim Upload Path Test</h2>";

$dashboard_upload_dir = __DIR__ . '/dashboard/../uploads/properties/';
$images_dir = __DIR__ . '/images/properties/';

echo "<h3>Klasör Kontrolleri:</h3>";
echo "<p><strong>Dashboard Upload Dir:</strong> " . $dashboard_upload_dir . "</p>";
echo "<p>Mevcut mu?: " . (is_dir($dashboard_upload_dir) ? 'EVET' : 'HAYIR') . "</p>";
echo "<p>Yazılabilir mi?: " . (is_writable($dashboard_upload_dir) ? 'EVET' : 'HAYIR') . "</p>";

echo "<p><strong>Images Dir:</strong> " . $images_dir . "</p>";
echo "<p>Mevcut mu?: " . (is_dir($images_dir) ? 'EVET' : 'HAYIR') . "</p>";

// Uploads klasöründeki dosyaları listele
echo "<h3>uploads/properties/ içindeki dosyalar:</h3>";
if (is_dir($dashboard_upload_dir)) {
    $files = scandir($dashboard_upload_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != '.htaccess') {
            echo "<p>- $file</p>";
        }
    }
    echo "<p>Toplam dosya sayısı: " . (count($files) - 2) . "</p>";
} else {
    echo "<p>Klasör bulunamadı!</p>";
}

// Test dosyası yazma
echo "<h3>Test Dosyası Yazma:</h3>";
$test_file = $dashboard_upload_dir . 'test_' . time() . '.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<p style='color: green;'>✓ Test dosyası başarıyla yazıldı: " . basename($test_file) . "</p>";
    unlink($test_file); // Temizle
} else {
    echo "<p style='color: red;'>✗ Test dosyası yazılamadı!</p>";
}

// property-image.php test
echo "<h3>property-image.php Test:</h3>";
echo "<p>Test URL: <a href='property-image.php?path=test.jpg'>property-image.php?path=test.jpg</a></p>";

// Common functions test
if (file_exists('includes/common-functions.php')) {
    include 'includes/common-functions.php';
    echo "<h3>Common Functions Test:</h3>";
    echo "<p>Test Image Path: " . getImagePathSingle('test.jpg') . "</p>";
}
?>
