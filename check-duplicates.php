<?php
include 'db.php';
$result = $conn->query('SELECT COUNT(*) as count FROM properties WHERE id = 1');
$row = $result->fetch_assoc();
echo 'ID=1 olan satır sayısı: ' . $row['count'] . PHP_EOL;

$result = $conn->query('SELECT id, title, parking, LENGTH(images) as img_len FROM properties WHERE id = 1');
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Title: {$row['title']}, Parking: {$row['parking']}, Image Length: {$row['img_len']}" . PHP_EOL;
}
?>
