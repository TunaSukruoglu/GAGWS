<?php
include 'db.php';

echo "Mevcut Properties:\n\n";

$result = $conn->query("SELECT id, title, status FROM properties ORDER BY id DESC LIMIT 5");

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - " . $row['title'] . " (" . $row['status'] . ")\n";
    }
} else {
    echo "Hiç property bulunamadı.\n";
}

echo "\nEdit link örneği: add-property.php?edit=1\n";
?>
