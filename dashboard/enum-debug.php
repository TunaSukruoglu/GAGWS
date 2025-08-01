<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include '../db.php';
    
    // ENUM kontrolü
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "Database'de usage_status kolonu:\n";
        echo "Type: " . $row['Type'] . "\n\n";
        
        // ENUM değerlerini parse et
        preg_match_all("/'([^']+)'/", $row['Type'], $matches);
        if (isset($matches[1])) {
            $valid_values = $matches[1];
            echo "Geçerli ENUM değerleri (" . count($valid_values) . " adet):\n";
            foreach ($valid_values as $i => $val) {
                echo "  " . ($i+1) . ". '$val'\n";
            }
            
            echo "\nTest değerleri kontrolü:\n";
            $test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
            foreach ($test_values as $test) {
                $found = false;
                foreach ($valid_values as $valid) {
                    if (strcasecmp($test, $valid) === 0) {
                        $found = true;
                        break;
                    }
                }
                $status = $found ? 'GEÇERLİ ✓' : 'GEÇERSİZ ✗';
                echo "  '$test' -> $status\n";
                
                if (!$found) {
                    // En yakın eşleşmeyi bul
                    $closest = '';
                    $min_distance = 999;
                    foreach ($valid_values as $valid) {
                        $distance = levenshtein(strtolower($test), strtolower($valid));
                        if ($distance < $min_distance) {
                            $min_distance = $distance;
                            $closest = $valid;
                        }
                    }
                    echo "    En yakın: '$closest'\n";
                }
            }
        }
    } else {
        echo "usage_status kolonu bulunamadı!\n";
    }
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?>
