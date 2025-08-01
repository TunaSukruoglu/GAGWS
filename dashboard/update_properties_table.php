<?php
// Properties tablosunu güncelle
require_once 'db.php';

echo "<h2>Properties Tablosu Güncelleniyor...</h2>";

try {
    // Mevcut tablo yapısını kontrol et
    echo "<h3>Mevcut Tablo Yapısı:</h3>";
    $result = $conn->query("DESCRIBE properties");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Özellik sütunlarını ekle
    $feature_columns = [
        'interior_features' => 'İç Özellikler',
        'exterior_features' => 'Dış Özellikler', 
        'neighborhood_features' => 'Muhit Özellikleri',
        'transportation_features' => 'Ulaşım Özellikleri',
        'view_features' => 'Manzara Özellikleri',
        'housing_type_features' => 'Konut Tipi Özellikleri'
    ];
    
    echo "<h3>Sütun Ekleme İşlemleri:</h3>";
    
    foreach ($feature_columns as $column => $description) {
        // Önce sütunun var olup olmadığını kontrol et
        $check_query = "SHOW COLUMNS FROM properties LIKE '$column'";
        $check_result = $conn->query($check_query);
        
        if ($check_result->num_rows == 0) {
            // Sütun yoksa ekle
            $add_query = "ALTER TABLE properties ADD COLUMN `$column` JSON DEFAULT NULL";
            if ($conn->query($add_query)) {
                echo "<p style='color: green;'>✓ $description ($column) sütunu başarıyla eklendi</p>";
            } else {
                echo "<p style='color: red;'>✗ $description ($column) sütunu eklenemedi: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ $description ($column) sütunu zaten mevcut</p>";
        }
    }
    
    // Güncellenmiş tablo yapısını göster
    echo "<h3>Güncellenmiş Tablo Yapısı:</h3>";
    $result = $conn->query("DESCRIBE properties");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $highlight = (strpos($row['Field'], 'features') !== false) ? 'style="background-color: #e6f3ff;"' : '';
            echo "<tr $highlight>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Özellik sütunlarını kontrol et
    echo "<h3>Özellik Sütunları Kontrolü:</h3>";
    $feature_check_query = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
                           FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE TABLE_SCHEMA = 'gokhanaydinli_db' 
                           AND TABLE_NAME = 'properties' 
                           AND COLUMN_NAME LIKE '%features%'";
    
    $feature_result = $conn->query($feature_check_query);
    if ($feature_result && $feature_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Table</th><th>Column</th><th>Type</th><th>Nullable</th><th>Default</th></tr>";
        while ($row = $feature_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['TABLE_NAME'] . "</td>";
            echo "<td>" . $row['COLUMN_NAME'] . "</td>";
            echo "<td>" . $row['DATA_TYPE'] . "</td>";
            echo "<td>" . $row['IS_NULLABLE'] . "</td>";
            echo "<td>" . $row['COLUMN_DEFAULT'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3 style='color: green;'>✓ Tablo güncelleme işlemi tamamlandı!</h3>";
    echo "<p><a href='add-property.php'>← İlan Ekleme Sayfasına Dön</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Hata: " . $e->getMessage() . "</h3>";
}

$conn->close();
?>
