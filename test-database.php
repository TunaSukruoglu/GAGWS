<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'db.php';
    
    // Properties tablosunun yapısını kontrol et
    $query = "SHOW COLUMNS FROM properties";
    $result = $conn->query($query);
    
    echo "<h3>Properties Tablosu Yapısı:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while($row = $result->fetch_assoc()) {
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
    
    // Eksik alanları ekle
    $missing_columns = [
        'building_floors' => 'VARCHAR(20) DEFAULT NULL',
        'parking' => 'VARCHAR(50) DEFAULT NULL',
        'elevator' => 'VARCHAR(10) DEFAULT NULL', 
        'usage_status' => 'VARCHAR(20) DEFAULT NULL',
        'credit_eligible' => 'VARCHAR(10) DEFAULT NULL',
        'deed_status' => 'VARCHAR(20) DEFAULT NULL',
        'exchange' => 'VARCHAR(10) DEFAULT NULL'
    ];
    
    echo "<h3>Eksik Alanları Ekleme:</h3>";
    
    foreach($missing_columns as $column => $definition) {
        try {
            $check_query = "SHOW COLUMNS FROM properties LIKE '$column'";
            $check_result = $conn->query($check_query);
            
            if($check_result->num_rows == 0) {
                $alter_query = "ALTER TABLE properties ADD $column $definition";
                if($conn->query($alter_query)) {
                    echo "<p style='color: green;'>✅ $column alanı başarıyla eklendi</p>";
                } else {
                    echo "<p style='color: red;'>❌ $column eklenirken hata: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color: blue;'>ℹ️ $column alanı zaten mevcut</p>";
            }
        } catch(Exception $e) {
            echo "<p style='color: red;'>❌ $column kontrol hatası: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test verisi kontrol et
    echo "<h3>ID 40 Test Verisi:</h3>";
    $test_query = "SELECT parking, elevator, usage_status, building_floors, credit_eligible, deed_status, exchange FROM properties WHERE id = 40 LIMIT 1";
    $test_result = $conn->query($test_query);
    
    if($test_result && $test_result->num_rows > 0) {
        $test_data = $test_result->fetch_assoc();
        echo "<pre>";
        print_r($test_data);
        echo "</pre>";
    } else {
        echo "<p>Test verisi bulunamadı</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?>
