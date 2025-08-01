<?php
// Database bağlantısı
include 'db.php';

try {
    // Backup table oluştur
    $sql1 = "CREATE TABLE IF NOT EXISTS properties_features_backup AS SELECT * FROM properties LIMIT 0";
    $conn->query($sql1);
    echo "✓ Backup table created successfully\n";
    
    // Features column'u ekle
    $sql2 = "ALTER TABLE properties ADD COLUMN features TEXT AFTER main_image";
    $conn->query($sql2);
    echo "✓ Features column added successfully\n";
    
    // Mevcut kayıtları boş JSON ile güncelle
    $sql3 = "UPDATE properties SET features = '{}' WHERE features IS NULL";
    $conn->query($sql3);
    echo "✓ Existing records updated with empty JSON\n";
    
    echo "\n✅ Database update completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
