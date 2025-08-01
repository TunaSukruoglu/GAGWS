 <?php
include 'db.php';

echo "Usage Status ENUM Detaylı Test\n";
echo "==============================\n\n";

// ENUM tanımını kontrol et
$result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
$row = $result->fetch_assoc();
echo "ENUM Definition: " . $row['Type'] . "\n";
echo "Default: " . $row['Default'] . "\n";
echo "Null: " . $row['Null'] . "\n\n";

// Her ENUM değerini tek tek test et
$test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];

foreach ($test_values as $test_value) {
    echo "Testing: '$test_value'\n";
    
    try {
        // Minimal test query
        $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, type, category, listing_type, area, usage_status, status) VALUES (1, 'ENUM Test', 'Test description', 100000, 'sale', 'apartment', 'Satılık', 50, ?, 'active')");
        
        if (!$stmt) {
            echo "  Prepare Error: " . $conn->error . "\n";
            continue;
        }
        
        $bind_result = $stmt->bind_param("s", $test_value);
        if (!$bind_result) {
            echo "  Bind Error: " . $stmt->error . "\n";
            continue;
        }
        
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            echo "  ✓ INSERT SUCCESS (ID: $id)\n";
            
            // Kaydedilen değeri kontrol et
            $check = $conn->query("SELECT usage_status FROM properties WHERE id = $id");
            if ($check && $check_row = $check->fetch_assoc()) {
                $saved_value = $check_row['usage_status'];
                $display = empty($saved_value) ? '[EMPTY]' : "'$saved_value'";
                echo "  Saved as: $display\n";
                
                if ($saved_value !== $test_value) {
                    echo "  ⚠️  WARNING: Value mismatch!\n";
                    echo "  Expected: '$test_value'\n";
                    echo "  Got: $display\n";
                }
            }
            
        } else {
            echo "  ✗ INSERT ERROR: " . $stmt->error . "\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ EXCEPTION: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Character encoding test
echo "Character Encoding Test:\n";
echo "========================\n";

foreach ($test_values as $test_value) {
    echo "Value: '$test_value'\n";
    echo "  Bytes: " . strlen($test_value) . "\n";
    echo "  UTF-8 length: " . mb_strlen($test_value, 'UTF-8') . "\n";
    echo "  Hex: " . bin2hex($test_value) . "\n";
    echo "  Is ASCII: " . (ctype_print($test_value) ? 'Yes' : 'No') . "\n";
    echo "\n";
}
?>
