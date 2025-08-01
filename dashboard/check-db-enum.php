<?php
include '../db.php';

echo "<h2>Database Column Check</h2>";

// Usage Status sütunu kontrolü
$query = "SHOW COLUMNS FROM properties LIKE 'usage_status'";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    echo "<h3>usage_status Column Info:</h3>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
    
    // ENUM değerlerini parse et
    $type = $row['Type'];
    echo "<p><strong>Raw Type:</strong> " . htmlspecialchars($type) . "</p>";
    
    if (preg_match_all("/'([^']+)'/", $type, $matches)) {
        echo "<h4>Allowed ENUM Values:</h4>";
        echo "<ol>";
        foreach ($matches[1] as $i => $value) {
            echo "<li><code>'" . htmlspecialchars($value) . "'</code></li>";
        }
        echo "</ol>";
        
        echo "<h4>Test Values:</h4>";
        $test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
        foreach ($test_values as $test_val) {
            $found = in_array($test_val, $matches[1]);
            echo "<p>✅ <code>'$test_val'</code> - " . ($found ? "<span style='color:green'>VALID</span>" : "<span style='color:red'>INVALID</span>") . "</p>";
        }
    }
} else {
    echo "Column not found!";
}

// Mevcut verileri kontrol et
echo "<h3>Current Data in Database:</h3>";
$data_query = "SELECT DISTINCT usage_status FROM properties WHERE usage_status IS NOT NULL LIMIT 10";
$data_result = $conn->query($data_query);

if ($data_result) {
    echo "<ul>";
    while ($row = $data_result->fetch_assoc()) {
        echo "<li><code>'" . htmlspecialchars($row['usage_status']) . "'</code></li>";
    }
    echo "</ul>";
}
?>
