<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "<h3>Database USAGE_STATUS Kolonu Debug</h3>";

try {
    // ENUM değerlerini kontrol et
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'usage_status'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<h4>usage_status kolonu bilgisi:</h4>";
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        
        // ENUM değerlerini parse et
        if (isset($row['Type'])) {
            preg_match("/^enum\((.+)\)$/", $row['Type'], $matches);
            if (isset($matches[1])) {
                $enum_values = str_getcsv($matches[1], ',', "'");
                echo "<h4>Geçerli ENUM değerleri:</h4>";
                echo "<ul>";
                foreach ($enum_values as $value) {
                    echo "<li>'$value'</li>";
                }
                echo "</ul>";
            }
        }
    } else {
        echo "usage_status kolonu bulunamadı!";
    }
    
    // Mevcut kayıtlardaki usage_status değerlerini kontrol et
    echo "<h4>Mevcut kayıtlardaki usage_status değerleri:</h4>";
    $usage_result = $conn->query("SELECT DISTINCT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status");
    if ($usage_result) {
        echo "<ul>";
        while ($usage_row = $usage_result->fetch_assoc()) {
            echo "<li>'" . htmlspecialchars($usage_row['usage_status']) . "' (" . $usage_row['count'] . " kayıt)</li>";
        }
        echo "</ul>";
    }
    
    // Test değerleri
    echo "<h4>Test Değerleri:</h4>";
    $test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
    foreach ($test_values as $test_value) {
        echo "<p>Test: '$test_value' - ";
        
        // Test sorgusu çalıştır
        $test_stmt = $conn->prepare("SELECT ? as test_value");
        $test_stmt->bind_param("s", $test_value);
        
        if ($test_stmt->execute()) {
            echo "<span style='color: green;'>✓ Geçerli format</span>";
        } else {
            echo "<span style='color: red;'>✗ Hata: " . $test_stmt->error . "</span>";
        }
        echo "</p>";
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?>
