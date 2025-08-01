<?php
require_once 'db.php';

echo "<h2>Room Structure Analysis</h2>";
echo "<h3>Current Room Count Distribution</h3>";

try {
    // Get all unique room_count values
    $stmt = $pdo->query("SELECT room_count, COUNT(*) as count FROM properties GROUP BY room_count ORDER BY count DESC");
    $room_counts = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Room Count</th><th>Count</th></tr>";
    foreach($room_counts as $row) {
        echo "<tr><td>" . htmlspecialchars($row['room_count'] ?? 'NULL') . "</td><td>" . $row['count'] . "</td></tr>";
    }
    echo "</table>";

    // Check if bedrooms column exists
    echo "<h3>Current Bedrooms Distribution</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'bedrooms'");
    $bedrooms_exists = $stmt->fetch();
    
    if ($bedrooms_exists) {
        $stmt = $pdo->query("SELECT bedrooms, COUNT(*) as count FROM properties GROUP BY bedrooms ORDER BY count DESC");
        $bedrooms = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Bedrooms</th><th>Count</th></tr>";
        foreach($bedrooms as $row) {
            echo "<tr><td>" . htmlspecialchars($row['bedrooms'] ?? 'NULL') . "</td><td>" . $row['count'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Bedrooms column does not exist.</p>";
    }
    
    // Check if we need to add new columns
    $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'living_room_count'");
    $living_room_exists = $stmt->fetch();
    
    echo "<h3>Database Structure Recommendations</h3>";
    echo "<ul>";
    echo "<li>Current room_count: Mixed format (1+1, 2.5+1, etc.) - needs standardization</li>";
    if ($bedrooms_exists) {
        echo "<li>Bedrooms column: EXISTS</li>";
    } else {
        echo "<li>Bedrooms column: MISSING - needs to be added</li>";
    }
    
    if ($living_room_exists) {
        echo "<li>Living room column: EXISTS</li>";
    } else {
        echo "<li>Living room column: MISSING - needs to be added</li>";
    }
    echo "</ul>";

    echo "<h3>Proposed New Structure</h3>";
    echo "<ul>";
    echo "<li><strong>room_count</strong>: Total rooms (0-7)</li>";
    echo "<li><strong>bedrooms</strong>: Bedroom count (0-7)</li>";
    echo "<li><strong>living_room_count</strong>: Living room count (0-7)</li>";
    echo "</ul>";

} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
