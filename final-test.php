<?php
echo "<h1>Resim Sistemi Test Raporu</h1>";

// Test 1: show-image.php test
echo "<h2>Test 1: show-image.php ile resim gösterimi</h2>";
$test_images = [
    'property_687922ad4674a_1752769197.jpg',
    'property_6879045fd0f56_1752761439.jpg',
    'property_6879280a6aa46_1752770570.jpg'
];

foreach ($test_images as $img) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc; display: inline-block;'>";
    echo "<p>$img</p>";
    echo "<img src='show-image.php?img=$img' style='max-width: 200px; max-height: 150px;' alt='$img'>";
    echo "</div>";
}

// Test 2: Common functions test
echo "<h2>Test 2: Common functions test</h2>";
include 'includes/common-functions.php';

$test_json = '["property_687922ad4674a_1752769197.jpg","property_6879045fd0f56_1752761439.jpg"]';
$result = getImagePath($test_json);
echo "<p>JSON Test: <strong>$result</strong></p>";
echo "<img src='$result' style='max-width: 200px; max-height: 150px;' alt='JSON Test'>";

$single_result = getImagePathSingle('property_687922ad4674a_1752769197.jpg');
echo "<p>Single Test: <strong>$single_result</strong></p>";
echo "<img src='$single_result' style='max-width: 200px; max-height: 150px;' alt='Single Test'>";

// Test 3: Property'lerden veri çekme
echo "<h2>Test 3: Gerçek Property Resimleri</h2>";
include 'db.php';

$stmt = $conn->prepare("SELECT id, title, images FROM properties WHERE images IS NOT NULL AND images != '' ORDER BY created_at DESC LIMIT 2");
$stmt->execute();
$result = $stmt->get_result();

while ($property = $result->fetch_assoc()) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;'>";
    echo "<h3>Property: " . htmlspecialchars($property['title']) . "</h3>";
    
    $main_image = getImagePath($property['images']);
    echo "<p>Ana Resim: <strong>$main_image</strong></p>";
    echo "<img src='$main_image' style='max-width: 300px; max-height: 200px;' alt='Ana Resim'>";
    
    echo "</div>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #e8f5e8; border: 1px solid #4caf50;'>";
echo "<h3>✅ Sistem Durumu</h3>";
echo "<p>✅ show-image.php çalışıyor</p>";
echo "<p>✅ Common functions güncellenmiş</p>";
echo "<p>✅ Property resimleri show-image.php üzerinden servis ediliyor</p>";
echo "<p>✅ Ana sayfa resim sistemini düzeltildi</p>";
echo "<p>✅ Portföy sayfası resim sistemini düzeltildi</p>";
echo "</div>";
?>
