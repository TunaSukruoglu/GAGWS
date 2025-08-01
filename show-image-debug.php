<?php
// Debug show-image.php
echo "<h1>Show-Image.php Debug</h1>";

$test_image = 'property_687922ad3f8b0_1752769197.jpg';
echo "<h2>Test Resmi: $test_image</h2>";

// Olası yolları kontrol et
$possible_paths = [
    __DIR__ . '/dashboard/uploads/properties/' . $test_image,
    __DIR__ . '/uploads/properties/' . $test_image,
    __DIR__ . '/images/' . $test_image,
    __DIR__ . '/dashboard/images/' . $test_image
];

foreach ($possible_paths as $i => $path) {
    $exists = file_exists($path);
    $is_file = is_file($path);
    $size = $exists ? filesize($path) : 0;
    
    echo "<p><strong>Path " . ($i + 1) . ":</strong> $path</p>";
    echo "<p>- Exists: " . ($exists ? "✅ Yes" : "❌ No") . "</p>";
    echo "<p>- Is File: " . ($is_file ? "✅ Yes" : "❌ No") . "</p>";
    echo "<p>- Size: " . ($size > 0 ? number_format($size) . " bytes" : "0 bytes") . "</p>";
    echo "<hr>";
}

// Direkt show-image.php test
echo "<h2>Direkt Show-Image.php Test:</h2>";
echo "<p>URL: <a href='show-image.php?img=$test_image' target='_blank'>show-image.php?img=$test_image</a></p>";
echo "<img src='show-image.php?img=$test_image' style='max-width: 400px; border: 1px solid #ccc;' alt='Test Image'>";

// Başka test resimleri
echo "<h2>Diğer Test Resimleri:</h2>";
$other_images = [
    'property_6879280a6c5ff_1752770570.jpg',
    'property_687228d39285f_1752312019.png'
];

foreach ($other_images as $img) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ddd; display: inline-block;'>";
    echo "<p>$img</p>";
    echo "<img src='show-image.php?img=$img' style='max-width: 200px; max-height: 150px;' alt='$img'>";
    echo "</div>";
}
?>
