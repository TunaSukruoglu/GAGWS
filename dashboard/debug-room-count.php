<?php
include __DIR__ . '/../db.php';

// Test için bir property ID'si alıp room_count sütununu kontrol edelim
try {
    $sql = "SELECT * FROM properties LIMIT 1";
    $stmt = $pdo->query($sql);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Property Columns:</h3>";
    if ($property) {
        foreach ($property as $column => $value) {
            if (strpos(strtolower($column), 'room') !== false || strpos(strtolower($column), 'oda') !== false) {
                echo "<strong>$column:</strong> $value<br>";
            }
        }
    }
    
    // Tüm sütunları göster
    echo "<h3>All Columns:</h3>";
    if ($property) {
        echo "<pre>";
        foreach ($property as $column => $value) {
            echo "$column => $value\n";
        }
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
