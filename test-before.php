<?php
include 'db.php';
$result = $conn->query('SELECT id, title, parking, images, main_image FROM properties WHERE id = 1');
$row = $result->fetch_assoc();
echo 'Property ID 1 - BEFORE UPDATE:' . PHP_EOL;
echo 'Title: ' . $row['title'] . PHP_EOL;
echo 'Parking: ' . $row['parking'] . PHP_EOL;
$images = json_decode($row['images'], true);
echo 'Image count: ' . (is_array($images) ? count($images) : 0) . PHP_EOL;
echo 'Main Image: ' . $row['main_image'] . PHP_EOL;
echo 'First few images: ' . PHP_EOL;
if (is_array($images)) {
    for ($i = 0; $i < min(3, count($images)); $i++) {
        echo "  $i: " . $images[$i] . PHP_EOL;
    }
}
?>
