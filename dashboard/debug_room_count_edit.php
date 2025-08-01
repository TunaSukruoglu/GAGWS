<?php
// Test room count selection in edit mode
include '../db.php';

echo "<h3>Room Count Edit Mode Debug</h3>";

// Get a property with room_count
$result = $conn->query("SELECT * FROM properties WHERE room_count IS NOT NULL AND room_count != '' LIMIT 1");
$existing_property = $result->fetch_assoc();

if ($existing_property) {
    $edit_mode = true;
    
    echo "<h4>Property Details:</h4>";
    echo "ID: " . $existing_property['id'] . "<br>";
    echo "Title: " . htmlspecialchars($existing_property['title']) . "<br>";
    echo "Room Count: <strong>" . $existing_property['room_count'] . "</strong><br><br>";
    
    echo "<h4>Form Option Tests:</h4>";
    
    $room_options = ['1+0', '1+1', '2+1', '3+1', '4+1', '5+1'];
    
    foreach ($room_options as $option) {
        $is_selected = ($edit_mode && isset($existing_property['room_count']) && $existing_property['room_count'] == $option) ? 'SELECTED' : 'NOT SELECTED';
        $color = ($is_selected == 'SELECTED') ? 'green' : 'red';
        echo "<span style='color: $color;'><strong>$option:</strong> $is_selected</span><br>";
    }
    
    echo "<br><h4>Debug Values:</h4>";
    echo "edit_mode: " . ($edit_mode ? 'true' : 'false') . "<br>";
    echo "isset(existing_property['room_count']): " . (isset($existing_property['room_count']) ? 'true' : 'false') . "<br>";
    echo "existing_property['room_count']: '" . $existing_property['room_count'] . "'<br>";
    echo "String comparison ('2+1' == existing_room): " . (('2+1' == $existing_property['room_count']) ? 'true' : 'false') . "<br>";
    
} else {
    echo "No properties with room_count found.";
}

$conn->close();
?>
