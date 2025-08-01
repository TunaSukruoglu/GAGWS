<?php
// CORRECT DATABASE CONNECTION
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Standardizing Room Data in Correct Database</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to: {$dbname}</p>";
    
    // Get current room_count distribution
    echo "<h3>Current Room Count Distribution</h3>";
    $result = $conn->query("SELECT room_count, COUNT(*) as count FROM properties GROUP BY room_count ORDER BY count DESC");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Current Room Count</th><th>Count</th><th>Conversion</th></tr>";
    
    $conversion_map = [];
    while ($row = $result->fetch_assoc()) {
        $current = $row['room_count'];
        
        // Conversion logic
        if (empty($current) || $current === null || $current === '') {
            $new_room = 1;
            $new_bedroom = 1;
            $new_living = 1;
        } else {
            // Parse room formats like "2+1", "3.5+1", etc.
            if (preg_match('/^(\d+(?:\.\d+)?)\+(\d+)$/', $current, $matches)) {
                $bedrooms = (int)floatval($matches[1]); // Convert 2.5 to 2, 3.5 to 3
                $living_rooms = (int)$matches[2];
                $total_rooms = $bedrooms + $living_rooms;
                
                // Cap at 7
                $new_room = min($total_rooms, 7);
                $new_bedroom = min($bedrooms, 7);
                $new_living = min($living_rooms, 7);
            } else {
                // Already numeric or non-standard format
                if (is_numeric($current)) {
                    $new_room = min((int)$current, 7);
                    $new_bedroom = max(1, min((int)$current - 1, 7));
                    $new_living = 1;
                } else {
                    $new_room = 2;
                    $new_bedroom = 1;
                    $new_living = 1;
                }
            }
        }
        
        $conversion_map[$current] = [
            'room_count' => $new_room,
            'bedrooms' => $new_bedroom,
            'living_room_count' => $new_living
        ];
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($current ?: 'NULL/Empty') . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "<td>Total: {$new_room}, Bedroom: {$new_bedroom}, Living: {$new_living}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Apply conversions
    echo "<h3>Applying Conversions...</h3>";
    $updated_count = 0;
    
    foreach($conversion_map as $old_value => $new_values) {
        if ($old_value === null || $old_value === '') {
            $stmt = $conn->prepare("UPDATE properties SET 
                room_count = ?, 
                bedrooms = ?, 
                living_room_count = ? 
                WHERE room_count IS NULL OR room_count = ''");
            $stmt->bind_param("iii", 
                $new_values['room_count'],
                $new_values['bedrooms'],
                $new_values['living_room_count']
            );
        } else {
            $stmt = $conn->prepare("UPDATE properties SET 
                room_count = ?, 
                bedrooms = ?, 
                living_room_count = ? 
                WHERE room_count = ?");
            $stmt->bind_param("iiis", 
                $new_values['room_count'],
                $new_values['bedrooms'],
                $new_values['living_room_count'],
                $old_value
            );
        }
        
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $updated_count += $affected;
            echo "<p>✅ Updated {$affected} records: '{$old_value}' → Total:{$new_values['room_count']}, Bedroom:{$new_values['bedrooms']}, Living:{$new_values['living_room_count']}</p>";
        }
        $stmt->close();
    }
    
    echo "<p style='color: green; font-weight: bold;'>Total updated records: {$updated_count}</p>";
    
    // Verify results
    echo "<h3>Final Verification</h3>";
    $result = $conn->query("SELECT room_count, bedrooms, living_room_count, COUNT(*) as count 
                           FROM properties 
                           GROUP BY room_count, bedrooms, living_room_count 
                           ORDER BY count DESC LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Total Rooms</th><th>Bedrooms</th><th>Living Rooms</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['room_count']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bedrooms']) . "</td>";
        echo "<td>" . htmlspecialchars($row['living_room_count']) . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    echo "<h3 style='color: green;'>🎉 Data standardization complete!</h3>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
