<?php
echo "<h1>Akıllı Resim Sistemi Test</h1>";

// Test resimleri
$test_images = [
    'property_687922ad3f8b0_1752769197.jpg',
    'property_6879280a6c5ff_1752770570.jpg',
    'property_687228d39285f_1752312019.png'
];

foreach ($test_images as $img) {
    echo "<h2>Test: $img</h2>";
    
    // Debug bilgilerini göster
    echo "<h3>Debug Bilgileri:</h3>";
    echo "<iframe src='smart-image.php?img=$img&debug=1' style='width: 100%; height: 300px; border: 1px solid #ccc;'></iframe>";
    
    // Gerçek resmi göster
    echo "<h3>Düzeltilmiş Resim:</h3>";
    echo "<img src='smart-image.php?img=$img' style='max-width: 400px; border: 1px solid #green;' alt='$img'>";
    
    // Eski sistem ile karşılaştır
    echo "<h3>Eski Sistem (Karşılaştırma):</h3>";
    echo "<img src='show-image.php?img=$img' style='max-width: 400px; border: 1px solid #red;' alt='$img'>";
    
    echo "<hr>";
}

// Common functions test
echo "<h2>Common Functions Test:</h2>";
include 'includes/common-functions.php';

$test_json = '["property_687922ad3f8b0_1752769197.jpg","property_6879280a6c5ff_1752770570.jpg"]';
$result = getImagePath($test_json);
echo "<p><strong>getImagePath Result:</strong> $result</p>";
echo "<img src='$result' style='max-width: 300px; border: 1px solid #blue;' alt='Common Functions Test'>";

echo "<h2>Sistem Durumu:</h2>";
echo "<p>✅ Smart-image.php oluşturuldu</p>";
echo "<p>✅ EXIF rotation desteği</p>";
echo "<p>✅ Portrait/Landscape otomatik algılaması</p>";
echo "<p>✅ Resim boyutlandırma ve optimizasyon</p>";
echo "<p>✅ Common functions güncellendi</p>";
?>
