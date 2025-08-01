<?php
include 'db.php';

echo "=== VERİTABANI SÜTUNLARI ===\n";
$result = $conn->query('SHOW COLUMNS FROM properties LIKE "%features%"');
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

echo "\n=== PROPERTIES TABLOSU ===\n";
$result = $conn->query('SELECT id, title, interior_features, exterior_features, neighborhood_features, transportation_features, view_features, housing_type_features FROM properties ORDER BY id DESC LIMIT 3');
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " - " . $row['title'] . "\n";
    echo "Interior: " . ($row['interior_features'] ?? 'NULL') . "\n";
    echo "Exterior: " . ($row['exterior_features'] ?? 'NULL') . "\n";
    echo "Neighborhood: " . ($row['neighborhood_features'] ?? 'NULL') . "\n";
    echo "Transportation: " . ($row['transportation_features'] ?? 'NULL') . "\n";
    echo "View: " . ($row['view_features'] ?? 'NULL') . "\n";
    echo "Housing Type: " . ($row['housing_type_features'] ?? 'NULL') . "\n";
    echo "---\n";
}
?>
