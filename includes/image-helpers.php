<?php
/**
 * Image Helper Functions
 * includes/image-helpers.php
 * 
 * Cloudflare Images ile eski sistem arasında uyumluluk sağlar
 */

/**
 * Get all Cloudflare images for a property
 */
function getPropertyCloudflareImages($propertyId, $size = 'medium') {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT cloudflare_image_id, original_filename, image_urls, is_main, metadata, upload_date 
        FROM property_cloudflare_images 
        WHERE property_id = ? 
        ORDER BY is_main DESC, upload_date ASC
    ");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    
    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // URL'leri parse et ve istenen boyutu döndür
    foreach ($images as &$image) {
        $urls = json_decode($image['image_urls'], true);
        $image['url'] = $urls[$size] ?? $urls['medium'] ?? '';
        $image['all_urls'] = $urls;
        $image['metadata_parsed'] = json_decode($image['metadata'], true);
    }
    
    return $images;
}

/**
 * Get main Cloudflare image for a property
 */
function getPropertyMainCloudflareImage($propertyId, $size = 'medium') {
    $images = getPropertyCloudflareImages($propertyId, $size);
    
    // Ana resim olarak işaretlenmiş olanı bul
    foreach ($images as $image) {
        if ($image['is_main'] == 1) {
            return $image;
        }
    }
    
    // Ana resim yoksa ilk resmi döndür
    return $images[0] ?? null;
}

/**
 * Get property main image URL (Cloudflare + fallback)
 */
function getPropertyMainImage($propertyId, $size = 'medium', $fallback = true) {
    // Önce Cloudflare'den dene
    $cloudflareImage = getPropertyMainCloudflareImage($propertyId, $size);
    if ($cloudflareImage && !empty($cloudflareImage['url'])) {
        return $cloudflareImage['url'];
    }
    
    if (!$fallback) {
        return null;
    }
    
    // Fallback: eski sistem
    global $conn;
    $stmt = $conn->prepare("SELECT main_image FROM properties WHERE id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && !empty($result['main_image'])) {
        // smart-image.php kullanarak eski resimleri boyutlandır
        $width = getSizeWidth($size);
        $height = getSizeHeight($size);
        return "../smart-image.php?img=" . urlencode($result['main_image']) . "&width={$width}&height={$height}";
    }
    
    // Son fallback: no-image placeholder
    return "assets/images/no-image.jpg";
}

/**
 * Get all property images (Cloudflare + fallback)
 */
function getAllPropertyImages($propertyId, $size = 'medium') {
    $allImages = [];
    
    // Cloudflare images
    $cloudflareImages = getPropertyCloudflareImages($propertyId, $size);
    foreach ($cloudflareImages as $image) {
        $allImages[] = [
            'url' => $image['url'],
            'type' => 'cloudflare',
            'id' => $image['cloudflare_image_id'],
            'is_main' => $image['is_main'],
            'filename' => $image['original_filename']
        ];
    }
    
    // Fallback: eski sistem resimleri
    if (empty($allImages)) {
        global $conn;
        $stmt = $conn->prepare("SELECT images, main_image FROM properties WHERE id = ?");
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && !empty($result['images'])) {
            $oldImages = json_decode($result['images'], true);
            if (is_array($oldImages)) {
                $width = getSizeWidth($size);
                $height = getSizeHeight($size);
                
                foreach ($oldImages as $index => $imageName) {
                    $allImages[] = [
                        'url' => "../smart-image.php?img=" . urlencode($imageName) . "&width={$width}&height={$height}",
                        'type' => 'legacy',
                        'id' => $imageName,
                        'is_main' => ($imageName === $result['main_image']),
                        'filename' => $imageName
                    ];
                }
            }
        }
    }
    
    return $allImages;
}

/**
 * Save Cloudflare image to database
 */
function saveCloudflareImageToDatabase($propertyId, $imageData) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO property_cloudflare_images 
        (property_id, cloudflare_image_id, original_filename, image_urls, metadata, is_main, domain) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isMain = $imageData['is_main'] ?? false;
    
    $stmt->bind_param("issssis", 
        $propertyId,
        $imageData['cloudflare_id'],
        $imageData['filename'],
        json_encode($imageData['urls']),
        json_encode($imageData['metadata'] ?? []),
        $isMain ? 1 : 0,
        $domain
    );
    
    $success = $stmt->execute();
    
    if ($success) {
        error_log("Saved Cloudflare image to DB: {$imageData['cloudflare_id']} for property {$propertyId}");
    } else {
        error_log("Failed to save Cloudflare image to DB: " . $stmt->error);
    }
    
    return $success;
}

/**
 * Set main Cloudflare image for property
 */
function setMainCloudflareImage($propertyId, $cloudflareImageId) {
    global $conn;
    
    // Transaction başlat
    $conn->autocommit(false);
    
    try {
        // Önce tüm resimleri non-main yap
        $stmt = $conn->prepare("UPDATE property_cloudflare_images SET is_main = 0 WHERE property_id = ?");
        $stmt->bind_param("i", $propertyId);
        $stmt->execute();
        
        // Seçili resmi main yap
        $stmt = $conn->prepare("UPDATE property_cloudflare_images SET is_main = 1 WHERE property_id = ? AND cloudflare_image_id = ?");
        $stmt->bind_param("is", $propertyId, $cloudflareImageId);
        $success = $stmt->execute();
        
        if ($success && $stmt->affected_rows > 0) {
            // Properties tablosunda cloudflare_main_image'ı güncelle
            $stmt = $conn->prepare("UPDATE properties SET cloudflare_main_image = ? WHERE id = ?");
            $stmt->bind_param("si", $cloudflareImageId, $propertyId);
            $stmt->execute();
            
            $conn->commit();
            error_log("Set main Cloudflare image: {$cloudflareImageId} for property {$propertyId}");
            return true;
        } else {
            $conn->rollback();
            error_log("Failed to set main Cloudflare image: {$cloudflareImageId} for property {$propertyId}");
            return false;
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Exception setting main Cloudflare image: " . $e->getMessage());
        return false;
    } finally {
        $conn->autocommit(true);
    }
}

/**
 * Delete Cloudflare image from database
 */
function deleteCloudflareImageFromDatabase($propertyId, $cloudflareImageId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM property_cloudflare_images WHERE property_id = ? AND cloudflare_image_id = ?");
    $stmt->bind_param("is", $propertyId, $cloudflareImageId);
    $success = $stmt->execute();
    
    if ($success) {
        error_log("Deleted Cloudflare image from DB: {$cloudflareImageId}");
        
        // Eğer silinen resim main image ise, başka bir resmi main yap
        if ($stmt->affected_rows > 0) {
            $remainingImages = getPropertyCloudflareImages($propertyId);
            if (!empty($remainingImages)) {
                // İlk resmi main yap
                setMainCloudflareImage($propertyId, $remainingImages[0]['cloudflare_image_id']);
            }
        }
    } else {
        error_log("Failed to delete Cloudflare image from DB: " . $stmt->error);
    }
    
    return $success;
}

/**
 * Get size dimensions for different image sizes
 */
function getSizeWidth($size) {
    $sizes = [
        'thumbnail' => 150,
        'small' => 400,
        'medium' => 800,
        'large' => 1200,
        'original' => 1920
    ];
    
    return $sizes[$size] ?? $sizes['medium'];
}

function getSizeHeight($size) {
    $sizes = [
        'thumbnail' => 150,
        'small' => 300,
        'medium' => 600,
        'large' => 900,
        'original' => 1080
    ];
    
    return $sizes[$size] ?? $sizes['medium'];
}

/**
 * Process uploaded images (ana fonksiyon)
 */
function processPropertyImages($propertyId, $editMode = false) {
    error_log("=== CLOUDFLARE IMAGES PROCESSING START ===");
    error_log("Property ID: {$propertyId}, Edit Mode: " . ($editMode ? 'true' : 'false'));
    
    $cloudflare = new CloudflareImages();
    $uploadResults = [];
    $finalImageCount = 0;
    
    // Edit mode: Mevcut resimlerden başla
    if ($editMode && !empty($_POST['updated_existing_images'])) {
        $existingImages = json_decode($_POST['updated_existing_images'], true);
        if (is_array($existingImages)) {
            error_log("Edit mode: Existing images count: " . count($existingImages));
        }
    }
    
    // Yeni resimler varsa Cloudflare'e yükle
    if (!empty($_FILES['property_images']['name'][0])) {
        $fileCount = count($_FILES['property_images']['name']);
        error_log("Processing {$fileCount} new images for Cloudflare");
        
        $imagesToUpload = [];
        
        // Dosyaları hazırla
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['property_images']['error'][$i] === UPLOAD_ERR_OK) {
                $imagesToUpload[] = [
                    'tmp_name' => $_FILES['property_images']['tmp_name'][$i],
                    'name' => $_FILES['property_images']['name'][$i],
                    'size' => $_FILES['property_images']['size'][$i],
                    'type' => $_FILES['property_images']['type'][$i]
                ];
            }
        }
        
        if (!empty($imagesToUpload)) {
            error_log("Uploading " . count($imagesToUpload) . " images to Cloudflare");
            
            foreach ($imagesToUpload as $index => $imageFile) {
                $metadata = [
                    'original_filename' => $imageFile['name'],
                    'file_size' => $imageFile['size'],
                    'upload_index' => $index,
                    'mime_type' => $imageFile['type']
                ];
                
                $uploadResult = $cloudflare->uploadPropertyImage($imageFile['tmp_name'], $propertyId, $metadata);
                
                if ($uploadResult['success']) {
                    // Database'e kaydet
                    $imageData = [
                        'cloudflare_id' => $uploadResult['image_id'],
                        'urls' => $uploadResult['urls'],
                        'filename' => $imageFile['name'],
                        'metadata' => $uploadResult['metadata'],
                        'is_main' => ($index === 0) // İlk resim main olsun
                    ];
                    
                    if (saveCloudflareImageToDatabase($propertyId, $imageData)) {
                        $uploadResults[] = $uploadResult;
                        $finalImageCount++;
                        error_log("Successfully processed image: {$uploadResult['image_id']}");
                    } else {
                        error_log("Failed to save to database: {$uploadResult['image_id']}");
                    }
                } else {
                    error_log("Cloudflare upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
                }
            }
        }
    }
    
    // Ana resim belirleme
    $mainImageId = '';
    $allImages = getPropertyCloudflareImages($propertyId);
    
    if (!empty($allImages)) {
        $mainImage = array_filter($allImages, function($img) {
            return $img['is_main'] == 1;
        });
        
        if (empty($mainImage) && !empty($allImages)) {
            // İlk resmi ana resim yap
            setMainCloudflareImage($propertyId, $allImages[0]['cloudflare_image_id']);
            $mainImageId = $allImages[0]['cloudflare_image_id'];
        } else {
            $mainImageId = array_values($mainImage)[0]['cloudflare_image_id'] ?? '';
        }
        
        $finalImageCount = count($allImages);
    }
    
    error_log("=== CLOUDFLARE IMAGES PROCESSING END ===");
    error_log("Final image count: {$finalImageCount}, Main image: {$mainImageId}");
    
    return [
        'cloudflare_images' => json_encode($allImages),
        'main_cloudflare_image' => $mainImageId,
        'images_count' => $finalImageCount,
        'upload_results' => $uploadResults
    ];
}

/**
 * Generate responsive image HTML
 */
function generateResponsiveImageHTML($propertyId, $alt = '', $class = '', $lazy = true) {
    $images = getPropertyCloudflareImages($propertyId);
    $mainImage = getPropertyMainCloudflareImage($propertyId);
    
    if (!$mainImage) {
        return '<img src="assets/images/no-image.jpg" alt="' . htmlspecialchars($alt) . '" class="' . htmlspecialchars($class) . '">';
    }
    
    $urls = $mainImage['all_urls'];
    $lazyAttr = $lazy ? 'loading="lazy"' : '';
    
    return sprintf(
        '<img src="%s" 
              srcset="%s 400w, %s 800w, %s 1200w" 
              sizes="(max-width: 400px) 400px, (max-width: 800px) 800px, 1200px"
              alt="%s" 
              class="%s" 
              %s>',
        htmlspecialchars($urls['small'] ?? ''),
        htmlspecialchars($urls['small'] ?? ''),
        htmlspecialchars($urls['medium'] ?? ''),
        htmlspecialchars($urls['large'] ?? ''),
        htmlspecialchars($alt),
        htmlspecialchars($class),
        $lazyAttr
    );
}

/**
 * Get image count for property
 */
function getPropertyImageCount($propertyId) {
    $cloudflareImages = getPropertyCloudflareImages($propertyId);
    return count($cloudflareImages);
}

/**
 * Check if property has images
 */
function propertyHasImages($propertyId) {
    return getPropertyImageCount($propertyId) > 0;
}

/**
 * Clean up orphaned Cloudflare images
 */
function cleanupOrphanedCloudflareImages() {
    global $conn;
    
    error_log("Starting cleanup of orphaned Cloudflare images...");
    
    // Silinmiş property'lere ait resimleri bul
    $stmt = $conn->prepare("
        SELECT pci.cloudflare_image_id, pci.property_id 
        FROM property_cloudflare_images pci 
        LEFT JOIN properties p ON pci.property_id = p.id 
        WHERE p.id IS NULL
    ");
    $stmt->execute();
    $orphanedImages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($orphanedImages)) {
        error_log("No orphaned Cloudflare images found");
        return ['deleted_count' => 0, 'errors' => []];
    }
    
    $cloudflare = new CloudflareImages();
    $deletedCount = 0;
    $errors = [];
    
    foreach ($orphanedImages as $image) {
        $imageId = $image['cloudflare_image_id'];
        $propertyId = $image['property_id'];
        
        // Cloudflare'den sil
        if ($cloudflare->deleteImage($imageId)) {
            // Database'den sil
            $stmt = $conn->prepare("DELETE FROM property_cloudflare_images WHERE cloudflare_image_id = ?");
            $stmt->bind_param("s", $imageId);
            $stmt->execute();
            
            $deletedCount++;
            error_log("Cleaned up orphaned image: {$imageId} (property: {$propertyId})");
        } else {
            $errors[] = "Failed to delete image: {$imageId}";
            error_log("Failed to delete orphaned image: {$imageId}");
        }
    }
    
    error_log("Cleanup completed. Deleted: {$deletedCount}, Errors: " . count($errors));
    
    return [
        'deleted_count' => $deletedCount,
        'errors' => $errors,
        'total_orphaned' => count($orphanedImages)
    ];
}

/**
 * Migrate legacy images to Cloudflare
 */
function migrateLegacyImagesToCloudflare($propertyId) {
    global $conn;
    
    error_log("Starting legacy image migration for property: {$propertyId}");
    
    // Mevcut Cloudflare resimleri var mı kontrol et
    $existingCloudflareImages = getPropertyCloudflareImages($propertyId);
    if (!empty($existingCloudflareImages)) {
        error_log("Property {$propertyId} already has Cloudflare images, skipping migration");
        return ['success' => false, 'message' => 'Property already has Cloudflare images'];
    }
    
    // Legacy resimleri al
    $stmt = $conn->prepare("SELECT images, main_image FROM properties WHERE id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result || empty($result['images'])) {
        return ['success' => false, 'message' => 'No legacy images found'];
    }
    
    $legacyImages = json_decode($result['images'], true);
    if (!is_array($legacyImages) || empty($legacyImages)) {
        return ['success' => false, 'message' => 'Invalid legacy images data'];
    }
    
    $cloudflare = new CloudflareImages();
    $uploadedCount = 0;
    $errors = [];
    
    foreach ($legacyImages as $index => $imageName) {
        $imagePath = __DIR__ . '/../images/properties/' . $imageName;
        
        if (!file_exists($imagePath)) {
            $errors[] = "Legacy image not found: {$imageName}";
            continue;
        }
        
        $metadata = [
            'original_filename' => $imageName,
            'migrated_from' => 'legacy_system',
            'migration_date' => date('Y-m-d H:i:s')
        ];
        
        $uploadResult = $cloudflare->uploadPropertyImage($imagePath, $propertyId, $metadata);
        
        if ($uploadResult['success']) {
            $imageData = [
                'cloudflare_id' => $uploadResult['image_id'],
                'urls' => $uploadResult['urls'],
                'filename' => $imageName,
                'metadata' => $uploadResult['metadata'],
                'is_main' => ($imageName === $result['main_image'])
            ];
            
            if (saveCloudflareImageToDatabase($propertyId, $imageData)) {
                $uploadedCount++;
                error_log("Migrated legacy image: {$imageName} -> {$uploadResult['image_id']}");
            } else {
                $errors[] = "Failed to save migrated image to database: {$imageName}";
            }
        } else {
            $errors[] = "Failed to upload legacy image: {$imageName} - " . ($uploadResult['error'] ?? 'Unknown error');
        }
    }
    
    error_log("Migration completed for property {$propertyId}. Uploaded: {$uploadedCount}, Errors: " . count($errors));
    
    return [
        'success' => $uploadedCount > 0,
        'uploaded_count' => $uploadedCount,
        'errors' => $errors,
        'total_legacy_images' => count($legacyImages)
    ];
}

/**
 * Get Cloudflare usage statistics
 */
function getCloudflareUsageStats() {
    $cloudflare = new CloudflareImages();
    
    // API'den istatistikleri al
    $apiStats = $cloudflare->getUsageStats();
    
    // Database'den yerel istatistikleri al
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_images,
            COUNT(DISTINCT property_id) as properties_with_images,
            SUM(CASE WHEN is_main = 1 THEN 1 ELSE 0 END) as main_images,
            COUNT(DISTINCT domain) as domains,
            MIN(upload_date) as first_upload,
            MAX(upload_date) as last_upload
        FROM property_cloudflare_images
    ");
    $stmt->execute();
    $dbStats = $stmt->get_result()->fetch_assoc();
    
    // Domain bazlı istatistikler
    $stmt = $conn->prepare("
        SELECT domain, COUNT(*) as image_count 
        FROM property_cloudflare_images 
        GROUP BY domain 
        ORDER BY image_count DESC
    ");
    $stmt->execute();
    $domainStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    return [
        'api_stats' => $apiStats,
        'database_stats' => $dbStats,
        'domain_stats' => $domainStats
    ];
}

/**
 * Validate image file before upload
 */
function validateImageFile($file) {
    $errors = [];
    
    // Dosya tipi kontrolü
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Geçersiz dosya tipi: ' . $file['type'];
    }
    
    // Dosya boyutu kontrolü (5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $errors[] = 'Dosya çok büyük: ' . formatBytes($file['size']) . ' (Max: 5MB)';
    }
    
    // Dosya uzantısı kontrolü
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions)) {
        $errors[] = 'Geçersiz dosya uzantısı: ' . $extension;
    }
    
    // Upload hatası kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Upload hatası: ' . getUploadErrorMessage($file['error']);
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Get upload error message
 */
function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Dosya çok büyük (php.ini sınırı)';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Dosya çok büyük (form sınırı)';
        case UPLOAD_ERR_PARTIAL:
            return 'Dosya kısmen yüklendi';
        case UPLOAD_ERR_NO_FILE:
            return 'Dosya seçilmedi';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Geçici klasör bulunamadı';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Dosya yazılamadı';
        case UPLOAD_ERR_EXTENSION:
            return 'Dosya uzantısı engellendi';
        default:
            return 'Bilinmeyen hata: ' . $errorCode;
    }
}

/**
 * Create thumbnail using GD library (fallback)
 */
function createThumbnail($sourcePath, $targetPath, $width = 150, $height = 150) {
    if (!extension_loaded('gd')) {
        error_log("GD extension not loaded, cannot create thumbnail");
        return false;
    }
    
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) {
        error_log("Cannot get image info for: {$sourcePath}");
        return false;
    }
    
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Create source image
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        default:
            error_log("Unsupported image type: {$mimeType}");
            return false;
    }
    
    if (!$sourceImage) {
        error_log("Failed to create source image from: {$sourcePath}");
        return false;
    }
    
    // Calculate dimensions
    $ratio = min($width / $sourceWidth, $height / $sourceHeight);
    $newWidth = round($sourceWidth * $ratio);
    $newHeight = round($sourceHeight * $ratio);
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
    }
    
    // Resize
    imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    // Save thumbnail
    $success = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $success = imagejpeg($thumbnail, $targetPath, 85);
            break;
        case 'image/png':
            $success = imagepng($thumbnail, $targetPath);
            break;
        case 'image/gif':
            $success = imagegif($thumbnail, $targetPath);
            break;
    }
    
    // Cleanup
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);
    
    return $success;
}

// Bu dosyanın sonunda include guard
if (!defined('IMAGE_HELPERS_LOADED')) {
    define('IMAGE_HELPERS_LOADED', true);
    error_log("Image helpers loaded successfully");
}
?>