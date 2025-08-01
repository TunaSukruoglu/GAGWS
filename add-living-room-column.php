<?php
require_once 'db.php';

echo "<h2>Adding Living Room Count Column</h2>";

try {
    // Check if living_room_count column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'living_room_count'");
    $living_room_exists = $stmt->fetch();
    
    if (!$living_room_exists) {
        // Add living_room_count column
        $pdo->exec("ALTER TABLE properties ADD COLUMN living_room_count INT DEFAULT 1 AFTER bedrooms");
        echo "<p style='color: green;'>✓ living_room_count column added successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ living_room_count column already exists</p>";
    }
    
    // Show current columns
    echo "<h3>Current Properties Table Structure</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM properties");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach($columns as $col) {
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
