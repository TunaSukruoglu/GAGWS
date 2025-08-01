<?php
// Create better visual placeholders for thumbnails
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

function createBetterPlaceholder($propertyId, $imageIndex, $cloudflareId) {
    $thumbnailDir = __DIR__ . '/uploads/thumbnails';
    
    // Create a more attractive placeholder
    $thumbnail = imagecreatetruecolor(150, 150);
    
    // Color palette based on image index
    $colors = [
        ['bg' => [52, 152, 219], 'accent' => [41, 128, 185]],   // Blue
        ['bg' => [46, 204, 113], 'accent' => [39, 174, 96]],   // Green
        ['bg' => [155, 89, 182], 'accent' => [142, 68, 173]],  // Purple
        ['bg' => [230, 126, 34], 'accent' => [211, 84, 0]],    // Orange
        ['bg' => [231, 76, 60], 'accent' => [192, 57, 43]],    // Red
    ];
    
    $colorIndex = $imageIndex % count($colors);
    $bgColor = imagecolorallocate($thumbnail, ...$colors[$colorIndex]['bg']);
    $accentColor = imagecolorallocate($thumbnail, ...$colors[$colorIndex]['accent']);
    $whiteColor = imagecolorallocate($thumbnail, 255, 255, 255);
    $lightColor = imagecolorallocate($thumbnail, 255, 255, 255);
    
    // Fill background
    imagefill($thumbnail, 0, 0, $bgColor);
    
    // Create border gradient effect
    for ($i = 0; $i < 3; $i++) {
        imagerectangle($thumbnail, $i, $i, 149-$i, 149-$i, $accentColor);
    }
    
    // Add camera icon (simplified)
    $centerX = 75;
    $centerY = 60;
    
    // Camera body
    imagefilledrectangle($thumbnail, $centerX-25, $centerY-15, $centerX+25, $centerY+15, $whiteColor);
    imagefilledrectangle($thumbnail, $centerX-23, $centerY-13, $centerX+23, $centerY+13, $accentColor);
    
    // Camera lens
    imagefilledellipse($thumbnail, $centerX, $centerY, 20, 20, $whiteColor);
    imagefilledellipse($thumbnail, $centerX, $centerY, 16, 16, $accentColor);
    imagefilledellipse($thumbnail, $centerX, $centerY, 8, 8, $whiteColor);
    
    // Image number
    $text = "IMG " . ($imageIndex + 1);
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $x = (150 - $textWidth) / 2;
    $y = 90;
    
    imagestring($thumbnail, $font, $x, $y, $text, $whiteColor);
    
    // Property info
    $propText = "Property #" . $propertyId;
    $font2 = 2;
    $textWidth2 = imagefontwidth($font2) * strlen($propText);
    $x2 = (150 - $textWidth2) / 2;
    $y2 = 110;
    
    imagestring($thumbnail, $font2, $x2, $y2, $propText, $lightColor);
    
    // CF Status
    $statusText = "CF: Delivery Issue";
    $font3 = 1;
    $textWidth3 = imagefontwidth($font3) * strlen($statusText);
    $x3 = (150 - $textWidth3) / 2;
    $y3 = 125;
    
    imagestring($thumbnail, $font3, $x3, $y3, $statusText, $lightColor);
    
    $placeholderPath = $thumbnailDir . "/thumb_{$propertyId}_{$imageIndex}_{$cloudflareId}.jpg";
    imagejpeg($thumbnail, $placeholderPath, 90);
    imagedestroy($thumbnail);
    
    echo "✅ Better placeholder created: $placeholderPath\n";
    return true;
}

// Get Property 20's images and recreate better placeholders
$query = "SELECT id, images FROM properties WHERE id = 20";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $images = json_decode($row['images'], true);
    echo "Creating better placeholders for " . count($images) . " images\n\n";
    
    foreach ($images as $index => $imageUrl) {
        echo "Creating placeholder for Image " . ($index + 1) . "\n";
        
        // Extract Cloudflare ID from URL
        if (preg_match('/\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\//', $imageUrl, $matches)) {
            $cloudflareId = $matches[1];
            createBetterPlaceholder(20, $index, $cloudflareId);
        }
    }
    
    echo "\n🎨 Better visual placeholders created!\n";
} else {
    echo "❌ Property 20 not found\n";
}
?>
