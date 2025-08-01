<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "Usage Status ENUM Düzeltme İşlemi\n";
echo "=================================\n\n";

try {
    // Önce mevcut yapıyı kontrol et
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Mevcut ENUM: " . $row['Type'] . "\n\n";
    }
    
    // ENUM'u düzelt - Türkçe karakter olmadan
    $alter_sql = "ALTER TABLE properties MODIFY COLUMN usage_status ENUM('Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli') NOT NULL DEFAULT 'Bos'";
    
    echo "Çalıştırılacak SQL:\n";
    echo $alter_sql . "\n\n";
    
    if ($conn->query($alter_sql)) {
        echo "✓ usage_status ENUM başarıyla güncellendi!\n\n";
        
        // Yeni yapıyı kontrol et
        $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
        if ($result && $row = $result->fetch_assoc()) {
            echo "Yeni ENUM: " . $row['Type'] . "\n\n";
        }
        
        // Test değerleri
        echo "Test Değerleri:\n";
        $test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
        foreach ($test_values as $test) {
            echo "✓ '$test'\n";
        }
        
    } else {
        echo "✗ ENUM güncelleme hatası: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Hata: " . $e->getMessage() . "\n";
}
?>
