<?php
// Fotoğrafları geri getirme scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db.php';

echo "<h2>Fotoğraf Geri Getirme Scripti</h2>";

// Uploads klasöründeki tüm dosyaları al
$upload_dir = "uploads/properties/";
$files = scandir($upload_dir);

$property_images = [];
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        // Dosya adından timestamp'i çıkar
        if (preg_match('/property_([a-f0-9]+)_(\d+)/', $file, $matches)) {
            $timestamp = $matches[2];
            $property_images[$timestamp][] = $file;
        } elseif (preg_match('/test_(\d+)_/', $file, $matches)) {
            $timestamp = $matches[1];
            $property_images[$timestamp][] = $file;
        }
    }
}

// Timestamp'lere göre sırala
krsort($property_images);

echo "<h3>Bulunan Fotoğraf Grupları:</h3>";
foreach ($property_images as $timestamp => $files) {
    $date = date('Y-m-d H:i:s', $timestamp);
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h4>Grup: $date ($timestamp)</h4>";
    echo "<p>Dosya sayısı: " . count($files) . "</p>";
    echo "<ul>";
    foreach ($files as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
    
    // Bu grubu bir property'e ata
    $images_json = json_encode($files);
    echo "<form method='post' style='margin: 10px 0;'>";
    echo "<input type='hidden' name='action' value='restore'>";
    echo "<input type='hidden' name='timestamp' value='$timestamp'>";
    echo "<input type='hidden' name='images' value='" . htmlspecialchars($images_json) . "'>";
    echo "<label>Property ID: <input type='number' name='property_id' required></label> ";
    echo "<button type='submit'>Bu fotoğrafları geri getir</button>";
    echo "</form>";
    echo "</div>";
}

// Form gönderildiğinde
if (isset($_POST['action']) && $_POST['action'] == 'restore') {
    $property_id = intval($_POST['property_id']);
    $images = $_POST['images'];
    
    // Veritabanını güncelle
    $stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ? WHERE id = ?");
    $images_array = json_decode($images, true);
    $main_image = !empty($images_array) ? $images_array[0] : '';
    
    if ($stmt->execute([$images, $main_image, $property_id])) {
        echo "<div style='color: green; font-weight: bold;'>Property ID $property_id için fotoğraflar geri getirildi!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>Hata: " . $stmt->error . "</div>";
    }
}

// Mevcut properties'leri listele
echo "<h3>Mevcut Properties:</h3>";
$result = $conn->query("SELECT id, title, images FROM properties ORDER BY id DESC LIMIT 10");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Mevcut Fotoğraflar</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>";
        if (!empty($row['images'])) {
            $images = json_decode($row['images'], true);
            if (is_array($images)) {
                echo count($images) . " fotoğraf";
            } else {
                echo "JSON hatası";
            }
        } else {
            echo "<strong style='color: red;'>Fotoğraf yok!</strong>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
