<?php
include '../db.php';

// Property 23 için thumbnail'lar oluştur
echo "🎯 Property 26 Thumbnail Generator\n";
echo "================================\n";

$property_id = 26;

// Property 23'ün image'larını al
$stmt = $conn->prepare("SELECT cloudflare_images FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $cloudflare_images = $row['cloudflare_images'];
    $images = json_decode($cloudflare_images, true);
    
    if ($images && count($images) > 0) {
        echo "📸 Found " . count($images) . " images for Property 23\n";
        
        // Thumbnail creation function
        function createLocalThumbnail($cloudflareImageId, $propertyId, $imageIndex) {
            $thumbnailDir = '/var/www/vhosts/gokhanaydinli.com/httpdocs/uploads/thumbnails';
            $thumbnailPath = "{$thumbnailDir}/thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg";
            
            if (file_exists($thumbnailPath)) {
                echo "   ✅ Thumbnail already exists: thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg\n";
                return true;
            }
            
            // Create colorful placeholder thumbnail
            $width = 150;
            $height = 150;
            $image = imagecreatetruecolor($width, $height);
            
            // Color palette
            $colors = [
                [52, 152, 219],   // Blue
                [46, 204, 113],   // Green  
                [155, 89, 182],   // Purple
                [230, 126, 34],   // Orange
                [231, 76, 60],    // Red
                [26, 188, 156],   // Turquoise
                [241, 196, 15]    // Yellow
            ];
            
            $colorIndex = $imageIndex % count($colors);
            $bgColor = imagecolorallocate($image, $colors[$colorIndex][0], $colors[$colorIndex][1], $colors[$colorIndex][2]);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            
            imagefill($image, 0, 0, $bgColor);
            
            // Add camera icon (simple)
            $iconColor = imagecolorallocate($image, 255, 255, 255);
            imagefilledrectangle($image, 50, 50, 100, 80, $iconColor);
            imagefilledrectangle($image, 70, 40, 90, 50, $iconColor);
            imagefilledellipse($image, 75, 65, 20, 20, $bgColor);
            
            // Add text
            $text = "IMG " . ($imageIndex + 1);
            $textX = (int)(($width - strlen($text) * 10) / 2);
            $textY = 110;
            imagestring($image, 3, $textX, $textY, $text, $textColor);
            
            // Save thumbnail
            $success = imagejpeg($image, $thumbnailPath, 90);
            imagedestroy($image);
            
            if ($success) {
                chmod($thumbnailPath, 0644);
                echo "   ✅ Created: thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg\n";
                return true;
            } else {
                echo "   ❌ Failed to create: thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg\n";
                return false;
            }
        }
        
        $created = 0;
        foreach ($images as $index => $imageData) {
            // Extract Cloudflare ID - handle both URL and direct ID formats
            $cloudflareId = '';
            if (is_string($imageData)) {
                if (preg_match('/\/([a-f0-9\-]{36})\//', $imageData, $matches)) {
                    // URL format
                    $cloudflareId = $matches[1];
                } else if (preg_match('/^[a-f0-9\-]{36}$/', $imageData)) {
                    // Direct ID format
                    $cloudflareId = $imageData;
                }
            }
            
            if ($cloudflareId) {
                echo "📸 Processing image " . ($index + 1) . ": {$cloudflareId}\n";
                
                if (createLocalThumbnail($cloudflareId, $property_id, $index)) {
                    $created++;
                }
            } else {
                echo "   ⚠️ Could not extract Cloudflare ID from: " . print_r($imageData, true) . "\n";
            }
        }
        
        echo "\n🎯 Summary:\n";
        echo "✅ Created {$created} thumbnails for Property 23\n";
        echo "📁 Location: /uploads/thumbnails/\n";
        echo "🔧 Ready for edit mode display!\n";
        
    } else {
        echo "❌ No images found for Property 23\n";
    }
} else {
    echo "❌ Property 23 not found\n";
}
?>
