<?php
include 'db.php';

$property_id = $_GET['id'] ?? 1;

echo "<h2>Property Details Debug - ID: $property_id</h2>";

// Property bilgilerini al
$query = "SELECT * FROM properties WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='color: red;'>Property bulunamadı!</p>";
    exit;
}

echo "<h3>Property Raw Data:</h3>";
echo "ID: " . $property['id'] . "<br>";
echo "Title: " . htmlspecialchars($property['title']) . "<br>";
echo "Images: [" . htmlspecialchars($property['images']) . "]<br>";
echo "Main Image: [" . htmlspecialchars($property['main_image']) . "]<br><br>";

echo "<h3>Images Processing:</h3>";

// Images array oluştur (aynı mantık property-details.php'de olduğu gibi)
$images = [];
if (!empty($property['images'])) {
    // Try JSON decode first
    $decoded = json_decode($property['images'], true);
    if (is_array($decoded) && !empty($decoded)) {
        $images = $decoded;
        echo "✅ JSON decode başarılı: " . count($images) . " resim<br>";
    } else {
        // Fallback to comma split
        $images = explode(',', $property['images']);
        echo "⚠️ JSON decode başarısız, comma split kullanıldı<br>";
    }
    
    // Quick cleanup and path correction
    $corrected_images = [];
    foreach ($images as $image) {
        if (!empty(trim($image))) {
            $filename = basename(trim($image));
            $smart_path = "smart-image.php?img=" . urlencode($filename);
            $corrected_images[] = $smart_path;
            echo "Resim: " . htmlspecialchars($filename) . " -> " . htmlspecialchars($smart_path) . "<br>";
        }
    }
    $images = $corrected_images;
}

// Default image if none found
if (empty($images)) {
    $images = ['smart-image.php?img=GA.jpg'];
    echo "⚠️ Hiç resim bulunamadı, default resim kullanılıyor<br>";
}

echo "<h3>Final Images Array:</h3>";
foreach ($images as $i => $img) {
    echo "[$i] " . htmlspecialchars($img) . "<br>";
}

echo "<h3>Test Resim Yükleme:</h3>";
foreach ($images as $i => $img) {
    echo "<p>Resim $i: <a href='" . htmlspecialchars($img) . "' target='_blank'>" . htmlspecialchars($img) . "</a></p>";
    echo "<img src='" . htmlspecialchars($img) . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc; margin: 5px;' alt='Test'><br>";
}

echo "<h3>✅ Debug tamamlandı!</h3>";
?>
