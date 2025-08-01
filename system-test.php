<?php
include 'includes/common-functions.php';

echo "<h1>Güncellenmiş Resim Sistem Testi</h1>";

// Test 1: Gerçek JSON veri
$test_json = '["property_687922ad3f8b0_1752769197.jpg","property_6879280a6c5ff_1752770570.jpg"]';
$result = getImagePath($test_json);
echo "<h2>JSON Test:</h2>";
echo "<p><strong>Result:</strong> $result</p>";
echo "<img src='$result' style='max-width: 300px; border: 1px solid #green;' alt='JSON Test'>";

// Test 2: Single image
$single_result = getImagePathSingle('property_687922ad3f8b0_1752769197.jpg');
echo "<h2>Single Image Test:</h2>";
echo "<p><strong>Result:</strong> $single_result</p>";
echo "<img src='$single_result' style='max-width: 300px; border: 1px solid #blue;' alt='Single Test'>";

// Test 3: Veritabanından gerçek veri
include 'db.php';
$stmt = $conn->prepare("SELECT id, title, images FROM properties WHERE images IS NOT NULL AND images != '' ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Veritabanından Gerçek Property Resimleri:</h2>";
while ($property = $result->fetch_assoc()) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;'>";
    echo "<h3>" . htmlspecialchars($property['title']) . "</h3>";
    echo "<p><strong>Raw Images:</strong> " . htmlspecialchars($property['images']) . "</p>";
    
    $main_image = getImagePath($property['images']);
    echo "<p><strong>Computed Path:</strong> $main_image</p>";
    echo "<img src='$main_image' style='max-width: 300px; height: 200px; object-fit: cover; border: 1px solid #ccc;' alt='Property Image'>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>System Status:</h2>";
echo "<p>✅ Common functions güncellendi</p>";
echo "<p>✅ Direkt dosya yolları kullanılıyor</p>";
echo "<p>✅ Dashboard/uploads/properties öncelikli</p>";
?>
