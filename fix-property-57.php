<?php
// CHECK SPECIFIC PROPERTY 57
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Property 57 Investigation</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check property 57 specifically
    echo "<h3>Property 57 Details:</h3>";
    $result = $conn->query("SELECT id, title, parking FROM properties WHERE id = 57");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<p><strong>ID:</strong> " . $row['id'] . "</p>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($row['title']) . "</p>";
        echo "<p><strong>Parking Value:</strong> '" . ($row['parking'] ?? 'NULL') . "'</p>";
        echo "<p><strong>Is NULL:</strong> " . (is_null($row['parking']) ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Is Empty:</strong> " . (empty($row['parking']) ? 'YES' : 'NO') . "</p>";
        echo "<p><strong>Length:</strong> " . strlen($row['parking'] ?? '') . "</p>";
        
        // Update this specific property
        echo "<h3>Updating Property 57:</h3>";
        $update_result = $conn->query("UPDATE properties SET parking = 'Otopark Yok' WHERE id = 57");
        
        if ($update_result) {
            echo "<p style='color: green;'>✅ Successfully updated property 57 parking to 'Otopark Yok'</p>";
            
            // Verify the update
            $verify_result = $conn->query("SELECT parking FROM properties WHERE id = 57");
            $updated_row = $verify_result->fetch_assoc();
            echo "<p><strong>New Parking Value:</strong> '" . $updated_row['parking'] . "'</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update property 57: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>Property 57 not found!</p>";
    }
    
    // Check all parking values again
    echo "<h3>Final Parking Distribution:</h3>";
    $result = $conn->query("SELECT 
        parking, 
        COUNT(*) as count,
        CASE 
            WHEN parking IS NULL THEN 'NULL'
            WHEN parking = '' THEN 'EMPTY'
            WHEN parking = 'NULL' THEN 'STRING_NULL'
            ELSE 'VALID'
        END as status
        FROM properties 
        GROUP BY parking 
        ORDER BY count DESC");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Parking Value</th><th>Count</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $parking_display = $row['parking'] ?? 'NULL';
        $status_color = $row['status'] === 'VALID' ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($parking_display) . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "<td style='color: {$status_color};'>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test property details link
    echo "<h3>Test Links:</h3>";
    echo "<p><a href='property-details.php?id=57' target='_blank'>View Property 57 Details</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { font-family: Arial, sans-serif; margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
