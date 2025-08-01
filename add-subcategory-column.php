<?php
// Alt kategori sütunu ekleme script'i
require_once 'db.php';

try {
    // Properties tablosuna subcategory sütunu ekle
    $sql = "ALTER TABLE properties ADD COLUMN subcategory VARCHAR(100) DEFAULT '' AFTER category";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Subcategory sütunu başarıyla eklendi.<br>";
    } else {
        echo "❌ Hata: " . $conn->error . "<br>";
    }
    
    // Tablonun güncel yapısını kontrol et
    $result = $conn->query("DESCRIBE properties");
    echo "<h3>Properties tablosu yapısı:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage();
}

$conn->close();
?>
