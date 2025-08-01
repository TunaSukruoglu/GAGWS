<?php
// CORRECT DATABASE CONNECTION - matching add-property.php
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Fixing living_room_count in Correct Database</h2>";

try {
    // Connect to the same database as add-property.php
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Connected to: {$dbname} database</p>";
    
    // Check if living_room_count exists
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'living_room_count'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ living_room_count already exists!</p>";
    } else {
        echo "<p style='color: red;'>❌ living_room_count does NOT exist in {$dbname}</p>";
        echo "<p>Adding column now...</p>";
        
        // Add the column
        if ($conn->query("ALTER TABLE properties ADD COLUMN living_room_count INT DEFAULT 1 AFTER bedrooms")) {
            echo "<p style='color: green;'>✅ living_room_count column added successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to add column: " . $conn->error . "</p>";
        }
    }
    
    // Show all columns to verify
    echo "<h3>Current Properties Table Structure</h3>";
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
    
    // Test INSERT
    echo "<h3>Testing INSERT Query</h3>";
    $test_query = "INSERT INTO properties SET 
        user_id = 1,
        title = 'TEST LIVING ROOM',
        description = 'Test',
        price = 100000,
        type = 'sale',
        category = 'apartment',
        subcategory = 'daire',
        listing_type = 'Satılık',
        room_count = 3,
        bedrooms = 2,
        living_room_count = 1,
        bathrooms = 1,
        status = 'active',
        created_at = NOW()";
    
    if ($conn->query($test_query)) {
        $test_id = $conn->insert_id;
        echo "<p style='color: green;'>✅ Test INSERT with living_room_count successful! ID: {$test_id}</p>";
        
        // Clean up test record
        $conn->query("DELETE FROM properties WHERE id = {$test_id}");
        echo "<p>Test record cleaned up.</p>";
    } else {
        echo "<p style='color: red;'>❌ Test INSERT failed: " . $conn->error . "</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
