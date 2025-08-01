<?php
// Database column check script
include '../db.php';

echo "<h3>Checking Properties Table Structure</h3>";

try {
    // Get table structure
    $result = $conn->query("DESCRIBE properties");
    
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Required Columns Check</h3>";
    $required_columns = ['usage_status', 'dues', 'credit_eligible', 'deed_status', 'exchange', 'location_type'];
    
    foreach ($required_columns as $column) {
        if (in_array($column, $existing_columns)) {
            echo "<p style='color: green;'>✓ $column - EXISTS</p>";
        } else {
            echo "<p style='color: red;'>✗ $column - MISSING</p>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
