<?php
// Basit ilan listesi - hangi ilanın resimleri yok görelim
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db.php';

echo "<h2>İlan Resimleri Kontrol</h2>";

// Tüm ilanları listele
$query = "SELECT id, title, images, main_image FROM properties ORDER BY id DESC LIMIT 10";
$result = $conn->query($query);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Resim Durumu</th><th>İşlem</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
        
        // Resim durumu
        if (empty($row['images']) || $row['images'] == '[]') {
            echo "<td style='color: red; font-weight: bold;'>RESİM YOK</td>";
        } else {
            $images = json_decode($row['images'], true);
            if (is_array($images)) {
                echo "<td style='color: green;'>" . count($images) . " resim var</td>";
            } else {
                echo "<td style='color: red;'>JSON HATASI</td>";
            }
        }
        
        echo "<td><a href='add-property.php?edit=" . $row['id'] . "' target='_blank'>Düzenle</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hata: " . $conn->error . "</p>";
}

echo "<h3>Hangi ilanın resimleri yok?</h3>";
echo "<p>Yukarıdaki listeden 'RESİM YOK' yazan ilanın ID'sini not edin.</p>";
?>
