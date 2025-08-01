<?php
include __DIR__ . '/../db.php';

echo "<h2>Database Column Names</h2>";

$query = "DESCRIBE properties";
$result = $conn->query($query);

if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . ($row['Key'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Query failed: " . $conn->error;
}
?>
