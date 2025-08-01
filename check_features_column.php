<?php
// Database bağlantısı
include 'db.php';

try {
    // Properties tablosunun sütunlarını kontrol et
    $result = $conn->query("SHOW COLUMNS FROM properties");
    echo "Properties table columns:\n";
    echo "========================\n";
    
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    // Features sütunu var mı kontrol et
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'features'");
    if ($result->num_rows > 0) {
        echo "\n✅ Features column exists!\n";
        
        // Mevcut kayıtları boş JSON ile güncelle
        $sql3 = "UPDATE properties SET features = '{}' WHERE features IS NULL OR features = ''";
        $conn->query($sql3);
        echo "✓ Existing records updated with empty JSON\n";
        
    } else {
        echo "\n❌ Features column does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
