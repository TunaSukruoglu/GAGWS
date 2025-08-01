<?php
include 'db.php';

// Son eklenen property'leri göster
$stmt = $conn->prepare("SELECT id, title, created_at FROM properties ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Son Property'ler:</h2>";
while ($row = $result->fetch_assoc()) {
    echo "<p><a href='property-details.php?id=" . $row['id'] . "' target='_blank'>ID: " . $row['id'] . " - " . htmlspecialchars($row['title']) . "</a></p>";
}
?>
