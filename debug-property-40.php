<?php
include 'db.php';

echo "=== PROPERTY ID 40 IMAGE DEBUG ===\n";

$stmt = $conn->prepare('SELECT id, title, images FROM properties WHERE id = 40');
$stmt->execute();
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if ($property) {
    echo "Property ID: {$property['id']}\n";
    echo "Title: {$property['title']}\n";
    echo "Raw images data: {$property['images']}\n";
    echo "\n=== JSON DECODE TEST ===\n";
    
    $decoded = json_decode($property['images'], true);
    if (is_array($decoded)) {
        echo "JSON decode successful:\n";
        foreach ($decoded as $i => $img) {
            echo "Image $i: $img\n";
        }
    } else {
        echo "JSON decode failed, trying comma split:\n";
        $split = explode(',', $property['images']);
        foreach ($split as $i => $img) {
            echo "Image $i: " . trim($img) . "\n";
        }
    }
    
    echo "\n=== FILE EXISTENCE CHECK ===\n";
    
    $images = [];
    if ($decoded && is_array($decoded)) {
        $images = $decoded;
    } else {
        $images = explode(',', $property['images']);
    }
    
    foreach ($images as $i => $image) {
        $image = trim($image);
        if (empty($image)) continue;
        
        $filename = basename($image);
        echo "Checking image $i: $filename\n";
        
        $paths_to_check = [
            "dashboard/uploads/properties/$filename",
            "uploads/properties/$filename", 
            "images/$filename",
            "dashboard/images/$filename"
        ];
        
        foreach ($paths_to_check as $path) {
            if (file_exists($path)) {
                echo "  ✓ FOUND: $path\n";
                break;
            } else {
                echo "  ✗ NOT FOUND: $path\n";
            }
        }
    }
} else {
    echo "Property with ID 40 not found!\n";
}

echo "\n=== CHECK IF show-image.php EXISTS ===\n";
if (file_exists('show-image.php')) {
    echo "✓ show-image.php exists\n";
} else {
    echo "✗ show-image.php NOT found\n";
}
?>
