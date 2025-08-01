<?php
// Basit test - enum değerlerini kontrol et
include 'db.php';

// Test INSERT
$test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];

echo "Usage Status ENUM Test\n";
echo "=====================\n\n";

foreach ($test_values as $test_value) {
    try {
        // Test sorgusu - sadece kontrol için
        $stmt = $conn->prepare("SELECT ? as test_usage_status");
        $stmt->bind_param("s", $test_value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            echo "✓ '$test_value' - Format geçerli\n";
        } else {
            echo "✗ '$test_value' - Format hatası\n";
        }
        
    } catch (Exception $e) {
        echo "✗ '$test_value' - Hata: " . $e->getMessage() . "\n";
    }
}

// Database table structure kontrolü
echo "\nTable Structure:\n";
echo "================\n";

try {
    $result = $conn->query("SHOW CREATE TABLE properties");
    if ($result && $row = $result->fetch_assoc()) {
        $create_sql = $row['Create Table'];
        
        // usage_status satırını bul
        $lines = explode("\n", $create_sql);
        foreach ($lines as $line) {
            if (strpos($line, 'usage_status') !== false) {
                echo "Usage Status Definition: " . trim($line) . "\n";
                break;
            }
        }
    }
} catch (Exception $e) {
    echo "Structure check error: " . $e->getMessage() . "\n";
}
?>
