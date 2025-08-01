<?php
// Get a property ID for testing
include '../db.php';

echo "<h3>Get Property ID for Edit Testing</h3>";

$result = $conn->query("SELECT id, title, room_count FROM properties LIMIT 1");
$property = $result->fetch_assoc();

if ($property) {
    echo "<p>Test Property:</p>";
    echo "<ul>";
    echo "<li>ID: " . $property['id'] . "</li>";
    echo "<li>Title: " . htmlspecialchars($property['title']) . "</li>";
    echo "<li>Room Count: " . $property['room_count'] . "</li>";
    echo "</ul>";
    
    echo "<p><a href='add-property.php?edit=" . $property['id'] . "' target='_blank'>Edit This Property</a></p>";
} else {
    echo "No properties found.";
}

$conn->close();
?>
