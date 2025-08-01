<?php
require_once 'db.php';

echo "<h2>Room Count Standardization</h2>";

try {
    // First, analyze current data
    echo "<h3>Current Room Count Analysis</h3>";
    $stmt = $pdo->query("SELECT room_count, COUNT(*) as count FROM properties GROUP BY room_count ORDER BY count DESC");
    $current_data = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Current Room Count</th><th>Count</th><th>Proposed Conversion</th></tr>";
    
    $conversion_map = [];
    foreach($current_data as $row) {
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
                // Fallback for non-standard formats
                $new_room = 2;
                $new_bedroom = 1;
                $new_living = 1;
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
        echo "<td>Room: {$new_room}, Bedroom: {$new_bedroom}, Living: {$new_living}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Apply conversions
    echo "<h3>Applying Conversions...</h3>";
    $updated_count = 0;
    
    foreach($conversion_map as $old_value => $new_values) {
        if ($old_value === null || $old_value === '') {
            $stmt = $pdo->prepare("UPDATE properties SET 
                room_count = ?, 
                bedrooms = ?, 
                living_room_count = ? 
                WHERE room_count IS NULL OR room_count = ''");
        } else {
            $stmt = $pdo->prepare("UPDATE properties SET 
                room_count = ?, 
                bedrooms = ?, 
                living_room_count = ? 
                WHERE room_count = ?");
        }
        
        if ($old_value === null || $old_value === '') {
            $result = $stmt->execute([
                $new_values['room_count'],
                $new_values['bedrooms'],
                $new_values['living_room_count']
            ]);
        } else {
            $result = $stmt->execute([
                $new_values['room_count'],
                $new_values['bedrooms'],
                $new_values['living_room_count'],
                $old_value
            ]);
        }
        
        if ($result) {
            $affected = $stmt->rowCount();
            $updated_count += $affected;
            echo "<p>✓ Updated {$affected} records from '{$old_value}' to Room:{$new_values['room_count']}, Bedroom:{$new_values['bedrooms']}, Living:{$new_values['living_room_count']}</p>";
        }
    }
    
    echo "<p style='color: green; font-weight: bold;'>Total updated records: {$updated_count}</p>";
    
    // Verify results
    echo "<h3>Verification - New Distribution</h3>";
    $stmt = $pdo->query("SELECT room_count, bedrooms, living_room_count, COUNT(*) as count 
                        FROM properties 
                        GROUP BY room_count, bedrooms, living_room_count 
                        ORDER BY count DESC");
    $new_data = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Room Count</th><th>Bedrooms</th><th>Living Rooms</th><th>Count</th></tr>";
    foreach($new_data as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['room_count']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bedrooms']) . "</td>";
        echo "<td>" . htmlspecialchars($row['living_room_count']) . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch(PDOException $e) {
    echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
}
?>
