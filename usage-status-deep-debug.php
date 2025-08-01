<?php
include 'db.php';

echo "Usage Status ENUM Deep Debug\n";
echo "============================\n\n";

// ENUM tanımını kontrol et
$result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
$row = $result->fetch_assoc();
echo "Current ENUM definition: " . $row['Type'] . "\n\n";

// Mevcut usage_status değerlerini kontrol et
$result = $conn->query("SELECT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status");
echo "Existing values in database:\n";
while ($row = $result->fetch_assoc()) {
    $value = $row['usage_status'];
    $count = $row['count'];
    $display = empty($value) ? '[EMPTY]' : "'{$value}'";
    echo "  {$display}: {$count} records\n";
}

// Test direct insert of individual ENUM values
echo "\nDirect ENUM Test:\n";
$test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];

foreach ($test_values as $test_value) {
    try {
        $stmt = $conn->prepare("INSERT INTO properties (user_id, title, description, price, type, category, listing_type, area, usage_status, status) VALUES (1, 'Test ENUM', 'Test', 100000, 'sale', 'apartment', 'Satılık', 50, ?, 'active')");
        $stmt->bind_param("s", $test_value);
        
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            echo "✓ '{$test_value}' -> SUCCESS (ID: {$id})\n";
            
            // Verify what was actually saved
            $check = $conn->query("SELECT usage_status FROM properties WHERE id = {$id}");
            $saved = $check->fetch_assoc()['usage_status'];
            $saved_display = empty($saved) ? '[EMPTY]' : "'{$saved}'";
            echo "  Saved as: {$saved_display}\n";
            
        } else {
            echo "✗ '{$test_value}' -> ERROR: " . $stmt->error . "\n";
        }
    } catch (Exception $e) {
        echo "✗ '{$test_value}' -> EXCEPTION: " . $e->getMessage() . "\n";
    }
}
?>
