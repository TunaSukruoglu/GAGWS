<?php
require_once '../db.php';

// Force get property 20 data
$property_query = "SELECT * FROM properties WHERE id = 20";
$stmt = $conn->prepare($property_query);
$stmt->execute();
$existing_property = $stmt->get_result()->fetch_assoc();

if ($existing_property && !empty($existing_property['images'])) {
    $all_existing_images = json_decode($existing_property['images'], true);
    if (is_array($all_existing_images)) {
        echo "Total images found: " . count($all_existing_images) . "\n";
        
        // Filter Cloudflare images
        $existing_images = array_filter($all_existing_images, function($image) {
            return strpos($image, 'https://imagedelivery.net/') === 0 || 
                   strpos($image, 'cloudflare') !== false ||
                   preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $image);
        });
        
        echo "Cloudflare images: " . count($existing_images) . "\n";
        
        foreach ($existing_images as $index => $image) {
            echo "Image " . ($index + 1) . ": " . $image . "\n";
        }
    }
} else {
    echo "No images found for property 20\n";
}
?>
