<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Basit Test</h2>";

try {
    include '../db.php';
    echo "✅ DB OK<br>";
    
    // Tablo yapısını kontrol et
    echo "<h3>Properties Table Structure:</h3>";
    $result = $conn->query("DESCRIBE properties");
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} ({$row['Type']})<br>";
    }
    
    // Basit bir property getir
    echo "<h3>Sample Property:</h3>";
    $prop = $conn->query("SELECT * FROM properties LIMIT 1");
    if ($prop && $prop->num_rows > 0) {
        $property = $prop->fetch_assoc();
        echo "<pre>";
        print_r($property);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<hr>";
echo "<a href='edit-property.php?id=1'>Test Edit Property</a>";
?>
