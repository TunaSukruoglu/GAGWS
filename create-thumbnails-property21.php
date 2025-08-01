<?php
// Create thumbnails for Property 21
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'includes/cloudflare-images-config.php';

function createThumbnailFromId($cloudflareId, $propertyId, $imageIndex) {
    echo "Creating thumbnail for: $cloudflareId (Property: $propertyId, Index: $imageIndex)\n";
    
    $thumbnailDir = __DIR__ . '/uploads/thumbnails';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    // Create attractive placeholder since Cloudflare delivery has issues
    echo "Creating visual placeholder thumbnail...\n";
    return createVisualPlaceholder($propertyId, $imageIndex, $cloudflareId);
}

function createVisualPlaceholder($propertyId, $imageIndex, $cloudflareId) {
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
        ['bg' => [26, 188, 156], 'accent' => [22, 160, 133]],  // Turquoise
        ['bg' => [241, 196, 15], 'accent' => [243, 156, 18]],  // Yellow
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
    $centerY = 55;
    
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
    $x = (int)((150 - $textWidth) / 2);
    $y = 85;
    
    imagestring($thumbnail, $font, $x, $y, $text, $whiteColor);
    
    // Property info
    $propText = "Property #" . $propertyId;
    $font2 = 2;
    $textWidth2 = imagefontwidth($font2) * strlen($propText);
    $x2 = (int)((150 - $textWidth2) / 2);
    $y2 = 105;
    
    imagestring($thumbnail, $font2, $x2, $y2, $propText, $lightColor);
    
    // Cloudflare ID (short)
    $shortId = substr($cloudflareId, 0, 8) . '...';
    $font3 = 1;
    $textWidth3 = imagefontwidth($font3) * strlen($shortId);
    $x3 = (int)((150 - $textWidth3) / 2);
    $y3 = 120;
    
    imagestring($thumbnail, $font3, $x3, $y3, $shortId, $lightColor);
    
    // Status
    $statusText = "Click to Preview";
    $font4 = 1;
    $textWidth4 = imagefontwidth($font4) * strlen($statusText);
    $x4 = (int)((150 - $textWidth4) / 2);
    $y4 = 135;
    
    imagestring($thumbnail, $font4, $x4, $y4, $statusText, $lightColor);
    
    $placeholderPath = $thumbnailDir . "/thumb_{$propertyId}_{$imageIndex}_{$cloudflareId}.jpg";
    imagejpeg($thumbnail, $placeholderPath, 90);
    imagedestroy($thumbnail);
    
    echo "✅ Visual placeholder created: $placeholderPath\n";
    return true;
}

// Get Property 21's images and create thumbnails
$propertyId = 21;
$query = "SELECT id, images FROM properties WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $images = json_decode($row['images'], true);
    echo "Creating thumbnails for " . count($images) . " images of Property $propertyId\n\n";
    
    foreach ($images as $index => $imageUrl) {
        echo "=== Processing Image " . ($index + 1) . " ===\n";
        
        // Extract Cloudflare ID from URL
        if (preg_match('/\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\//', $imageUrl, $matches)) {
            $cloudflareId = $matches[1];
            createThumbnailFromId($cloudflareId, $propertyId, $index);
        } else {
            echo "❌ Could not extract Cloudflare ID from: $imageUrl\n";
        }
        echo "\n";
    }
    
    echo "🎉 Thumbnail creation completed for Property $propertyId!\n";
    echo "Check: /uploads/thumbnails/ directory\n";
} else {
    echo "❌ Property $propertyId not found or no images\n";
}
?>
