<?php
include 'db.php';

$result = $conn->query('SELECT id, title, parking, images, main_image, dues, elevator, furnished, heating FROM properties WHERE id = 1');
$row = $result->fetch_assoc();

echo 'Property ID 1 - CURRENT STATUS:' . PHP_EOL;
echo 'Title: ' . $row['title'] . PHP_EOL;
echo 'Parking: ' . ($row['parking'] ?: 'EMPTY') . PHP_EOL;
echo 'Dues (Aidat): ' . ($row['dues'] ?: 'EMPTY') . PHP_EOL;
echo 'Elevator (Asansör): ' . ($row['elevator'] ?: 'EMPTY') . PHP_EOL;
echo 'Furnished: ' . ($row['furnished'] ?: 'EMPTY') . PHP_EOL;
echo 'Heating: ' . ($row['heating'] ?: 'EMPTY') . PHP_EOL;
$images = json_decode($row['images'], true);
echo 'Image count: ' . (is_array($images) ? count($images) : 0) . PHP_EOL;
echo 'Main Image: ' . ($row['main_image'] ?: 'EMPTY') . PHP_EOL;
?>
