<?php
include 'db.php';

echo "=== FEATURED PROPERTIES CHECK ===\n";
$stmt = $conn->prepare('SELECT id, title, featured, images FROM properties WHERE featured = 1 LIMIT 5');
$stmt->execute();
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($featured) . " featured properties:\n";
foreach($featured as $prop) {
    echo "ID: {$prop['id']}, Title: {$prop['title']}, Images: {$prop['images']}\n";
}

echo "\n=== ALL PROPERTIES (for comparison) ===\n";
$stmt2 = $conn->prepare('SELECT id, title, featured, images FROM properties ORDER BY id DESC LIMIT 5');
$stmt2->execute();
$all = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach($all as $prop) {
    echo "ID: {$prop['id']}, Title: {$prop['title']}, Featured: {$prop['featured']}, Images: {$prop['images']}\n";
}

echo "\n=== UPDATE PROPERTY 38 TO FEATURED ===\n";
$update = $conn->prepare('UPDATE properties SET featured = 1 WHERE id = 38');
if ($update->execute()) {
    echo "Property 38 marked as featured successfully!\n";
} else {
    echo "Failed to update property 38\n";
}

echo "\n=== VERIFY UPDATE ===\n";
$verify = $conn->prepare('SELECT id, title, featured FROM properties WHERE id = 38');
$verify->execute();
$result = $verify->fetch(PDO::FETCH_ASSOC);
if ($result) {
    echo "Property 38 - Featured: {$result['featured']}\n";
}
?>
