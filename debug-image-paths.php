<?php
include 'db.php';

echo "=== RESIM YOLU KONTROL SİSTEMİ ===\n\n";

// Son 5 property'nin resimlerini kontrol et
$stmt = $conn->prepare('SELECT id, title, images FROM properties ORDER BY id DESC LIMIT 5');
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($properties as $property) {
    echo "=== PROPERTY ID: {$property['id']} ===\n";
    echo "Title: {$property['title']}\n";
    echo "Raw images: {$property['images']}\n";
    
    $images = [];
    if (!empty($property['images'])) {
        $decoded = json_decode($property['images'], true);
        if (is_array($decoded)) {
            $images = $decoded;
        } else {
            $images = explode(',', $property['images']);
        }
    }
    
    if (empty($images)) {
        echo "No images found\n\n";
        continue;
    }
    
    foreach($images as $i => $image) {
        $image = trim($image);
        if (empty($image)) continue;
        
        echo "Image $i: $image\n";
        
        $filename = basename($image);
        $paths_to_check = [
            "dashboard/uploads/properties/$filename",
            "uploads/properties/$filename", 
            "images/$filename"
        ];
        
        $found = false;
        foreach ($paths_to_check as $path) {
            if (file_exists($path)) {
                echo "  ✓ FOUND: $path\n";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "  ✗ NOT FOUND anywhere!\n";
            echo "  Expected filename: $filename\n";
        }
    }
    echo "\n";
}

echo "\n=== DASHBOARD UPLOAD KLASÖRÜ İÇERİĞİ ===\n";
$dashboard_dir = 'dashboard/uploads/properties/';
if (is_dir($dashboard_dir)) {
    $files = scandir($dashboard_dir);
    $files = array_filter($files, function($file) { return $file !== '.' && $file !== '..'; });
    
    echo "Files in dashboard/uploads/properties/:\n";
    foreach($files as $file) {
        echo "  - $file\n";
    }
} else {
    echo "dashboard/uploads/properties/ directory does not exist!\n";
}
?>
