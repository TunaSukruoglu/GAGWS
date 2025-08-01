<?php
// Create thumbnails for existing Cloudflare images
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
    
    // Try different URL formats
    $urlFormats = [
        "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $cloudflareId . "/public",
        "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $cloudflareId,
        "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $cloudflareId . "/original"
    ];
    
    foreach ($urlFormats as $originalUrl) {
        echo "Trying URL: $originalUrl\n";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; ThumbnailGenerator/1.0)'
            ]
        ]);
        
        $imageData = @file_get_contents($originalUrl, false, $context);
        
        if ($imageData !== false && strlen($imageData) > 100) {
            echo "Successfully downloaded image data (" . strlen($imageData) . " bytes)\n";
            
            $originalImage = @imagecreatefromstring($imageData);
            if ($originalImage !== false) {
                // Get original dimensions
                $originalWidth = imagesx($originalImage);
                $originalHeight = imagesy($originalImage);
                echo "Original dimensions: {$originalWidth}x{$originalHeight}\n";
                
                // Calculate thumbnail dimensions (150x150 max, maintain aspect ratio)
                $thumbnailSize = 150;
                if ($originalWidth > $originalHeight) {
                    $newWidth = $thumbnailSize;
                    $newHeight = intval(($originalHeight * $thumbnailSize) / $originalWidth);
                } else {
                    $newHeight = $thumbnailSize;
                    $newWidth = intval(($originalWidth * $thumbnailSize) / $originalHeight);
                }
                
                echo "Thumbnail dimensions: {$newWidth}x{$newHeight}\n";
                
                // Create thumbnail
                $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($thumbnail, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                
                // Save thumbnail
                $thumbnailPath = $thumbnailDir . "/thumb_{$propertyId}_{$imageIndex}_{$cloudflareId}.jpg";
                $success = imagejpeg($thumbnail, $thumbnailPath, 85);
                
                // Cleanup
                imagedestroy($originalImage);
                imagedestroy($thumbnail);
                
                if ($success) {
                    echo "✅ Thumbnail created: $thumbnailPath\n";
                    return true;
                } else {
                    echo "❌ Failed to save thumbnail\n";
                }
            } else {
                echo "❌ Failed to create image from data\n";
            }
        } else {
            echo "❌ Failed to download or empty data\n";
        }
    }
    
    // Create placeholder if all fails
    echo "Creating placeholder thumbnail...\n";
    return createPlaceholder($propertyId, $imageIndex, $cloudflareId);
}

function createPlaceholder($propertyId, $imageIndex, $cloudflareId) {
    $thumbnailDir = __DIR__ . '/uploads/thumbnails';
    
    $thumbnail = imagecreatetruecolor(150, 150);
    $bgColor = imagecolorallocate($thumbnail, 240, 240, 240);
    $borderColor = imagecolorallocate($thumbnail, 200, 200, 200);
    $textColor = imagecolorallocate($thumbnail, 100, 100, 100);
    
    imagefill($thumbnail, 0, 0, $bgColor);
    imagerectangle($thumbnail, 0, 0, 149, 149, $borderColor);
    
    // Add text
    $text = "IMG " . ($imageIndex + 1);
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = (150 - $textWidth) / 2;
    $y = (150 - $textHeight) / 2 - 10;
    
    imagestring($thumbnail, $font, $x, $y, $text, $textColor);
    
    // Add CF ID (shortened)
    $shortId = substr($cloudflareId, 0, 8) . '...';
    $font2 = 2;
    $textWidth2 = imagefontwidth($font2) * strlen($shortId);
    $x2 = (150 - $textWidth2) / 2;
    $y2 = $y + 20;
    
    imagestring($thumbnail, $font2, $x2, $y2, $shortId, $textColor);
    
    $placeholderPath = $thumbnailDir . "/thumb_{$propertyId}_{$imageIndex}_{$cloudflareId}.jpg";
    imagejpeg($thumbnail, $placeholderPath, 85);
    imagedestroy($thumbnail);
    
    echo "✅ Placeholder created: $placeholderPath\n";
    return true;
}

// Get Property 20's images
$query = "SELECT id, images FROM properties WHERE id = 20";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $images = json_decode($row['images'], true);
    echo "Found " . count($images) . " images for Property 20\n\n";
    
    foreach ($images as $index => $imageUrl) {
        echo "=== Processing Image " . ($index + 1) . " ===\n";
        
        // Extract Cloudflare ID from URL
        if (preg_match('/\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\//', $imageUrl, $matches)) {
            $cloudflareId = $matches[1];
            createThumbnailFromId($cloudflareId, 20, $index);
        } else {
            echo "❌ Could not extract Cloudflare ID from: $imageUrl\n";
        }
        echo "\n";
    }
    
    echo "🎉 Thumbnail creation completed!\n";
    echo "Check: /uploads/thumbnails/ directory\n";
} else {
    echo "❌ Property 20 not found or no images\n";
}
?>
