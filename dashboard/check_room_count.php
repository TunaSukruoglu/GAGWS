<?php
// Check room_count values in database
include '../db.php';

echo "<h3>Room Count Values in Database</h3>";

try {
    $result = $conn->query("SELECT id, title, room_count FROM properties ORDER BY id DESC LIMIT 5");
    
    echo "<table border='1'><tr><th>ID</th><th>Title</th><th>room_count</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 50)) . "...</td>";
        echo "<td><strong>" . ($row['room_count'] ?? 'NULL') . "</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
