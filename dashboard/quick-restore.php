<?php
// Acil fotoğraf geri getirme scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../db.php';

echo "<h2>Acil Fotoğraf Geri Getirme</h2>";

// Uploads klasöründeki son fotoğrafları al
$upload_dir = "uploads/properties/";
$files = scandir($upload_dir);

// Timestamp'e göre grupla
$groups = [];
foreach ($files as $file) {
    if ($file != '.' && $file != '..' && is_file($upload_dir . $file)) {
        if (preg_match('/property_[a-f0-9]+_(\d+)/', $file, $matches)) {
            $timestamp = $matches[1];
            $groups[$timestamp][] = $file;
        }
    }
}

// Son 5 grubu göster
krsort($groups);
$groups = array_slice($groups, 0, 5, true);

echo "<h3>Son Yüklenen Fotoğraf Grupları:</h3>";

foreach ($groups as $timestamp => $files) {
    $date = date('Y-m-d H:i:s', $timestamp);
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h4>$date</h4>";
    echo "<p>" . count($files) . " fotoğraf</p>";
    
    // İlk 3 fotoğrafı göster
    for ($i = 0; $i < min(3, count($files)); $i++) {
        echo "<img src='$upload_dir{$files[$i]}' style='width: 100px; height: 100px; object-fit: cover; margin: 5px;'>";
    }
    
    echo "<br><br>";
    echo "<form method='post' style='display: inline;'>";
    echo "<input type='hidden' name='action' value='restore'>";
    echo "<input type='hidden' name='files' value='" . implode(',', $files) . "'>";
    echo "<label>Property ID: <input type='number' name='property_id' style='width: 80px;' required></label> ";
    echo "<input type='submit' value='Geri Getir' style='background: green; color: white; padding: 5px 10px;'>";
    echo "</form>";
    echo "</div>";
}

// Geri getirme işlemi
if (isset($_POST['action']) && $_POST['action'] == 'restore') {
    $property_id = intval($_POST['property_id']);
    $files = explode(',', $_POST['files']);
    
    if ($property_id > 0 && !empty($files)) {
        $images_json = json_encode($files);
        $main_image = $files[0];
        
        $stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ? WHERE id = ?");
        $stmt->bind_param("ssi", $images_json, $main_image, $property_id);
        
        if ($stmt->execute()) {
            echo "<div style='background: green; color: white; padding: 10px; margin: 10px;'>✓ Property ID $property_id için " . count($files) . " fotoğraf geri getirildi!</div>";
        } else {
            echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>✗ Hata: " . $stmt->error . "</div>";
        }
    }
}

// Fotoğrafı olmayan property'leri göster
echo "<h3>Fotoğrafı Olmayan Property'ler:</h3>";
$result = $conn->query("SELECT id, title FROM properties WHERE images IS NULL OR images = '' OR images = '[]' ORDER BY id DESC LIMIT 10");

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Başlık</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Tüm property'lerin fotoğrafları mevcut!</p>";
}
?>
