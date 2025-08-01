<?php
// First check with db.php (PDO)
echo "<h2>Database Connection Check</h2>";

try {
    require_once 'db.php';
    echo "<h3>PDO Connection (db.php) - SUCCESS</h3>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM properties WHERE Field = 'living_room_count'");
    $column = $stmt->fetch();
    
    if ($column) {
        echo "<p style='color: green;'>✅ living_room_count exists in PDO connection</p>";
        echo "<p>Column details: " . print_r($column, true) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ living_room_count NOT found in PDO connection</p>";
        
        // Try to add it again
        echo "<p>Attempting to add column again...</p>";
        $pdo->exec("ALTER TABLE properties ADD COLUMN living_room_count INT DEFAULT 1 AFTER bedrooms");
        echo "<p style='color: green;'>✅ Column added via PDO</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>PDO Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// Now check with mysqli connection (like in add-property.php)
echo "<h3>MySQLi Connection Check</h3>";
try {
    // Using the same connection method as add-property.php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "emlakci";
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ MySQLi Connection - SUCCESS</p>";
    
    // Check if column exists in mysqli
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'living_room_count'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ living_room_count exists in MySQLi connection</p>";
        $row = $result->fetch_assoc();
        echo "<p>Column details: " . print_r($row, true) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ living_room_count NOT found in MySQLi connection</p>";
        
        // Try to add it via mysqli
        echo "<p>Attempting to add column via MySQLi...</p>";
        if ($conn->query("ALTER TABLE properties ADD COLUMN living_room_count INT DEFAULT 1 AFTER bedrooms")) {
            echo "<p style='color: green;'>✅ Column added via MySQLi</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add column: " . $conn->error . "</p>";
        }
    }
    
    // Show all columns to verify
    echo "<h4>All Properties Table Columns (MySQLi)</h4>";
    $result = $conn->query("SHOW COLUMNS FROM properties");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $highlight = in_array($row['Field'], ['room_count', 'bedrooms', 'living_room_count']) ? ' style="background-color: yellow;"' : '';
        echo "<tr{$highlight}>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>MySQLi Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Testing INSERT Query</h3>";
echo "<p>Testing the exact query that's failing...</p>";

try {
    $conn = new mysqli("localhost", "root", "", "emlakci");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Test a simple insert with living_room_count
    $test_query = "INSERT INTO properties SET 
        user_id = 1,
        title = 'TEST PROPERTY',
        description = 'Test description',
        price = 100000,
        type = 'sale',
        category = 'apartment',
        subcategory = 'daire',
        listing_type = 'Satılık',
        area_gross = 100,
        area_net = 90,
        area = 100,
        address = 'Test Address',
        city = 'Test City',
        district = 'Test District',
        room_count = 3,
        bedrooms = 2,
        living_room_count = 1,
        bathrooms = 1,
        status = 'active',
        created_at = NOW()";
    
    echo "<p>Test Query:</p>";
    echo "<pre>" . htmlspecialchars($test_query) . "</pre>";
    
    if ($conn->query($test_query)) {
        echo "<p style='color: green;'>✅ Test INSERT successful!</p>";
        $test_id = $conn->insert_id;
        echo "<p>Test record ID: {$test_id}</p>";
        
        // Delete the test record
        $conn->query("DELETE FROM properties WHERE id = {$test_id}");
        echo "<p>Test record deleted.</p>";
    } else {
        echo "<p style='color: red;'>❌ Test INSERT failed: " . $conn->error . "</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Test Error: " . $e->getMessage() . "</p>";
}
?>
