<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "<h2>USAGE_STATUS ENUM Değerleri Kontrolü</h2>";

try {
    // Detaylı ENUM kontrolü
    $result = $conn->query("SHOW CREATE TABLE properties");
    if ($result && $row = $result->fetch_assoc()) {
        $create_table = $row['Create Table'];
        
        // usage_status satırını bul
        if (preg_match("/`usage_status`\s+enum\(([^)]+)\)/i", $create_table, $matches)) {
            echo "<h3>usage_status ENUM tanımı:</h3>";
            echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";
            
            // ENUM değerlerini parse et
            $enum_string = $matches[1];
            $enum_values = [];
            
            // Tek tırnak içindeki değerleri bul
            preg_match_all("/'([^']+)'/", $enum_string, $value_matches);
            if (isset($value_matches[1])) {
                $enum_values = $value_matches[1];
                
                echo "<h3>Geçerli değerler:</h3>";
                echo "<ul>";
                foreach ($enum_values as $i => $value) {
                    echo "<li>" . ($i+1) . ". '<strong>" . htmlspecialchars($value) . "</strong>'</li>";
                }
                echo "</ul>";
                
                // Test değerleri ile karşılaştır
                echo "<h3>Test Sonuçları:</h3>";
                $test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
                
                foreach ($test_values as $test) {
                    $found = in_array($test, $enum_values);
                    $color = $found ? 'green' : 'red';
                    $icon = $found ? '✓' : '✗';
                    echo "<p style='color: $color;'>$icon '$test' - " . ($found ? 'Geçerli' : 'Geçersiz') . "</p>";
                    
                    if (!$found) {
                        // En yakın eşleşmeyi bul
                        $closest = '';
                        $closest_distance = 999;
                        foreach ($enum_values as $enum_val) {
                            $distance = levenshtein(strtolower($test), strtolower($enum_val));
                            if ($distance < $closest_distance) {
                                $closest_distance = $distance;
                                $closest = $enum_val;
                            }
                        }
                        echo "<small style='color: orange;'> → En yakın: '$closest'</small>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>usage_status ENUM bulunamadı!</p>";
        }
    }
    
    // Charset kontrolü
    echo "<h3>Database ve Table Charset:</h3>";
    $charset_result = $conn->query("SHOW TABLE STATUS LIKE 'properties'");
    if ($charset_result && $charset_row = $charset_result->fetch_assoc()) {
        echo "<p>Table Collation: " . $charset_row['Collation'] . "</p>";
    }
    
    // Connection charset
    $conn_charset = $conn->query("SELECT @@character_set_connection, @@collation_connection");
    if ($conn_charset && $conn_row = $conn_charset->fetch_assoc()) {
        echo "<p>Connection Charset: " . $conn_row['@@character_set_connection'] . "</p>";
        echo "<p>Connection Collation: " . $conn_row['@@collation_connection'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?>
