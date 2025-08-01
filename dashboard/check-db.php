<?php
// Basit veritabanı kontrol scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db.php';

echo "<h2>Veritabanı Kontrol</h2>";

// Tüm properties'leri al
$result = $conn->query("SELECT id, title, images, main_image FROM properties ORDER BY id DESC");

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Images JSON</th><th>Main Image</th><th>Durum</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
        echo "<td>" . htmlspecialchars($row['images']) . "</td>";
        echo "<td>" . htmlspecialchars($row['main_image']) . "</td>";
        
        // Durum kontrolü
        if (empty($row['images'])) {
            echo "<td style='color: red; font-weight: bold;'>FOTOĞRAF YOK!</td>";
        } else {
            $images = json_decode($row['images'], true);
            if (is_array($images)) {
                echo "<td style='color: green;'>" . count($images) . " fotoğraf</td>";
            } else {
                echo "<td style='color: red;'>JSON HATASI</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Veritabanı hatası: " . $conn->error;
}

// Uploads klasöründeki fotoğrafları da göster
echo "<h3>Uploads Klasöründeki Fotoğraflar</h3>";
$upload_dir = "uploads/properties/";
if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    $count = 0;
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $count++;
            if ($count <= 10) {
                echo "<div>$file</div>";
            }
        }
    }
    echo "<p>Toplam " . ($count) . " fotoğraf dosyası bulundu.</p>";
} else {
    echo "<p style='color: red;'>Uploads klasörü bulunamadı!</p>";
}
?>
