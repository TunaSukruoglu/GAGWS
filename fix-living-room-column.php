<?php
require_once 'db.php';

echo "<h2>Database Column Check & Fix</h2>";

try {
    // Check current table structure
    echo "<h3>Current Properties Table Structure</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM properties");
    $columns = $stmt->fetchAll();
    
    $living_room_exists = false;
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach($columns as $col) {
        if ($col['Field'] === 'living_room_count') {
            $living_room_exists = true;
        }
        $highlight = in_array($col['Field'], ['room_count', 'bedrooms', 'living_room_count']) ? ' style="background-color: yellow;"' : '';
        echo "<tr{$highlight}>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if (!$living_room_exists) {
        echo "<h3 style='color: red;'>❌ living_room_count column is MISSING!</h3>";
        echo "<p>Adding living_room_count column now...</p>";
        
        // Add the missing column
        $pdo->exec("ALTER TABLE properties ADD COLUMN living_room_count INT DEFAULT 1 AFTER bedrooms");
        echo "<p style='color: green;'>✅ living_room_count column added successfully!</p>";
        
        // Verify it was added
        $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'living_room_count'");
        $verify = $stmt->fetch();
        if ($verify) {
            echo "<p style='color: green;'>✅ Verification: Column exists now</p>";
        } else {
            echo "<p style='color: red;'>❌ Error: Column still not found</p>";
        }
    } else {
        echo "<h3 style='color: green;'>✅ living_room_count column already exists!</h3>";
    }
    
    // Show final table structure
    echo "<h3>Final Table Structure</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM properties");
    $final_columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach($final_columns as $col) {
        $highlight = in_array($col['Field'], ['room_count', 'bedrooms', 'living_room_count']) ? ' style="background-color: yellow;"' : '';
        echo "<tr{$highlight}>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>
