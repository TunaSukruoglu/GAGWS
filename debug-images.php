<?php
include 'db.php';

// Son eklenen öne çıkan ilanın resim bilgilerini kontrol et
$query = $conn->prepare("
    SELECT id, title, images, main_image, featured 
    FROM properties 
    WHERE featured = 1 
    ORDER BY created_at DESC 
    LIMIT 5
");
$query->execute();
$properties = $query->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h2>Öne Çıkan İlanların Resim Bilgileri:</h2>";

if (empty($properties)) {
    echo "<p>Henüz öne çıkan ilan yok.</p>";
} else {
    foreach ($properties as $property) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<h3>İlan ID: " . $property['id'] . " - " . htmlspecialchars($property['title']) . "</h3>";
        echo "<p><strong>Featured:</strong> " . ($property['featured'] ? 'Evet' : 'Hayır') . "</p>";
        echo "<p><strong>Images:</strong> " . htmlspecialchars($property['images']) . "</p>";
        echo "<p><strong>Main Image:</strong> " . htmlspecialchars($property['main_image']) . "</p>";
        
        // Images alanını parse et
        if (!empty($property['images'])) {
            $images = json_decode($property['images'], true);
            if (is_array($images)) {
                echo "<p><strong>JSON Parse Sonucu:</strong></p><ul>";
                foreach ($images as $img) {
                    $img_path = "dashboard/uploads/properties/" . $img;
                    $exists = file_exists($img_path) ? "✅ VAR" : "❌ YOK";
                    echo "<li>" . htmlspecialchars($img) . " - " . $exists . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p><strong>JSON değil, virgülle ayrılmış:</strong></p>";
                $split_images = explode(',', $property['images']);
                echo "<ul>";
                foreach ($split_images as $img) {
                    $img = trim($img);
                    $img_path = "dashboard/uploads/properties/" . $img;
                    $exists = file_exists($img_path) ? "✅ VAR" : "❌ YOK";
                    echo "<li>" . htmlspecialchars($img) . " - " . $exists . "</li>";
                }
                echo "</ul>";
            }
        }
        echo "</div>";
    }
}
?>
