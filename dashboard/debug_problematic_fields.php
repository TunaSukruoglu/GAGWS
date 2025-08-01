<?php
// Debug problematic fields
include '../db.php';

echo "<h3>Debug Problematic Fields</h3>";

try {
    // Get a sample property to check actual values
    $result = $conn->query("SELECT id, title, room_count, parking, district FROM properties LIMIT 3");
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>room_count</th><th>parking</th><th>district</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "...</td>";
        echo "<td><strong>" . ($row['room_count'] ?? 'NULL') . "</strong></td>";
        echo "<td><strong>" . ($row['parking'] ?? 'NULL') . "</strong></td>";
        echo "<td><strong>" . ($row['district'] ?? 'NULL') . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the exact conditions used in the form
    echo "<h4>Form Condition Tests:</h4>";
    $sample = $conn->query("SELECT * FROM properties LIMIT 1")->fetch_assoc();
    
    if ($sample) {
        $edit_mode = true;
        
        echo "<h5>Sample Property (ID: " . $sample['id'] . "):</h5>";
        echo "<ul>";
        echo "<li>room_count: '" . ($sample['room_count'] ?? 'NULL') . "'</li>";
        echo "<li>parking: '" . ($sample['parking'] ?? 'NULL') . "'</li>"; 
        echo "<li>district: '" . ($sample['district'] ?? 'NULL') . "'</li>";
        echo "</ul>";
        
        echo "<h5>Form Condition Results:</h5>";
        
        // Test room count conditions
        $room_options = ['1+0', '1+1', '2+1', '3+1'];
        echo "<strong>Room Count Tests:</strong><br>";
        foreach ($room_options as $option) {
            $selected = ($edit_mode && isset($sample['room_count']) && $sample['room_count'] == $option);
            $color = $selected ? 'green' : 'red';
            echo "<span style='color: $color;'>$option: " . ($selected ? 'SELECTED' : 'NOT SELECTED') . "</span><br>";
        }
        
        echo "<br><strong>Parking Tests:</strong><br>";
        $parking_options = ['Yok', 'Açık Otopark', 'Kapalı Otopark', 'Var'];
        foreach ($parking_options as $option) {
            $selected = ($edit_mode && isset($sample['parking']) && $sample['parking'] == $option);
            $color = $selected ? 'green' : 'red';
            echo "<span style='color: $color;'>$option: " . ($selected ? 'SELECTED' : 'NOT SELECTED') . "</span><br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
