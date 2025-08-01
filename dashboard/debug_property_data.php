<?php
// Debug existing property data
include '../db.php';

echo "<h3>Existing Property Data Debug</h3>";

try {
    // Get a sample property to see the actual data structure
    $result = $conn->query("SELECT * FROM properties LIMIT 1");
    
    if ($row = $result->fetch_assoc()) {
        echo "<h4>Sample Property Data (ID: " . $row['id'] . "):</h4>";
        echo "<table border='1'><tr><th>Column</th><th>Value</th></tr>";
        
        foreach ($row as $key => $value) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h4>Specific Field Mapping Check:</h4>";
        echo "<ul>";
        echo "<li><strong>room_count:</strong> " . ($row['room_count'] ?? 'NULL') . "</li>";
        echo "<li><strong>building_age/year_built:</strong> " . ($row['year_built'] ?? 'NULL') . "</li>";
        echo "<li><strong>parking:</strong> " . ($row['parking'] ?? 'NULL') . "</li>";
        echo "<li><strong>dues:</strong> " . ($row['dues'] ?? 'NULL') . "</li>";
        echo "<li><strong>city:</strong> " . ($row['city'] ?? 'NULL') . "</li>";
        echo "<li><strong>district:</strong> " . ($row['district'] ?? 'NULL') . "</li>";
        echo "<li><strong>location_type:</strong> " . ($row['location_type'] ?? 'NULL') . "</li>";
        echo "</ul>";
    } else {
        echo "<p>No properties found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
