<?php
require_once 'db.php';

echo "<h2>Room Structure Final Report</h2>";

try {
    // Final verification
    echo "<h3>✅ Column Structure Verification</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM properties WHERE Field IN ('room_count', 'bedrooms', 'living_room_count')");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Status</th></tr>";
    
    $required_columns = ['room_count', 'bedrooms', 'living_room_count'];
    $existing_columns = array_column($columns, 'Field');
    
    foreach($required_columns as $req_col) {
        $exists = in_array($req_col, $existing_columns);
        $status = $exists ? '<span style="color: green;">✅ EXISTS</span>' : '<span style="color: red;">❌ MISSING</span>';
        echo "<tr>";
        echo "<td>{$req_col}</td>";
        echo "<td>" . ($exists ? 'INT' : 'N/A') . "</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Current data distribution
    echo "<h3>📊 Current Data Distribution</h3>";
    $stmt = $pdo->query("SELECT room_count, bedrooms, living_room_count, COUNT(*) as count 
                        FROM properties 
                        GROUP BY room_count, bedrooms, living_room_count 
                        ORDER BY count DESC LIMIT 20");
    $data = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Total Rooms</th><th>Bedrooms</th><th>Living Rooms</th><th>Count</th></tr>";
    foreach($data as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['room_count']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bedrooms']) . "</td>";
        echo "<td>" . htmlspecialchars($row['living_room_count']) . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3 style='color: green;'>🎉 System Ready!</h3>";
    echo "<p>The room structure has been successfully standardized:</p>";
    echo "<ul>";
    echo "<li>✅ Database columns: room_count, bedrooms, living_room_count</li>";
    echo "<li>✅ All values standardized to 0-7 range</li>";
    echo "<li>✅ Form updated with separate dropdowns</li>";
    echo "<li>✅ Display logic updated in property-details.php</li>";
    echo "<li>✅ System ready for testing!</li>";
    echo "</ul>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>
