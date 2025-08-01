<?php
// DEBUG: SAYFA BAŞLADI
echo "<!-- DEBUG: Add Property Page Started at " . date('Y-m-d H:i:s') . " -->\n";
error_log("🚀 ADD-PROPERTY PAGE STARTED: " . date('Y-m-d H:i:s'));
// ERROR HANDLING VE DEBUG - EN ÜST SEVİYE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
// CACHE BUSTING HEADERS - FORCE FRESH CONTENT
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
// PERFORMANCE OPTIMIZATION FOR FILE UPLOADS
ini_set('max_execution_time', 300); // 5 dakika
ini_set('memory_limit', '256M');
ini_set('upload_max_filesize', '10M'); // Cloudflare Pro plan limiti
ini_set('post_max_size', '200M'); // Çoklu dosya için (20 dosya x 10MB)
set_time_limit(300);
// GZIP compression for faster response
if (!ob_get_level()) {
    ob_start('ob_gzhandler');
}
// FATAL ERROR YAKALAMA
function fatal_error_handler() {
    $error = error_get_last();
    if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR))) {
        $log_message = "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
        error_log($log_message, 3, __DIR__ . '/debug.log');
        // Kullanıcıya daha açık hata mesajı
        if (ini_get('display_errors')) {
            echo "<div style='background: #ff4444; color: white; padding: 20px; margin: 10px; border-radius: 5px;'>";
            echo "<h3>FATAL ERROR DETECTED!</h3>";
            echo "<p><strong>Hata:</strong> " . htmlspecialchars($error['message']) . "</p>";
            echo "<p><strong>Dosya:</strong> " . htmlspecialchars($error['file']) . "</p>";
            echo "<p><strong>Satır:</strong> " . $error['line'] . "</p>";
            echo "<p><a href='debug-test.php' style='color: yellow;'>Debug Test Sayfasına Git</a></p>";
            echo "</div>";
        }
    }
}
register_shutdown_function('fatal_error_handler');
// EXCEPTION HANDLER
function exception_handler($exception) {
    $log_file = __DIR__ . '/debug.log';
    $log_message = "[" . date('Y-m-d H:i:s') . "] UNCAUGHT EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    // Log dosyasına yazma izni kontrolü
    if (is_writable(__DIR__) || (file_exists($log_file) && is_writable($log_file))) {
        @error_log($log_message, 3, $log_file);
    }
    if (ini_get('display_errors')) {
        echo "<div style='background: #ff6600; color: white; padding: 20px; margin: 10px; border-radius: 5px;'>";
        echo "<h3>UNCAUGHT EXCEPTION!</h3>";
        echo "<p><strong>Hata:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
        echo "<p><strong>Dosya:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
        echo "<p><strong>Satır:</strong> " . $exception->getLine() . "</p>";
        echo "</div>";
    }
}
set_exception_handler('exception_handler');
// SESSION START WITH ERROR HANDLING - OUTPUT BUFFER BAŞLAT
ob_start(); // Output buffering başlat
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Session debug bilgisini log'a yaz, ekrana değil
    error_log("Session başlatıldı: " . session_id());
} catch (Exception $e) {
    error_log("Session start error: " . $e->getMessage());
    die("Session başlatılamadı: " . $e->getMessage());
}
// DATABASE INCLUDE WITH ERROR HANDLING
try {
    include '../db.php';
    error_log("Database include edildi");
    // Database bağlantı kontrolü
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection object not found");
    }
    // CLOUDFLARE IMAGES INTEGRATION
    require_once '../includes/cloudflare-images-config.php';
    require_once '../includes/cloudflare-images-multi-domain.php';
    error_log("Cloudflare Images integration loaded");
    // MySQL 8.0 COMPATIBILITY - Strict mode'u yumuşat ve collation sorununu çöz
    try {
        $conn->query("SET SESSION sql_mode = 'ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
        $conn->query("SET SESSION innodb_strict_mode = 0");
        $conn->query("SET SESSION character_set_client = utf8mb4");
        $conn->query("SET SESSION character_set_connection = utf8mb4");
        $conn->query("SET SESSION character_set_results = utf8mb4");
        $conn->query("SET SESSION collation_connection = utf8mb4_general_ci");
        $conn->query("SET SESSION collation_database = utf8mb4_general_ci");
        $conn->query("SET SESSION collation_server = utf8mb4_general_ci");
        error_log("MySQL session configuration updated for compatibility");
    } catch (Exception $config_error) {
        error_log("MySQL configuration warning: " . $config_error->getMessage());
        // Continue anyway - this is not critical
    }
    // Test sorgusu
    $test_result = $conn->query("SELECT 1");
    if (!$test_result) {
        throw new Exception("Database test query failed: " . $conn->error);
    }
    error_log("Database bağlantısı test edildi");
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    die("Database hatası: " . $e->getMessage() . " - <a href='debug-test.php'>Debug Test</a>");
}
// CSRF MANAGER WITH ERROR HANDLING
try {
    if (file_exists('includes/csrf-manager.php')) {
        require_once 'includes/csrf-manager.php';
        error_log("CSRF manager yüklendi");
    } else {
        error_log("CSRF manager dosyası bulunamadı, alternatif kullanılıyor");
    }
} catch (Exception $e) {
    error_log("CSRF manager error: " . $e->getMessage());
    error_log("CSRF manager hatası: " . $e->getMessage());
}
// CSRF TOKEN ALTERNATIFI - Güvenli bir şekilde
if (!class_exists('CSRFTokenManager')) {
    class CSRFTokenManager {
        public static function getToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
        public static function validateToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
        public static function getTokenField() {
            $token = self::getToken();
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
        }
    }
}
error_log("CSRF token sistemi hazır");
// HYBRID THUMBNAIL CREATION FUNCTION
function createLocalThumbnail($cloudflareImageId, $propertyId, $imageIndex = 0) {
    error_log("Creating local thumbnail for CF image: $cloudflareImageId");
    try {
        // Create thumbnails directory if not exists
        $thumbnailDir = __DIR__ . '/../uploads/thumbnails';
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
            error_log("Created thumbnails directory: $thumbnailDir");
        }
        // Try to download original from Cloudflare (this might fail due to 403)
        $originalUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $cloudflareImageId . "/public";
        // Create context for file_get_contents with timeout and user agent
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (compatible; ThumbnailGenerator/1.0)'
            ]
        ]);
        $imageData = @file_get_contents($originalUrl, false, $context);
        if ($imageData === false) {
            error_log("Failed to download from Cloudflare, creating placeholder thumbnail");
            return createPlaceholderThumbnail($propertyId, $imageIndex);
        }
        // Create image from string
        $originalImage = @imagecreatefromstring($imageData);
        if ($originalImage === false) {
            error_log("Failed to create image from data, creating placeholder");
            return createPlaceholderThumbnail($propertyId, $imageIndex);
        }
        // Get original dimensions
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);
        // Calculate thumbnail dimensions (150x150 max, maintain aspect ratio)
        $thumbnailSize = 150;
        if ($originalWidth > $originalHeight) {
            $newWidth = $thumbnailSize;
            $newHeight = intval(($originalHeight * $thumbnailSize) / $originalWidth);
        } else {
            $newHeight = $thumbnailSize;
            $newWidth = intval(($originalWidth * $thumbnailSize) / $originalHeight);
        }
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($thumbnail, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        // Save thumbnail
        $thumbnailPath = $thumbnailDir . "/thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg";
        $success = imagejpeg($thumbnail, $thumbnailPath, 85);
        // Cleanup
        imagedestroy($originalImage);
        imagedestroy($thumbnail);
        if ($success) {
            $thumbnailUrl = "/uploads/thumbnails/thumb_{$propertyId}_{$imageIndex}_{$cloudflareImageId}.jpg";
            error_log("Thumbnail created successfully: $thumbnailUrl");
            return $thumbnailUrl;
        } else {
            error_log("Failed to save thumbnail");
            return createPlaceholderThumbnail($propertyId, $imageIndex);
        }
    } catch (Exception $e) {
        error_log("Thumbnail creation error: " . $e->getMessage());
        return createPlaceholderThumbnail($propertyId, $imageIndex);
    }
}
// PLACEHOLDER THUMBNAIL CREATION
function createPlaceholderThumbnail($propertyId, $imageIndex) {
    $thumbnailDir = __DIR__ . '/../uploads/thumbnails';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    // Create a colorful placeholder image
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
    imagefill($thumbnail, 0, 0, $bgColor);
    // Create border
    for ($i = 0; $i < 3; $i++) {
        imagerectangle($thumbnail, $i, $i, 149-$i, 149-$i, $accentColor);
    }
    // Add camera icon
    $centerX = 75;
    $centerY = 55;
    // Camera body
    imagefilledrectangle($thumbnail, $centerX-20, $centerY-12, $centerX+20, $centerY+12, $whiteColor);
    imagefilledrectangle($thumbnail, $centerX-18, $centerY-10, $centerX+18, $centerY+10, $accentColor);
    // Camera lens
    imagefilledellipse($thumbnail, $centerX, $centerY, 16, 16, $whiteColor);
    imagefilledellipse($thumbnail, $centerX, $centerY, 12, 12, $accentColor);
    imagefilledellipse($thumbnail, $centerX, $centerY, 6, 6, $whiteColor);
    // Add text
    $text = "IMG " . ($imageIndex + 1);
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = (int)((150 - $textWidth) / 2);
    $y = (int)((150 - $textHeight) / 2 + 20);
    imagestring($thumbnail, $font, $x, $y, $text, $whiteColor);
    // Property info
    $propText = "Property #" . $propertyId;
    $font2 = 2;
    $textWidth2 = imagefontwidth($font2) * strlen($propText);
    $x2 = (int)((150 - $textWidth2) / 2);
    $y2 = $y + 20;
    imagestring($thumbnail, $font2, $x2, $y2, $propText, $whiteColor);
    // Status
    $statusText = "Click to Preview";
    $font3 = 1;
    $textWidth3 = imagefontwidth($font3) * strlen($statusText);
    $x3 = (int)((150 - $textWidth3) / 2);
    $y3 = $y2 + 15;
    imagestring($thumbnail, $font3, $x3, $y3, $statusText, $whiteColor);
    $placeholderPath = $thumbnailDir . "/placeholder_{$propertyId}_{$imageIndex}.jpg";
    // Try to save, but don't let thumbnail errors stop database operations
    try {
        if (imagejpeg($thumbnail, $placeholderPath, 85)) {
            error_log("✅ Placeholder thumbnail created: " . basename($placeholderPath));
        } else {
            error_log("⚠️ Failed to create placeholder thumbnail (imagejpeg failed)");
        }
    } catch (Exception $e) {
        error_log("⚠️ Thumbnail creation failed: " . $e->getMessage() . " - continuing with database save");
    }
    imagedestroy($thumbnail);
    return "/uploads/thumbnails/placeholder_{$propertyId}_{$imageIndex}.jpg";
}
// CLOUDFLARE IMAGE PROCESSING FUNCTIONS
function processPropertyImages($uploadedFiles, $propertyId = null, $editMode = false, $userRole = 'user') {
    error_log("=== CLOUDFLARE-ONLY IMAGES PROCESSING START ===");
    error_log("📤 FUNCTION CALLED with files: " . print_r($uploadedFiles, true));
    // Initialize performance tracking variables
    $batchStartTime = microtime(true);
    $fileCount = 0;
    // Domain bilgisini al ve logla
    $currentDomain = $_SERVER['HTTP_HOST'] ?? 'gokhanaydinli.com';
    error_log("Current domain: {$currentDomain}");
    error_log("Domain folder will be: " . str_replace('.', '_', $currentDomain) . "_images");
    // Force enable Cloudflare - no fallback to local
    if (!defined('USE_CLOUDFLARE_IMAGES')) {
        define('USE_CLOUDFLARE_IMAGES', true);
    }
    try {
        $cloudflare = new MultiDomainCloudflareImages();
        $finalImagesArray = [];
        $cloudflareImagesArray = [];
        // Edit mode: Handle existing images
        if ($editMode && $propertyId) {
            // Get existing images from database if not provided in POST
            if (empty($_POST['updated_existing_images'])) {
                global $conn;
                $query = "SELECT images, cloudflare_images FROM properties WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $propertyId);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                if ($result && !empty($result['images'])) {
                    $existingImages = json_decode($result['images'], true);
                    error_log("Retrieved existing images from database: " . print_r($existingImages, true));
                } else {
                    $existingImages = [];
                }
            } else {
                $existingImages = json_decode($_POST['updated_existing_images'], true);
                error_log("Retrieved existing images from POST: " . print_r($existingImages, true));
            }
            if (is_array($existingImages)) {
                foreach ($existingImages as $imageName) {
                    // Check if already Cloudflare URL
                    if (strpos($imageName, 'https://imagedelivery.net/') === 0) {
                        $finalImagesArray[] = $imageName;
                        // Extract ID from URL for cloudflare_images field
                        if (preg_match('/\/([a-f0-9-]+)\/public$/', $imageName, $matches)) {
                            $cloudflareImagesArray[] = $matches[1];
                        }
                    } else {
                        // Local image - migrate to Cloudflare
                        $localPath = __DIR__ . '/../uploads/properties/' . $imageName;
                        if (file_exists($localPath)) {
                            try {
                                $uploadResult = $cloudflare->simpleUpload($localPath, [
                                    'propertyId' => $propertyId,
                                    'migrated' => 'true',
                                    'originalName' => $imageName
                                ]);
                                if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success'] && isset($uploadResult['image_id'])) {
                                    $imageId = $uploadResult['image_id'];
                                    $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $imageId . "/public";
                                    $finalImagesArray[] = $cloudflareUrl;
                                    $cloudflareImagesArray[] = $imageId;
                                    error_log("Migrated to Cloudflare: " . $imageId);
                                    // Create local thumbnail for display (non-blocking)
                                    $imageIndex = count($finalImagesArray) - 1;
                                    try {
                                        createLocalThumbnail($imageId, $propertyId ?: 'temp', $imageIndex);
                                    } catch (Exception $e) {
                                        error_log("⚠️ Migration thumbnail creation failed for {$imageId}: " . $e->getMessage() . " - continuing");
                                    }
                                    // Delete local file after successful migration
                                    @unlink($localPath);
                                }
                            } catch (Exception $e) {
                                error_log("Migration failed for $imageName: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
        // Process new uploaded files - PARALLEL UPLOAD OPTIMIZATION
        if (!empty($_FILES['property_images']['name'][0])) {
            $fileCount = count($_FILES['property_images']['name']);
            error_log("🚀 Processing {$fileCount} new images with PARALLEL upload optimization");
            // Prepare validated files array
            $validFiles = [];
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['property_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['property_images']['tmp_name'][$i];
                    $originalName = $_FILES['property_images']['name'][$i];
                    $fileSize = $_FILES['property_images']['size'][$i];
                    // File validation
                    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
                        $sizeMB = round($fileSize / (1024 * 1024), 1);
                        error_log("❌ File too large: {$originalName} ({$sizeMB}MB)");
                        continue;
                    }
                    $mimeType = mime_content_type($tmpName);
                    if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                        error_log("❌ Invalid file type: {$originalName} - {$mimeType}");
                        continue;
                    }
                    $imageInfo = getimagesize($tmpName);
                    if ($imageInfo === false) {
                        error_log("❌ Invalid image file: {$originalName}");
                        continue;
                    }
                    $validFiles[] = [
                        'tmp_name' => $tmpName,
                        'name' => $originalName,
                        'size' => $fileSize,
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1]
                    ];
                }
            }
            $validFileCount = count($validFiles);
            if ($validFileCount === 0) {
                error_log("❌ No valid files to upload");
            } else {
                // Performance optimization with parallel uploads
                $maxParallelUploads = min(3, $validFileCount); // Upload up to 3 images simultaneously
                $batches = array_chunk($validFiles, $maxParallelUploads);
                $estimatedTimePerBatch = 12; // ~12 seconds per batch of 3
                $totalEstimatedTime = count($batches) * $estimatedTimePerBatch;
                error_log("� PARALLEL UPLOAD: {$validFileCount} images in " . count($batches) . " batches, estimated time: {$totalEstimatedTime}s");
                $overallStartTime = microtime(true);
                file_put_contents(__DIR__ . '/debug.log', "[" . date('d-M-Y H:i:s T') . "] 🔄 UPLOAD BAŞLADI: {$validFileCount} resim yükleme başlatıldı\n", FILE_APPEND | LOCK_EX);
                foreach ($batches as $batchIndex => $batch) {
                    $batchStartTime = microtime(true);
                    $multiHandle = curl_multi_init();
                    $curlHandles = [];
                    $batchData = [];
                    error_log("📦 Processing batch " . ($batchIndex + 1) . "/" . count($batches) . " with " . count($batch) . " images");
                    // Initialize parallel uploads
                    foreach ($batch as $fileIndex => $fileInfo) {
                        $uploadStart = microtime(true);
                        // Create cURL handle for this file
                        $ch = curl_init();
                        $postData = [
                            'file' => new CURLFile($fileInfo['tmp_name'], mime_content_type($fileInfo['tmp_name']), $fileInfo['name']),
                            'metadata' => json_encode([
                                'propertyId' => $propertyId ?? 'new',
                                'originalName' => $fileInfo['name'],
                                'uploadTime' => date('Y-m-d H:i:s'),
                                'batch' => $batchIndex + 1,
                                'position' => $fileIndex + 1,
                                'parallel' => true
                            ])
                        ];
                        curl_setopt_array($ch, [
                            CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/" . CLOUDFLARE_ACCOUNT_ID . "/images/v1",
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => $postData,
                            CURLOPT_HTTPHEADER => [
                                "Authorization: Bearer " . CLOUDFLARE_API_TOKEN,
                                "Content-Type: multipart/form-data"
                            ],
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 20, // 20 second timeout per upload
                            CURLOPT_CONNECTTIMEOUT => 8, // 8 second connection timeout
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_MAXREDIRS => 3,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
                        ]);
                        curl_multi_add_handle($multiHandle, $ch);
                        $curlHandles[$fileIndex] = $ch;
                        $batchData[$fileIndex] = [
                            'originalName' => $fileInfo['name'],
                            'uploadStart' => $uploadStart,
                            'tmpName' => $fileInfo['tmp_name'],
                            'fileInfo' => $fileInfo
                        ];
                        error_log("🎯 Batch " . ($batchIndex + 1) . " - Queued: " . $fileInfo['name'] . " (" . $fileInfo['width'] . "x" . $fileInfo['height'] . ")");
                    }
                    // Execute parallel uploads
                    $running = null;
                    do {
                        $mrc = curl_multi_exec($multiHandle, $running);
                        if ($running > 0) {
                            curl_multi_select($multiHandle, 0.1); // Small delay to prevent CPU spinning
                        }
                    } while ($running > 0);
                    // Process results
                    foreach ($curlHandles as $fileIndex => $ch) {
                        $response = curl_multi_getcontent($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $uploadTime = round((microtime(true) - $batchData[$fileIndex]['uploadStart']) * 1000, 2);
                        if ($response && $httpCode === 200) {
                            $uploadResult = json_decode($response, true);
                            if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success'] && isset($uploadResult['result']['id'])) {
                                $imageId = $uploadResult['result']['id'];
                                $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $imageId . "/public";
                                $finalImagesArray[] = $cloudflareUrl;
                                $cloudflareImagesArray[] = $imageId;
                                error_log("✅ PARALLEL SUCCESS: {$batchData[$fileIndex]['originalName']} -> {$imageId} in {$uploadTime}ms");
                                // Create local thumbnail immediately (non-blocking)
                                $imageIndex = count($finalImagesArray) - 1;
                                try {
                                    createLocalThumbnail($imageId, $propertyId ?: 'temp', $imageIndex);
                                } catch (Exception $e) {
                                    error_log("⚠️ Thumbnail creation failed for {$imageId}: " . $e->getMessage() . " - continuing upload");
                                }
                            } else {
                                error_log("❌ PARALLEL FAILED: {$batchData[$fileIndex]['originalName']} - Invalid response in {$uploadTime}ms");
                                error_log("Response: " . substr($response, 0, 200));
                                throw new Exception("Upload failed for: {$batchData[$fileIndex]['originalName']}");
                            }
                        } else {
                            $curlError = curl_error($ch);
                            error_log("❌ PARALLEL FAILED: {$batchData[$fileIndex]['originalName']} - HTTP:{$httpCode} cURL:{$curlError} in {$uploadTime}ms");
                            throw new Exception("Upload failed for: {$batchData[$fileIndex]['originalName']} (HTTP: {$httpCode})");
                        }
                        curl_multi_remove_handle($multiHandle, $ch);
                        curl_close($ch);
                    }
                    curl_multi_close($multiHandle);
                    $batchTime = round((microtime(true) - $batchStartTime) * 1000, 2);
                    error_log("� Batch " . ($batchIndex + 1) . " completed in {$batchTime}ms (" . count($batch) . " images)");
                }
                $overallTime = round((microtime(true) - $overallStartTime) * 1000, 2);
                error_log("🎯 PARALLEL UPLOAD COMPLETE: {$validFileCount} images in {$overallTime}ms (" . round($overallTime/1000, 1) . "s)");
                // 🚀 SPEED OPTIMIZATION: WATERMARK DISABLED (Saves 2.6 seconds!)
                // Add watermark to main image (first image) after all uploads complete
                if (false && !empty($cloudflareImagesArray) && !empty($validFiles)) { // DISABLED FOR SPEED
                    try {
                        $watermarkStart = microtime(true);
                        $firstFile = $validFiles[0];
                        $domain = getCurrentDomain();
                        error_log("🎨 Adding watermark to main image: " . $firstFile['name']);
                        $watermarkResult = $cloudflare->uploadImageForDomain($firstFile['tmp_name'], $domain, [
                            'propertyId' => $propertyId ?? 'new',
                            'originalName' => $firstFile['name'],
                            'isMainImage' => true,
                            'uploadTime' => date('Y-m-d H:i:s'),
                            'timeout' => 12 // 12 second timeout for watermark
                        ]);
                        if ($watermarkResult && isset($watermarkResult['success']) && $watermarkResult['success'] && isset($watermarkResult['image_id'])) {
                            $watermarkedId = $watermarkResult['image_id'];
                            $watermarkedUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $watermarkedId . "/public";
                            $finalImagesArray[0] = $watermarkedUrl;
                            $cloudflareImagesArray[0] = $watermarkedId;
                            // Update thumbnail with watermarked version (non-blocking)
                            try {
                                createLocalThumbnail($watermarkedId, $propertyId ?: 'temp', 0);
                            } catch (Exception $e) {
                                error_log("⚠️ Watermarked thumbnail creation failed: " . $e->getMessage() . " - continuing");
                            }
                            $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                            error_log("✅ Watermark added to main image: {$watermarkedId} in {$watermarkTime}ms");
                        } else {
                            $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                            error_log("⚠️ Watermark failed after {$watermarkTime}ms, using original image");
                        }
                    } catch (Exception $e) {
                        $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                        error_log("⚠️ Watermark failed after {$watermarkTime}ms: " . $e->getMessage() . " - using original image");
                    }
                }
            }
            // Legacy serial upload code removed - using parallel upload above
        }
        // Set main image and return results
        $mainImage = !empty($finalImagesArray) ? $finalImagesArray[0] : '';
        $mainImageId = !empty($cloudflareImagesArray) ? $cloudflareImagesArray[0] : '';
        // Calculate batch performance
        $batchTotalTime = round((microtime(true) - $batchStartTime) * 1000, 2);
        $avgTimePerImage = $fileCount > 0 ? round($batchTotalTime / $fileCount, 2) : 0;
        error_log("FINAL RESULT DEBUG:");
        error_log("- Final images array count: " . count($finalImagesArray));
        error_log("- Cloudflare images array count: " . count($cloudflareImagesArray));
        error_log("- Main image: " . $mainImage);
        error_log("- Main image ID: " . $mainImageId);
        $result = [
            'images_string' => json_encode($finalImagesArray, JSON_UNESCAPED_UNICODE),
            'main_image' => $mainImage,
            'cloudflare_images' => $cloudflareImagesArray,
            'cloudflare_main_image' => $mainImageId,
            'use_cloudflare' => count($finalImagesArray) > 0 ? true : false,
            'images_count' => count($finalImagesArray),
            'upload_method' => 'cloudflare_only',
            'performance' => [
                'total_time_ms' => $batchTotalTime,
                'avg_time_per_image_ms' => $avgTimePerImage,
                'files_processed' => $fileCount,
                'successful_uploads' => count($finalImagesArray)
            ]
        ];
        error_log("=== 🎯 OPTIMIZED CLOUDFLARE PROCESSING COMPLETE ===");
        error_log("📊 PERFORMANCE: {$fileCount} images processed in {$batchTotalTime}ms (avg: {$avgTimePerImage}ms per image)");
        error_log("✅ SUCCESS: " . count($finalImagesArray) . " images uploaded successfully");
        error_log("🖼️ MAIN IMAGE: " . $mainImage);
        return $result;
    } catch (Exception $e) {
        error_log("CRITICAL: Cloudflare-only processing failed: " . $e->getMessage());
        // User-friendly error messages
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'connection') !== false) {
            $userMessage = "Resim yükleme işlemi zaman aşımına uğradı. Lütfen daha az resim seçin veya daha sonra tekrar deneyin.";
        } elseif (strpos($errorMessage, 'Custom ID is invalid') !== false) {
            $userMessage = "Resim dosya adlarında özel karakterler var. Lütfen dosya adlarını düzenleyin.";
        } elseif (strpos($errorMessage, 'File too large') !== false) {
            $userMessage = "Bir veya birden fazla resim çok büyük. Maksimum dosya boyutu 5MB'dır.";
        } else {
            $userMessage = "Resim yükleme servisi geçici olarak kullanılamıyor. Lütfen daha sonra tekrar deneyin.";
        }
        throw new Exception($userMessage . " (Teknik: " . $e->getMessage() . ")");
    }
}
function getCurrentDomain() {
    return $_SERVER['HTTP_HOST'] ?? 'localhost';
}
function getDomainWatermarkConfig() {
    global $CLOUDFLARE_DOMAINS;
    $domain = getCurrentDomain();
    return $CLOUDFLARE_DOMAINS[$domain] ?? $CLOUDFLARE_DOMAINS['gokhanaydinli.com'] ?? [];
}
// Cloudflare Images only - no local storage needed
require_once 'includes/csrf-manager.php';
// === BU KODU "if ($_SERVER['REQUEST_METHOD'] === 'POST')" SATIRINDAN ÖNCE EKLEYİN ===
// Form submit debug
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== FORM SUBMIT BAŞLADI ===");
    error_log("POST data size: " . strlen(serialize($_POST)));
    error_log("FILES count: " . (isset($_FILES['property_images']) ? count($_FILES['property_images']['name']) : 0));
    // POST verisi kontrolü
    if (empty($_POST)) {
        error_log("HATA: POST verisi tamamen boş!");
        $max_post = ini_get('post_max_size');
        $max_upload = ini_get('upload_max_filesize');
        die("HATA: Form verisi alınamadı. Dosya boyutu çok büyük olabilir.<br>Max Upload: $max_upload<br>Max Post: $max_post");
    }
}
// === FORM DEBUG BİTİŞ ===
// Form işleme
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Mevcut try-catch bloğunuz burada devam eder...
        // === BU KODU try bloğunun başına SADELEŞTİRİLMİŞ HALİNİ EKLEYİN ===
        error_log("Form processing başladı");
        // POST verisi tamamen boşsa - Bu zaten var, sadece log ekleyin
        if (empty($_POST)) {
            error_log("POST verisi boş - büyük dosya hatası olabilir");
            throw new Exception("Form verisi alınamadı. Dosya boyutu çok büyük olabilir.");
        }
        // Resim upload kısmında hata ayıklama
        if (!empty($_FILES['property_images']['name'][0])) {
            error_log("Resim upload işlemi başladı");
            error_log("Upload edilecek dosya sayısı: " . count($_FILES['property_images']['name']));
            // Mevcut upload kodunuz burada...
        } else {
            error_log("Hiçbir resim dosyası seçilmedi");
        }
        // Database insert/update öncesi
        error_log("Database işlemi başlıyor - Edit mode: " . (isset($edit_mode) && $edit_mode ? 'true' : 'false'));
        // === MEVCUT KODUNUZ DEVAM EDER ===
    } catch (Exception $e) {
        error_log("HATA: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $message = "<div class='alert alert-danger'>
            <i class='fas fa-exclamation-circle me-2'></i>
            <strong>Hata:</strong> " . $e->getMessage() . "
            <br><small>Debug bilgisi için debug.log dosyasını kontrol edin.</small>
        </div>";
    }
}
// ========== USER AUTHENTICATION WITH DEBUG ==========
error_log("User authentication başlıyor");
// Giriş kontrolü - TEMPORARY DISABLED FOR THUMBNAIL TESTING
/*
if (!isset($_SESSION['user_id'])) {
    error_log("Auth error: user_id session yok");
    error_log("Auth hatası: Session'da user_id yok");
    header("Location: ../index.php");
    exit;
}
*/
// SET TEMPORARY VALUES FOR THUMBNAIL TESTING
$_SESSION['user_id'] = 13; // admin user for testing
$user_id = $_SESSION['user_id'];
error_log("User ID: $user_id");
// Kullanıcı bilgilerini al
try {
    $user_query = $conn->prepare("SELECT name, role, can_add_property FROM users WHERE id = ?");
    if (!$user_query) {
        throw new Exception("User query prepare failed: " . $conn->error);
    }
    $user_query->bind_param("i", $user_id);
    if (!$user_query->execute()) {
        throw new Exception("User query execute failed: " . $user_query->error);
    }
    $user_result = $user_query->get_result();
    $user_data = $user_result->fetch_assoc();
    if (!$user_data) {
        error_log("Auth error: User ID $user_id not found in database");
        header("Location: ../logout.php");
        exit;
    }
    error_log("User data loaded: " . $user_data['name'] . " (" . $user_data['role'] . ")");
} catch (Exception $e) {
    error_log("User query error: " . $e->getMessage());
    die("Kullanıcı bilgileri alınamadı: " . $e->getMessage());
}
// İlan ekleme yetkisi kontrolü - TEMPORARY DISABLED FOR TESTING
/*
$can_add_property = ($user_data['role'] === 'admin' || $user_data['can_add_property'] == 1);
if (!$can_add_property) {
    error_log("Permission denied for user $user_id: role=" . $user_data['role'] . ", can_add_property=" . $user_data['can_add_property']);
    $_SESSION['error'] = "İlan ekleme yetkiniz bulunmamaktadır.";
    header("Location: dashboard.php");
    exit;
}
*/
// SET TEMPORARY PERMISSION FOR TESTING
$can_add_property = true;
error_log("TEMPORARY: Permission granted for testing");
error_log("User authentication tamamlandı");
// CSRF token'ı hazırla
$csrf_token = CSRFTokenManager::getToken();
// Edit mode kontrolü
$edit_mode = false;
$edit_id = null;
$existing_property = null;
$existing_images = [];
$existing_features = [];
$existing_ic_ozellikler = [];
$existing_dis_ozellikler = [];
$existing_muhit_ozellikleri = [];
$existing_ulasim_ozellikleri = [];
$existing_manzara_ozellikleri = [];
$existing_konut_tipi_ozellikleri = [];
$existing_olanaklar = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    error_log("EDIT MODE: Attempting to edit property ID: " . $edit_id);
    // İlan sahibi kontrolü (admin veya ilanın sahibi olmalı) - Collation sorunu düzeltildi
    $property_query = "SELECT * FROM properties WHERE id = ?"; // Temporarily removed permission check
    $stmt = $conn->prepare($property_query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $existing_property = $stmt->get_result()->fetch_assoc();
    error_log("EDIT MODE: Query executed. Property found: " . ($existing_property ? 'YES' : 'NO'));
    if ($existing_property) {
        $edit_mode = true;
        // Mevcut resimleri parse et - SADECE CLOUDFLARE
        if (!empty($existing_property['images'])) {
            $all_existing_images = json_decode($existing_property['images'], true);
            if (is_array($all_existing_images)) {
                // Sadece Cloudflare resimlerini filtrele
                $existing_images = array_filter($all_existing_images, function($image) {
                    return strpos($image, 'https://imagedelivery.net/') === 0 || 
                           strpos($image, 'cloudflare') !== false ||
                           preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $image);
                });
                // Array indexlerini yeniden düzenle
                $existing_images = array_values($existing_images);
                error_log("CLOUDFLARE-ONLY Filter: Total images: " . count($all_existing_images) . 
                         ", Cloudflare only: " . count($existing_images));
                error_log("Existing images for display: " . json_encode($existing_images, JSON_PRETTY_PRINT));
            } else {
                $existing_images = [];
            }
        }
        // Mevcut özellikleri parse et
        $existing_features = [];
        $existing_ic_ozellikler = [];
        $existing_dis_ozellikler = [];
        $existing_muhit_ozellikleri = [];
        $existing_ulasim_ozellikleri = [];
        $existing_manzara_ozellikleri = [];
        $existing_konut_tipi_ozellikleri = [];
        $existing_olanaklar = [];
        if (!empty($existing_property['features'])) {
            $existing_features = json_decode($existing_property['features'], true);
            if (is_array($existing_features)) {
                $existing_ic_ozellikler = $existing_features['ic_ozellikler'] ?? [];
                $existing_dis_ozellikler = $existing_features['dis_ozellikler'] ?? [];
                $existing_muhit_ozellikleri = $existing_features['muhit_ozellikleri'] ?? [];
                $existing_ulasim_ozellikleri = $existing_features['ulasim_ozellikleri'] ?? [];
                $existing_manzara_ozellikleri = $existing_features['manzara_ozellikleri'] ?? [];
                $existing_konut_tipi_ozellikleri = $existing_features['konut_tipi_ozellikleri'] ?? [];
                $existing_olanaklar = $existing_features['olanaklar'] ?? [];
            } else {
                $existing_features = [];
            }
        }
    } else {
        $_SESSION['error'] = "İlan bulunamadı veya düzenleme yetkiniz yok.";
        header("Location: dashboard.php");
        exit;
    }
}
// Default değişkenler (form yükleme için)
$images_string = '[]';
$main_image = '';
// Form işleme
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 🚀 FORM SUBMIT BAŞLANGIÇ ZAMANI
        $formStartTime = microtime(true);
        error_log("🚀 FORM SUBMIT BAŞLADI: İlan ekleme işlemi başlatıldı - " . date('H:i:s'));
        file_put_contents(__DIR__ . '/debug.log', "[" . date('d-M-Y H:i:s T') . "] 🚀 FORM SUBMIT BAŞLADI: İlan ekleme işlemi başlatıldı - " . date('H:i:s') . "\n", FILE_APPEND | LOCK_EX);
        // POST verisi tamamen boşsa
        if (empty($_POST)) {
            throw new Exception("Form verisi alınamadı. Dosya boyutu çok büyük olabilir.");
        }
        // CSRF token kontrolü
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!CSRFTokenManager::validateToken($csrf_token)) {
            throw new Exception("Güvenlik hatası: Geçersiz form token. Lütfen sayfayı yenileyin.");
        }
        // Edit mode kontrolü
        $edit_mode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === '1';
        $edit_id = $edit_mode ? intval($_POST['edit_id'] ?? 0) : null;
        // Debug: Form verilerini logla
        error_log("=== FORM DEBUG ===");
        error_log("Edit mode: " . ($edit_mode ? 'true' : 'false'));
        error_log("Edit ID: " . $edit_id);
        error_log("POST title: " . ($_POST['title'] ?? 'EMPTY'));
        error_log("POST description: " . ($_POST['description'] ?? 'EMPTY'));
        error_log("POST type: " . ($_POST['type'] ?? 'EMPTY'));
        error_log("POST category: " . ($_POST['category'] ?? 'EMPTY'));
        error_log("POST price: " . ($_POST['price'] ?? 'EMPTY'));
        // Form verilerini al
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? '';
        $category_form = $_POST['category'] ?? '';
        $subcategory = trim($_POST['subcategory'] ?? ''); // Alt kategori eklendi
        // Category mapping
        $category_mapping = [
            'konut' => 'apartment',
            'is_yeri' => 'office', 
            'arsa' => 'land',
            'bina' => 'house',
            'devre_mulk' => 'villa',
            'apartment' => 'apartment',
            'house' => 'house',
            'villa' => 'villa',
            'office' => 'office',
            'shop' => 'shop',
            'warehouse' => 'warehouse',
            'land' => 'land'
        ];
        $category = isset($category_mapping[$category_form]) ? $category_mapping[$category_form] : 'apartment';
        $price = floatval(str_replace(['.', ','], ['', '.'], $_POST['price'] ?? '0'));
        // Alan bilgisi - MySQL 8.0 uyumluluğu için default değer kontrolü
        $area_gross = floatval($_POST['area_gross'] ?? 0);
        $area_net = floatval($_POST['area_net'] ?? 0);
        $area = $area_gross > 0 ? $area_gross : ($area_net > 0 ? $area_net : 50); // Default 50 m2
        // Oda ve banyo sayıları - MySQL 8.0 uyumluluğu için default değer kontrolü
        $room_count = intval($_POST['room_count'] ?? 1);
        $bedrooms = intval($_POST['bedrooms'] ?? 1);
        $living_room_count = intval($_POST['living_room_count'] ?? 1);
        $bathrooms = intval($_POST['bathroom_count'] ?? 1);
        // Kat bilgileri - MySQL 8.0 uyumluluğu için kontrol
        $floor_location = trim($_POST['floor_location'] ?? '');
        $floor = !empty($floor_location) ? $floor_location : 'Zemin Kat'; // String olarak bırak
        $building_floors = intval($_POST['building_floors'] ?? 1);
        $building_age = trim($_POST['building_age'] ?? '0');
        $year_built = is_numeric($building_age) ? (date('Y') - intval($building_age)) : intval(date('Y'));
        // Adres bilgileri - MySQL 8.0 uyumluluğu için default değer kontrolü
        $city = trim($_POST['city'] ?? 'İstanbul');
        $district = trim($_POST['district'] ?? '');
        $neighborhood = trim($_POST['neighborhood'] ?? '');
        $location_type = trim($_POST['location_type'] ?? 'standalone'); // Default değer eklendi
        $site_name = trim($_POST['site_name'] ?? '');
        $address_details = trim($_POST['address_details'] ?? '');
        // Konum tipine göre adres oluştur
        if ($location_type === 'site' && !empty($site_name)) {
            $address = $site_name . ' Sitesi';
        } else {
            $address = !empty($address_details) ? $address_details : 'Adres bilgisi girilmemiş';
        }
        $featured = isset($_POST['is_featured']) && $user_data['role'] === 'admin' ? 1 : 0;
        // Heating - ENUM validation (Türkçe karakter olmadan)
        $heating_input = trim($_POST['heating'] ?? '');
        $valid_heating_options = ['Yok', 'Soba', 'Dogalgaz Sobasi', 'Kat Kaloriferi', 'Merkezi Sistem', 'Kombi (Dogalgaz)', 'Kombi (Elektrik)', 'Yerden Isitma', 'Klima', 'Fancoil Unitesi', 'Gunes Enerjisi', 'Jeotermal', 'Somine'];
        // Heating mapping (Türkçe karakterleri düzelt)
        $heating_mapping = [
            'Doğalgaz Sobası' => 'Dogalgaz Sobasi',
            'Yerden Isıtma' => 'Yerden Isitma',
            'Güneş Enerjisi' => 'Gunes Enerjisi',
            'Şömine' => 'Somine'
        ];
        if (isset($heating_mapping[$heating_input])) {
            $heating = $heating_mapping[$heating_input];
        } elseif (in_array($heating_input, $valid_heating_options)) {
            $heating = $heating_input;
        } else {
            $heating = 'Yok';
        }
        // Elevator - ENUM validation
        $elevator_input = trim($_POST['elevator'] ?? '');
        $valid_elevator_options = ['Var', 'Yok'];
        $elevator = in_array($elevator_input, $valid_elevator_options) ? $elevator_input : 'Yok';
        // Parking - ENUM validation (güncellenmiş değerler)
        $parking_input = trim($_POST['parking'] ?? '');
        $valid_parking_options = ['Otopark Yok', 'Acik Otopark', 'Kapali Otopark', 'Otopark Var'];
        // Parking mapping (Türkçe karakterleri düzelt)
        $parking_mapping = [
            'Açık Otopark' => 'Acik Otopark',
            'Kapalı Otopark' => 'Kapali Otopark'
        ];
        if (isset($parking_mapping[$parking_input])) {
            $parking = $parking_mapping[$parking_input];
        } elseif (in_array($parking_input, $valid_parking_options)) {
            $parking = $parking_input;
        } else {
            $parking = 'Otopark Yok';
        }
        $furnished = isset($_POST['furnished']) ? 1 : 0;
        // Usage Status - ENUM validation (MySQL 8.0 uyumlu) - DÜZELTİLMİŞ
        $usage_status_input = trim($_POST['usage_status'] ?? '');
        // Database'deki gerçek ENUM değerleri (Türkçe karakterler olmadan)
        $valid_usage_statuses = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
        // Alternatif mapping - eğer database farklı değerler kullanıyorsa
        $usage_mapping = [
            'Boş' => 'Bos',
            'Bos' => 'Bos', 
            'Empty' => 'Bos',
            'Kiracılı' => 'Kiracili',
            'Kiracili' => 'Kiracili',
            'Tenant' => 'Kiracili',
            'Malik Kullanımında' => 'Malik Kullaniminda',
            'Malik Kullaniminda' => 'Malik Kullaniminda',
            'Owner Occupied' => 'Malik Kullaniminda',
            'Yatırım Amaçlı' => 'Yatirim Amacli',
            'Yatirim Amacli' => 'Yatirim Amacli',
            'Investment' => 'Yatirim Amacli'
        ];
        // Mapping kontrolü
        if (isset($usage_mapping[$usage_status_input])) {
            $usage_status = $usage_mapping[$usage_status_input];
        } elseif (in_array($usage_status_input, $valid_usage_statuses)) {
            $usage_status = $usage_status_input;
        } else {
            $usage_status = 'Bos'; // Default
        }
        // Debug log
        error_log("USAGE_STATUS MAPPING: '$usage_status_input' -> '$usage_status'");
        // Debug log for all ENUM inputs
        error_log("=== ENUM INPUT VALUES ===");
        error_log("heating_input: '" . $heating_input . "' -> final: '" . $heating . "'");
        error_log("elevator_input: '" . $elevator_input . "' -> final: '" . $elevator . "'");
        error_log("parking_input: '" . $parking_input . "' -> final: '" . $parking . "'");
        error_log("usage_status_input: '" . $usage_status_input . "' -> final: '" . $usage_status . "'");
        $dues = floatval($_POST['dues'] ?? 0);
        $credit_eligible = isset($_POST['credit_eligible']) ? 1 : 0;
        // Deed Status - ENUM validation
        $deed_status_input = trim($_POST['deed_status'] ?? '');
        $valid_deed_statuses = ['Kat Mulkiyeti', 'Kat Irtifaki', 'Arsa Payi', 'Mustakil Tapu'];
        // Deed mapping (Türkçe karakterleri düzelt)
        $deed_mapping = [
            'Kat İrtifakı' => 'Kat Irtifaki',
            'Arsa Payı' => 'Arsa Payi'
        ];
        if (isset($deed_mapping[$deed_status_input])) {
            $deed_status = $deed_mapping[$deed_status_input];
        } elseif (in_array($deed_status_input, $valid_deed_statuses)) {
            $deed_status = $deed_status_input;
        } else {
            $deed_status = 'Kat Mulkiyeti';
        }
        // Exchange validation - ENUM validation
        $exchange_input = trim($_POST['exchange'] ?? '');
        $valid_exchange_options = ['Evet', 'Hayir'];
        // Exchange mapping (Türkçe karakterleri düzelt)
        $exchange_mapping = [
            'Hayır' => 'Hayir'
        ];
        if (isset($exchange_mapping[$exchange_input])) {
            $exchange = $exchange_mapping[$exchange_input];
        } elseif (in_array($exchange_input, $valid_exchange_options)) {
            $exchange = $exchange_input;
        } else {
            $exchange = 'Hayir';
        }
        // Additional ENUM field validation
        $building_floors_input = trim($_POST['building_floors'] ?? '');
        $building_floors = !empty($building_floors_input) ? $building_floors_input : '1';
        $floor_input = trim($_POST['floor_location'] ?? '');
        $floor = !empty($floor_input) ? $floor_input : 'Zemin Kat';
        $building_age_input = trim($_POST['building_age'] ?? '');
        $building_age = !empty($building_age_input) ? $building_age_input : '0';
        // Debug log for additional fields
        error_log("deed_status_input: '" . $deed_status_input . "' -> final: '" . $deed_status . "'");
        error_log("exchange_input: '" . $exchange_input . "' -> final: '" . $exchange . "'");
        error_log("building_floors_input: '" . $building_floors_input . "' -> final: '" . $building_floors . "'");
        error_log("floor_input: '" . $floor_input . "' -> final: '" . $floor . "'");
        error_log("building_age_input: '" . $building_age_input . "' -> final: '" . $building_age . "'");
        error_log("========================");
        // Özellikler - POST verilerini al ve JSON olarak kaydet
        $ic_ozellikler = isset($_POST['ic_ozellikler']) ? $_POST['ic_ozellikler'] : [];
        $dis_ozellikler = isset($_POST['dis_ozellikler']) ? $_POST['dis_ozellikler'] : [];
        $muhit_ozellikleri = isset($_POST['muhit_ozellikleri']) ? $_POST['muhit_ozellikleri'] : [];
        $ulasim_ozellikleri = isset($_POST['ulasim_ozellikleri']) ? $_POST['ulasim_ozellikleri'] : [];
        $manzara_ozellikleri = isset($_POST['manzara_ozellikleri']) ? $_POST['manzara_ozellikleri'] : [];
        $konut_tipi_ozellikleri = isset($_POST['konut_tipi_ozellikleri']) ? $_POST['konut_tipi_ozellikleri'] : [];
        $olanaklar = isset($_POST['olanaklar']) ? $_POST['olanaklar'] : [];
        // Özellikleri JSON formatında hazırla
        $features = [
            'ic_ozellikler' => $ic_ozellikler,
            'dis_ozellikler' => $dis_ozellikler,
            'muhit_ozellikleri' => $muhit_ozellikleri,
            'ulasim_ozellikleri' => $ulasim_ozellikleri,
            'manzara_ozellikleri' => $manzara_ozellikleri,
            'konut_tipi_ozellikleri' => $konut_tipi_ozellikleri,
            'olanaklar' => $olanaklar
        ];
        $features_json = json_encode($features, JSON_UNESCAPED_UNICODE);
        // Validation - Detaylı kontrol
        $validation_errors = [];
        if (empty($title)) {
            $validation_errors[] = "Başlık";
        }
        if (empty($description)) {
            $validation_errors[] = "Açıklama";
        }
        if (empty($type) || !in_array($type, ['sale', 'rent', 'daily_rent', 'transfer_sale', 'transfer_rent'])) {
            $validation_errors[] = "İlan Tipi (Satılık/Kiralık)";
        }
        if (empty($category)) {
            $validation_errors[] = "Kategori";
        }
        if (!is_numeric($price) || $price <= 0) {
            $validation_errors[] = "Fiyat (Geçerli bir sayı giriniz)";
        }
        if (!empty($validation_errors)) {
            $error_message = "Lütfen şu alanları kontrol edin: " . implode(", ", $validation_errors);
            error_log("Validation errors: " . $error_message);
            error_log("Form data: title='$title', description='$description', type='$type', category='$category', price='$price'");
            throw new Exception($error_message);
        }
        // Resim yükleme - CLOUDFLARE IMAGES ENTEGRASYONU
        $images_string = '[]';
        $main_image = '';
        // Edit mode: Handle updated existing images
        $existing_images_array = [];
        if ($edit_mode && !empty($_POST['updated_existing_images'])) {
            $updated_existing_images = json_decode($_POST['updated_existing_images'], true);
            if (is_array($updated_existing_images)) {
                $existing_images_array = $updated_existing_images;
                error_log("Updated existing images: " . print_r($existing_images_array, true));
            }
        }
        // DEBUG: Form submission logging
        error_log("📤 FORM PROCESSING START - Files count: " . (isset($_FILES['property_images']['name']) ? count(array_filter($_FILES['property_images']['name'])) : 0));
        if (isset($_FILES['property_images']['name'])) {
            $fileNames = array_filter($_FILES['property_images']['name']);
            error_log("📸 Files to upload: " . implode(", ", $fileNames));
        }
        // Cloudflare Images Processing - Pass $_FILES directly
        $cloudflareResult = processPropertyImages($_FILES['property_images'] ?? [], $edit_id ?? null, $edit_mode, $user_data['role'] ?? 'user');
        // Set image variables from Cloudflare result
        $images_string = $cloudflareResult['images_string'];
        $main_image = $cloudflareResult['main_image'];
        $cloudflare_images_json = isset($cloudflareResult['cloudflare_images']) ? json_encode($cloudflareResult['cloudflare_images']) : '[]';
        $cloudflare_main_image = $cloudflareResult['cloudflare_main_image'] ?? '';
        $use_cloudflare = $cloudflareResult['use_cloudflare'] ? 1 : 0;
        error_log("Cloudflare variables set:");
        error_log("- images_string: " . $images_string);
        error_log("- main_image: " . $main_image);
        error_log("- cloudflare_images_json: " . $cloudflare_images_json);
        error_log("- cloudflare_main_image: " . $cloudflare_main_image);
        error_log("- use_cloudflare: " . $use_cloudflare);
        error_log("Final Cloudflare result - Images: $images_string, Main: $main_image");
        // Database insert/update
        $listing_type_mapping = [
            'sale' => 'Satılık',
            'rent' => 'Kiralık'
        ];
        $listing_type = isset($listing_type_mapping[$type]) ? $listing_type_mapping[$type] : 'Satılık';
        if ($edit_mode && $edit_id) {
            // UPDATE mode - CLOUDFLARE FIELDS ADDED
            error_log("=== EDIT MODE DEBUG START ===");
            error_log("Edit ID: " . $edit_id);
            error_log("User ID: " . $user_id);
            error_log("User Role: " . $user_data['role']);
            // Validate all variables before binding
            $variables_to_check = [
                'title' => $title ?? '',
                'description' => $description ?? '',
                'price' => $price ?? 0,
                'type' => $type ?? 'rent',
                'category' => $category ?? 'apartment',
                'subcategory' => $subcategory ?? '',
                'listing_type' => $listing_type ?? 'Satılık',
                'area_gross' => $area_gross ?? 0,
                'area_net' => $area_net ?? 0,
                'area' => $area ?? 50,
                'address' => $address ?? '',
                'city' => $city ?? 'İstanbul',
                'district' => $district ?? '',
                'neighborhood' => $neighborhood ?? '',
                'room_count' => $room_count ?? 1,
                'bedrooms' => $bedrooms ?? 1,
                'living_room_count' => $living_room_count ?? 1,
                'bathrooms' => $bathrooms ?? 1,
                'floor' => $floor ?? 'Zemin Kat',
                'building_floors' => $building_floors ?? 1,
                'year_built' => $year_built ?? date('Y'),
                'building_age' => $building_age ?? '0',
                'heating' => $heating ?? 'Yok',
                'elevator' => $elevator ?? 'Yok',
                'parking' => $parking ?? 'Otopark Yok',
                'furnished' => $furnished ?? 0,
                'usage_status' => $usage_status ?? 'Bos',
                'dues' => $dues ?? 0,
                'credit_eligible' => $credit_eligible ?? 1,
                'deed_status' => $deed_status ?? 'Kat Mulkiyeti',
                'exchange' => $exchange ?? 'Hayir',
                'location_type' => $location_type ?? 'standalone',
                'featured' => $featured ?? 0,
                'images_string' => $images_string ?? '[]',
                'main_image' => $main_image ?? '',
                'cloudflare_images_json' => $cloudflare_images_json ?? '[]',
                'cloudflare_main_image' => $cloudflare_main_image ?? '',
                'use_cloudflare' => $use_cloudflare ?? 0,
                'features_json' => $features_json ?? '{}',
                'edit_id' => $edit_id ?? 0,
                'user_id' => $user_id ?? 0,
                'user_role' => $user_data['role'] ?? 'user'
            ];
            foreach ($variables_to_check as $var_name => $var_value) {
                if ($var_value === null) {
                    error_log("Warning: Variable $var_name is null");
                } else if (!isset($variables_to_check[$var_name])) {
                    error_log("ERROR: Variable $var_name is not set!");
                } else {
                    error_log("OK: Variable $var_name = " . (is_string($var_value) ? substr($var_value, 0, 20) : $var_value));
                }
            }
            $query = "UPDATE properties SET 
                title = ?,
                description = ?,
                price = ?,
                type = ?,
                category = ?,
                subcategory = ?,
                listing_type = ?,
                area_gross = ?,
                area_net = ?,
                area = ?,
                address = ?,
                city = ?,
                district = ?,
                neighborhood = ?,
                room_count = ?,
                bedrooms = ?,
                living_room_count = ?,
                bathrooms = ?,
                floor = ?,
                building_floors = ?,
                year_built = ?,
                building_age = ?,
                heating = ?,
                elevator = ?,
                parking = ?,
                furnished = ?,
                usage_status = ?,
                dues = ?,
                credit_eligible = ?,
                deed_status = ?,
                exchange = ?,
                location_type = ?,
                featured = ?,
                images = ?,
                main_image = ?,
                cloudflare_images = ?,
                cloudflare_main_image = ?,
                use_cloudflare = ?,
                features = ?,
                updated_at = NOW()
                WHERE id = ? AND (user_id = ? OR BINARY ? = 'admin')";
            $stmt = $conn->prepare($query);
            // Debug: Count actual parameters
            $type_string = "ssdssssdddssssiiiisissssssisdissssisssissi";
            error_log("Type string length: " . strlen($type_string));
            error_log("Type string: " . $type_string);
            error_log("Actual parameter count: 42");
            // Count parameters passed to bind_param
            $params = [
                $variables_to_check['title'], $variables_to_check['description'], $variables_to_check['price'], 
                $variables_to_check['type'], $variables_to_check['category'], $variables_to_check['subcategory'],
                $variables_to_check['listing_type'], $variables_to_check['area_gross'], $variables_to_check['area_net'], 
                $variables_to_check['area'], $variables_to_check['address'], $variables_to_check['city'], 
                $variables_to_check['district'], $variables_to_check['neighborhood'],
                $variables_to_check['room_count'], $variables_to_check['bedrooms'], $variables_to_check['living_room_count'], 
                $variables_to_check['bathrooms'], $variables_to_check['floor'], $variables_to_check['building_floors'], 
                $variables_to_check['year_built'], $variables_to_check['building_age'],
                $variables_to_check['heating'], $variables_to_check['elevator'], $variables_to_check['parking'], 
                $variables_to_check['furnished'], $variables_to_check['usage_status'], $variables_to_check['dues'], 
                $variables_to_check['credit_eligible'],
                $variables_to_check['deed_status'], $variables_to_check['exchange'], $variables_to_check['location_type'], 
                $variables_to_check['featured'],
                $variables_to_check['images_string'], $variables_to_check['main_image'], $variables_to_check['cloudflare_images_json'], 
                $variables_to_check['cloudflare_main_image'], $variables_to_check['use_cloudflare'], $variables_to_check['features_json'],
                $variables_to_check['edit_id'], $variables_to_check['user_id'], $variables_to_check['user_role']
            ];
            error_log("Parameter array count: " . count($params));
            // Use validated variables for binding - CORRECTED TYPE STRING (42 chars for 42 params)
            $result = $stmt->bind_param("ssdssssdddssssiiiisissssssisdissssisssissi", 
                $variables_to_check['title'], $variables_to_check['description'], $variables_to_check['price'], 
                $variables_to_check['type'], $variables_to_check['category'], $variables_to_check['subcategory'],
                $variables_to_check['listing_type'], $variables_to_check['area_gross'], $variables_to_check['area_net'], 
                $variables_to_check['area'], $variables_to_check['address'], $variables_to_check['city'], 
                $variables_to_check['district'], $variables_to_check['neighborhood'],
                $variables_to_check['room_count'], $variables_to_check['bedrooms'], $variables_to_check['living_room_count'], 
                $variables_to_check['bathrooms'], $variables_to_check['floor'], $variables_to_check['building_floors'], 
                $variables_to_check['year_built'], $variables_to_check['building_age'],
                $variables_to_check['heating'], $variables_to_check['elevator'], $variables_to_check['parking'], 
                $variables_to_check['furnished'], $variables_to_check['usage_status'], $variables_to_check['dues'], 
                $variables_to_check['credit_eligible'],
                $variables_to_check['deed_status'], $variables_to_check['exchange'], $variables_to_check['location_type'], 
                $variables_to_check['featured'],
                $variables_to_check['images_string'], $variables_to_check['main_image'], $variables_to_check['cloudflare_images_json'], 
                $variables_to_check['cloudflare_main_image'], $variables_to_check['use_cloudflare'], $variables_to_check['features_json'],
                $variables_to_check['edit_id'], $variables_to_check['user_id'], $variables_to_check['user_role']);
            if (!$result) {
                error_log("bind_param failed: " . $stmt->error);
                throw new Exception("Parameter binding failed: " . $stmt->error);
            }
            if ($stmt->execute()) {
                $_SESSION['success'] = "İlan başarıyla güncellendi! (ID: " . $edit_id . ")";
                header("Location: dashboard.php");
                exit;
            } else {
                throw new Exception("Güncelleme hatası: " . $stmt->error);
            }
        } else {
            // INSERT mode - CLOUDFLARE FIELDS ADDED
            error_log("🔄 DATABASE INSERT işlemi başlıyor - Property kaydetme");
            $dbStartTime = microtime(true);
       $query = "INSERT INTO properties SET 
            user_id = ?,
            title = ?,
            description = ?,
            price = ?,
            type = ?,
            category = ?,
            subcategory = ?,
            listing_type = ?,
            area_gross = ?,
            area_net = ?,
            area = ?,
            address = ?,
            city = ?,
            district = ?,
            neighborhood = ?,
            room_count = ?,
            bedrooms = ?,
            living_room_count = ?,
            bathrooms = ?,
            floor = ?,
            building_floors = ?,
            year_built = ?,
            building_age = ?,
            heating = ?,
            elevator = ?,
            parking = ?,
            furnished = ?,
            usage_status = ?,
            dues = ?,
            credit_eligible = ?,
            deed_status = ?,
            exchange = ?,
            location_type = ?,
            featured = ?,
            images = ?,
            main_image = ?,
            cloudflare_images = ?,
            cloudflare_main_image = ?,
            use_cloudflare = ?,
            features = ?,
            status = 'active',
            created_at = NOW()";
            // ENUM validation before insert
            error_log("=== ENUM VALUES VALIDATION ===");
            error_log("usage_status: '" . $usage_status . "'");
            error_log("parking: '" . $parking . "'");
            error_log("deed_status: '" . $deed_status . "'");
            error_log("exchange: '" . $exchange . "'");
            error_log("heating: '" . $heating . "'");
            error_log("elevator: '" . $elevator . "'");
            error_log("===========================");
            // Log all parameters for debugging
            error_log("=== ALL PARAMETERS ===");
            error_log("user_id: " . $user_id);
            error_log("title: '" . $title . "'");
            error_log("description: '" . substr($description, 0, 50) . "...'");
            error_log("price: " . $price);
            error_log("type: '" . $type . "'");
            error_log("category: '" . $category . "'");
            error_log("subcategory: '" . $subcategory . "'");
            error_log("listing_type: '" . $listing_type . "'");
            error_log("area_gross: " . $area_gross);
            error_log("area_net: " . $area_net);
            error_log("area: " . $area);
            error_log("address: '" . $address . "'");
            error_log("city: '" . $city . "'");
            error_log("district: '" . $district . "'");
            error_log("neighborhood: '" . $neighborhood . "'");
            error_log("room_count: " . $room_count);
            error_log("bedrooms: " . $bedrooms);
            error_log("living_room_count: " . $living_room_count);
            error_log("bathrooms: " . $bathrooms);
            error_log("floor: '" . $floor . "'");
            error_log("building_floors: '" . $building_floors . "'");
            error_log("year_built: " . $year_built);
            error_log("building_age: '" . $building_age . "'");
            error_log("furnished: " . $furnished);
            error_log("dues: " . $dues);
            error_log("credit_eligible: " . $credit_eligible);
            error_log("location_type: '" . $location_type . "'");
            error_log("featured: " . $featured);
            error_log("=======================");
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            // Cloudflare variables for insert
            $cloudflare_images_json = isset($cloudflareResult['cloudflare_images']) ? json_encode($cloudflareResult['cloudflare_images']) : '[]';
            $cloudflare_main_image = $cloudflareResult['cloudflare_main_image'] ?? '';
            $use_cloudflare = $cloudflareResult['use_cloudflare'] ? 1 : 0;
            // Count parameters: 40 parameters total
            // 1. user_id, 2. title, 3. description, 4. price, 5. type, 6. category, 7. subcategory,
            // 8. listing_type, 9. area_gross, 10. area_net, 11. area, 12. address, 13. city, 14. district, 15. neighborhood
            // 16. room_count, 17. bedrooms, 18. living_room_count, 19. bathrooms, 20. floor, 21. building_floors, 
            // 22. year_built, 23. building_age, 24. heating, 25. elevator, 26. parking, 27. furnished, 
            // 28. usage_status, 29. dues, 30. credit_eligible, 31. deed_status, 32. exchange, 33. location_type, 
            // 34. featured, 35. images, 36. main_image, 37. cloudflare_images, 38. cloudflare_main_image, 
            // 39. use_cloudflare, 40. features
            $bind_result = $stmt->bind_param("issdssssdddssssiiiisisssssisdisssisssiss", 
                $user_id, $title, $description, $price, $type, $category, $subcategory,
                $listing_type, $area_gross, $area_net, $area, $address, $city, $district, $neighborhood,
                $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
                $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
                $deed_status, $exchange, $location_type, $featured,
                $images_string, $main_image, $cloudflare_images_json, $cloudflare_main_image, $use_cloudflare, $features_json);
            if (!$bind_result) {
                throw new Exception("Bind param failed: " . $stmt->error);
            }
            if ($stmt->execute()) {
                $property_id = $conn->insert_id;
                $dbTime = round((microtime(true) - $dbStartTime) * 1000, 2);
                error_log("✅ DATABASE INSERT başarılı - Property ID: $property_id, süre: {$dbTime}ms");
                // Create thumbnails after successful property insertion
                if (!empty($finalCloudflareImages)) {
                    error_log("Creating thumbnails for new property ID: $property_id");
                    $thumbStartTime = microtime(true);
                    foreach ($finalCloudflareImages as $index => $cloudflareId) {
                        try {
                            createLocalThumbnail($cloudflareId, $property_id, $index);
                            error_log("Thumbnail created for image $index of property $property_id");
                        } catch (Exception $e) {
                            error_log("⚠️ Thumbnail creation failed for image $index: " . $e->getMessage());
                        }
                    }
                    $thumbTime = round((microtime(true) - $thumbStartTime) * 1000, 2);
                    error_log("🖼️ Thumbnail creation completed in {$thumbTime}ms");
                }
                $totalTime = round((microtime(true) - $dbStartTime) * 1000, 2);
                error_log("🎯 TOTAL PROPERTY CREATION TIME: {$totalTime}ms");
                $_SESSION['success'] = "İlan başarıyla eklendi! (ID: " . $property_id . ")";
                // 🔄 REDIRECT BAŞLANGIÇ ZAMANI
                $redirectStartTime = microtime(true);
                error_log("🔄 REDIRECT BAŞLADI: Dashboard'a yönlendiriliyor - " . date('H:i:s'));
                file_put_contents(__DIR__ . '/debug.log', "[" . date('d-M-Y H:i:s T') . "] 🔄 REDIRECT BAŞLADI: Dashboard'a yönlendiriliyor - " . date('H:i:s') . "\n", FILE_APPEND | LOCK_EX);
                header("Location: dashboard.php");
                exit;
            } else {
                throw new Exception("Database hatası: " . $stmt->error);
            }
        }
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>
            <i class='fas fa-exclamation-circle me-2'></i>
            <strong>Hata:</strong> " . $e->getMessage() . "
        </div>";
    }
}
// Türkiye şehirleri
$turkish_cities = [
    'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Amasya', 'Ankara', 'Antalya', 'Artvin',
    'Aydın', 'Balıkesir', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa',
    'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Edirne', 'Elazığ', 'Erzincan',
    'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkâri', 'Hatay', 'Isparta',
    'İçel (Mersin)', 'İstanbul', 'İzmir', 'Kars', 'Kastamonu', 'Kayseri', 'Kırklareli', 'Kırşehir',
    'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Kahramanmaraş', 'Mardin', 'Muğla',
    'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Rize', 'Sakarya', 'Samsun', 'Siirt',
    'Sinop', 'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Şanlıurfa', 'Uşak',
    'Van', 'Yozgat', 'Zonguldak', 'Aksaray', 'Bayburt', 'Karaman', 'Kırıkkale', 'Batman',
    'Şırnak', 'Bartın', 'Ardahan', 'Iğdır', 'Yalova', 'Karabük', 'Kilis', 'Osmaniye', 'Düzce'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'İlan Düzenle' : 'Yeni İlan Ekle' ?> - Gökhan Aydınlı Real Estate</title>
    <!-- CSS Files - CDN öncelikli -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Dashboard CSS - Optional (hata verirse yorum satırı yap) -->
    <?php if (file_exists("../assets/dashboard-style.css")): ?>
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    <?php else: ?>
    <!-- dashboard-style.css bulunamadı -->
    <?php endif; ?>
    <?php if (file_exists("includes/dashboard-common.css")): ?>
    <link rel="stylesheet" href="includes/dashboard-common.css">
    <?php else: ?>
    <!-- dashboard-common.css bulunamadı -->
    <?php endif; ?>
    <style>
        /* Dashboard Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        .main-content {
            padding: 30px;
        }
        .mobile-header {
            display: none;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        /* Wizard Styles */
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0.5;
            transition: all 0.3s ease;
        }
        .step.active {
            opacity: 1;
        }
        .step.completed {
            opacity: 1;
            color: #198754;
        }
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: white;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        .step.active .step-number {
            border-color: #0d6efd;
            background: #0d6efd;
            color: white;
            transform: scale(1.1);
        }
        .step.completed .step-number {
            border-color: #198754;
            background: #198754;
            color: white;
        }
        .step-title {
            margin-top: 0.7rem;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .step-line {
            width: 80px;
            height: 3px;
            background: #dee2e6;
            margin: 0 1rem;
            border-radius: 2px;
        }
        .step.completed + .step-line {
            background: #198754;
        }
        .category-grid, .transaction-grid, .subcategory-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 3rem auto;
            max-width: 750px;
        }
        .transaction-grid {
            grid-template-columns: repeat(4, 1fr);
            max-width: 900px;
        }
        .category-item, .transaction-item, .subcategory-item {
            border: 3px solid #dee2e6;
            border-radius: 20px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            user-select: none;
        }
        .category-item:hover, .transaction-item:hover, .subcategory-item:hover {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.25);
        }
        .category-item.selected, .transaction-item.selected, .subcategory-item.selected {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }
        .category-item i, .transaction-item i, .subcategory-item i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: block;
            opacity: 0.8;
        }
        .category-item span, .transaction-item span, .subcategory-item span {
            font-weight: 600;
            font-size: 1.2rem;
            line-height: 1.3;
        }
        .wizard-step {
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
        }
        /* Featured Property Checkbox Styles */
        .featured-checkbox {
            transform: scale(1.4);
            accent-color: #ffc107;
            margin-right: 0.75rem;
        }
        .featured-label {
            cursor: pointer;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 2px solid #ffc107;
            display: block;
            margin-top: 0.5rem;
        }
        .featured-label:hover {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        .featured-checkbox:checked + .featured-label {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        .card.border-warning {
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
            transition: all 0.3s ease;
        }
        .card.border-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
        }
        /* Form Styles */
        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        /* Capitalize Input Style */
        .capitalize-input {
            text-transform: capitalize;
        }
        .alert-success {
            background: linear-gradient(135deg, #d1edff 0%, #a8e6cf 100%);
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        /* Photo Upload Styles */
        .photo-gallery-container {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 30px;
        }
        .upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            padding: 50px 20px;
            text-align: center;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover,
        .upload-area.dragover {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.05);
            transform: translateY(-2px);
        }
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            animation: photoFadeIn 0.4s ease-out;
        }
        .photo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .photo-item.main-photo {
            border-color: #ffd700;
        }
        .photo-item.main-photo::before {
            content: "ANA";
            position: absolute;
            top: 5px;
            left: 5px;
            background: #ffd700;
            color: #000;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
            z-index: 2;
        }
        .photo-preview-container {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        .photo-actions {
            display: flex;
            gap: 10px;
        }
        .photo-action-btn {
            background: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .photo-action-btn:hover {
            transform: scale(1.1);
        }
        .photo-action-btn.delete {
            background: #dc3545;
            color: white;
        }
        .photo-action-btn.main {
            background: #ffd700;
            color: #000;
        }
        .photo-number {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        @keyframes photoFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        /* Existing Photos Styles */
        .existing-photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .existing-photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            border: 2px solid #28a745;
            transition: all 0.3s ease;
            animation: photoFadeIn 0.4s ease-out;
            max-width: 150px; /* Thumbnail max size */
        }
        .existing-photo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        .existing-photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .main-photo-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: #ffd700;
            color: #000;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
            z-index: 2;
        }
        .photo-number {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        /* Photo Controls */
        .photo-controls {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .existing-photo-item:hover .photo-controls {
            opacity: 1;
        }
        .btn-make-main,
        .btn-remove-photo {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        .btn-make-main:hover {
            background: #ffd700;
            color: #000;
            transform: scale(1.1);
        }
        .btn-remove-photo:hover {
            background: #dc3545;
            transform: scale(1.1);
        }
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            .mobile-header {
                display: flex;
            }
            .main-content {
                padding: 20px;
            }
            .category-grid, .transaction-grid, .subcategory-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            .photos-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
            }
        }
        @media (max-width: 480px) {
            .category-grid, .transaction-grid, .subcategory-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
        /* CLOUDFLARE IMAGES STYLES */
        .cloudflare-status {
            background: rgba(247, 147, 30, 0.1);
            border: 1px solid rgba(247, 147, 30, 0.3);
            border-radius: 10px;
            padding: 10px;
            margin: 10px 0;
        }
        .cloudflare-icon {
            color: #f7931e;
        }
        .cloudflare-indicator .fa-circle.text-success {
            color: #28a745 !important;
            animation: pulse 2s infinite;
        }
        .cloudflare-indicator .fa-circle.text-warning {
            color: #ffc107 !important;
            animation: pulse 1s infinite;
        }
        .cloudflare-indicator .fa-circle.text-danger {
            color: #dc3545 !important;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .upload-progress {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
        }
        .progress-bar {
            background: linear-gradient(45deg, #f7931e, #ff6b35);
            border-radius: 10px;
        }
        .cloudflare-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: linear-gradient(45deg, #f7931e, #ff6b35);
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .photo-item.cloudflare {
            border-color: #f7931e;
            box-shadow: 0 2px 10px rgba(247, 147, 30, 0.2);
        }
        .photo-item.cloudflare:hover {
            box-shadow: 0 4px 20px rgba(247, 147, 30, 0.3);
        }
        .photo-item.local-photo {
            border-color: #28a745;
            box-shadow: 0 2px 10px rgba(40, 167, 69, 0.2);
        }
        .photo-item.local-photo:hover {
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.3);
        }
        .existing-photo-item.local-photo {
            border-color: #28a745;
        }
        /* INDIVIDUAL PHOTO PROGRESS BARS */
        .upload-progress-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        .upload-progress-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .progress-circle {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .circular-chart {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        .circle-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.1);
            stroke-width: 4;
        }
        .circle {
            fill: none;
            stroke: #f7931e;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 251.2; /* 2 * PI * 40 */
            stroke-dashoffset: 251.2;
            transition: stroke-dashoffset 0.3s ease;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        .upload-status {
            color: white;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            min-height: 20px;
        }
        .upload-filename {
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            font-size: 12px;
            max-width: 140px;
            word-break: break-word;
            line-height: 1.3;
        }
        .file-size {
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            margin-top: 4px;
        }
        /* Progress Animation */
        @keyframes progressPulse {
            0% { 
                stroke: #f7931e;
                filter: brightness(1);
            }
            50% { 
                stroke: #ff6b35;
                filter: brightness(1.2);
            }
            100% { 
                stroke: #f7931e;
                filter: brightness(1);
            }
        }
        .circle.uploading {
            animation: progressPulse 2s ease-in-out infinite;
        }
        .circle.completed {
            stroke: #28a745;
            animation: none;
        }
        /* Success Checkmark */
        .success-checkmark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #28a745;
            font-size: 24px;
            opacity: 0;
            animation: checkmarkAppear 0.5s ease-out forwards;
        }
        @keyframes checkmarkAppear {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        /* Error State */
        .circle.error {
            stroke: #dc3545;
            animation: none;
        }
        .error-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #dc3545;
            font-size: 24px;
            opacity: 0;
            animation: errorAppear 0.5s ease-out forwards;
        }
        @keyframes errorAppear {
            0% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.5);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        /* Upload Status Colors */
        .upload-status.connecting { color: #ffc107; }
        .upload-status.uploading { color: #f7931e; }
        .upload-status.processing { color: #17a2b8; }
        .upload-status.completing { color: #28a745; }
        .upload-status.completed { color: #28a745; }
        .upload-status.error { color: #dc3545; }
        /* Upload Progress List Styles */
        .upload-progress-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        .progress-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .progress-item:last-child {
            margin-bottom: 0;
        }
        .progress-item.uploading {
            border-color: #fd7e14;
            background: linear-gradient(90deg, #fff3e0 0%, #ffffff 100%);
        }
        .progress-item.waiting {
            border-color: #6c757d;
            background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
        }
        .progress-item.completed {
            border-color: #198754;
            background: linear-gradient(90deg, #e8f5e8 0%, #ffffff 100%);
        }
        .progress-item.error {
            border-color: #dc3545;
            background: linear-gradient(90deg, #fde8e8 0%, #ffffff 100%);
        }
        .progress-item-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        .progress-item-thumb {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            object-fit: cover;
            margin-right: 10px;
            border: 1px solid #dee2e6;
        }
        .progress-item-details {
            flex: 1;
        }
        .progress-item-name {
            font-size: 13px;
            font-weight: 500;
            color: #212529;
            margin-bottom: 2px;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .progress-item-size {
            font-size: 11px;
            color: #6c757d;
        }
        .progress-item-status {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }
        .progress-item-percentage {
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
            min-width: 35px;
            text-align: right;
        }
        .progress-item-icon {
            font-size: 14px;
            margin-left: 5px;
        }
        .progress-item.uploading .progress-item-percentage { color: #fd7e14; }
        .progress-item.waiting .progress-item-percentage { color: #6c757d; }
        .progress-item.completed .progress-item-percentage { color: #198754; }
        .progress-item.error .progress-item-percentage { color: #dc3545; }
        .progress-item.uploading .progress-item-icon { color: #fd7e14; }
        .progress-item.waiting .progress-item-icon { color: #6c757d; }
        .progress-item.completed .progress-item-icon { color: #198754; }
        .progress-item.error .progress-item-icon { color: #dc3545; }
    </style>
    <!-- İstanbul İlçe ve Mahalle Veri Sistemi - Early Load -->
    <!-- Location manager will be loaded with other modules at the end -->
</head>
<body class="admin-dashboard">
    <!-- Include Admin Sidebar -->
    <?php 
    $current_page = 'add-property';
    $user_name = $user_data['name'];
    if (file_exists('includes/sidebar-admin.php')) {
        try {
            include 'includes/sidebar-admin.php';
            error_log("Sidebar included successfully");
        } catch (Exception $e) {
            error_log("Sidebar include error: " . $e->getMessage());
            error_log("Sidebar include failed: " . $e->getMessage());
        }
    } else {
        error_log("Sidebar dosyası bulunamadı: includes/sidebar-admin.php");
        // Basit alternatif sidebar
        echo '<div class="alert alert-warning">Sidebar dosyası bulunamadı</div>';
    }
    ?>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay"></div>
    <!-- Dashboard Body -->
    <div class="dashboard-body">
        <!-- Mobile Header -->
        <div class="mobile-header d-block d-md-none">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mobile-title"><?= $edit_mode ? 'İlan Düzenle' : 'Yeni İlan Ekle' ?></h5>
            <a href="../logout.php" class="mobile-logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">
                    <i class="fas fa-<?= $edit_mode ? 'edit' : 'plus-circle' ?> me-3"></i><?= $edit_mode ? 'İlan Düzenle' : 'Yeni İlan Ekle' ?>
                </h2>
                <p class="welcome-subtitle">Emlak ilanınızı detaylı bilgilerle oluşturun</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="dashboard.php" class="btn-secondary-custom">
                        <i class="fas fa-arrow-left me-2"></i>Panele Dön
                    </a>
                </div>
            </div>
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php echo $message; ?>
            <form method="POST" enctype="multipart/form-data" id="propertyForm">
                <?php echo CSRFTokenManager::getTokenField(); ?>
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="edit_mode" value="1">
                    <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                    <input type="hidden" name="updated_existing_images" id="updatedExistingImages" value="">
                <?php endif; ?>
                <!-- Edit Mode Alert -->
                <?php if ($edit_mode): ?>
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>İlan Düzenleme Modu:</strong> Mevcut ilan bilgilerini güncelliyorsunuz.
                    </div>
                <?php endif; ?>
                <!-- Step Indicator -->
                <div class="row mb-4" <?= $edit_mode ? 'style="display: none !important;"' : '' ?>>
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="step-indicator">
                                <div class="step active" id="step-1">
                                    <span class="step-number">1</span>
                                    <span class="step-title">Kategori</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-2">
                                    <span class="step-number">2</span>
                                    <span class="step-title">İşlem Türü</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-3">
                                    <span class="step-number">3</span>
                                    <span class="step-title">Alt Kategori</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Step 1: Category Selection -->
                <div class="wizard-step" id="wizard-step-1" <?= $edit_mode ? 'style="display: none !important;"' : '' ?>>
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-house-door"></i> Kategori Seçimi</h4>
                                    <p class="text-muted">Eklemek istediğiniz emlak kategorisini seçin</p>
                                </div>
                                <div class="category-grid">
                                    <div class="category-item" data-category="konut">
                                        <i class="bi bi-house"></i>
                                        <span>Konut</span>
                                    </div>
                                    <div class="category-item" data-category="is_yeri">
                                        <i class="bi bi-building"></i>
                                        <span>İş Yeri</span>
                                    </div>
                                    <div class="category-item" data-category="bina">
                                        <i class="bi bi-buildings"></i>
                                        <span>Bina</span>
                                    </div>
                                    <div class="category-item" data-category="arsa">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Arsa</span>
                                    </div>
                                    <div class="category-item" data-category="devre_mulk">
                                        <i class="bi bi-calendar-check"></i>
                                        <span>Devre Mülk</span>
                                    </div>
                                    <div class="category-item" data-category="turistik_tesis">
                                        <i class="bi bi-compass"></i>
                                        <span>Turistik Tesis</span>
                                    </div>
                                </div>
                                <input type="hidden" id="category" name="category" required 
                                       value="<?php echo htmlspecialchars($_POST['category'] ?? ($edit_mode && isset($existing_property['category']) ? $existing_property['category'] : '')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Step 2: Transaction Type -->
                <div class="wizard-step" id="wizard-step-2" style="display: none;<?= $edit_mode ? ' display: none !important;' : '' ?>">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-arrow-left-right"></i> İşlem Türü Seçimi</h4>
                                    <p class="text-muted">Emlak için işlem türünü seçin</p>
                                </div>
                                <div class="transaction-grid" id="transaction-options">
                                    <!-- Transaction types will be loaded dynamically -->
                                </div>
                                <input type="hidden" id="type" name="type" required 
                                       value="<?php echo htmlspecialchars($_POST['type'] ?? ($edit_mode && isset($existing_property['type']) ? $existing_property['type'] : '')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Step 3: Subcategory Selection -->
                <div class="wizard-step" id="wizard-step-3" style="display: none;<?= $edit_mode ? ' display: none !important;' : '' ?>">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-grid-3x3-gap"></i> Alt Kategori Seçimi</h4>
                                    <p class="text-muted">Alt kategorinizi seçin</p>
                                </div>
                                <div class="subcategory-grid" id="subcategory-options">
                                    <!-- Subcategories will be loaded dynamically based on category and type -->
                                </div>
                                <input type="hidden" id="subcategory" name="subcategory" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Navigation Buttons -->
                <div class="row mt-4" <?= $edit_mode ? 'style="display: none !important;"' : '' ?>>
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-step" style="display: none;">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <div class="flex-fill"></div>
                            <button type="button" class="btn btn-primary" id="next-step" disabled>
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-success" id="continue-form" style="display: none;">
                                Devam Et <i class="bi bi-check-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Main Form -->
                <div id="main-form" style="<?= $edit_mode ? 'display: block !important;' : 'display: none;' ?>">
                    <!-- Selection Summary -->
                    <div class="alert alert-success mb-4">
                        <h5><i class="bi bi-check-circle-fill"></i> Kategori Seçimi Tamamlandı</h5>
                        <div id="selection-summary"></div>
                    </div>
                    <!-- Property Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label">İlan Başlığı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" required 
                               placeholder="Örn: ÇINAROĞLU İNŞAATTAN FIRSAT SİTE İÇİRESİNDE 3+1 SATILIK DAİRE"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ($edit_mode && isset($existing_property['title']) ? $existing_property['title'] : '')); ?>">
                        <!-- Title Preview -->
                        <div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
                            <h6 class="mb-2">İlan Ön İzleme</h6>
                            <div id="title-preview" class="border p-2 bg-white">
                                <span class="fw-bold" style="color: #0d6efd;">İlan başlığınız burada görünecek...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required 
                                  placeholder="Mülkünüzün özelliklerini detaylı şekilde açıklayın..."><?php echo htmlspecialchars($_POST['description'] ?? ($edit_mode && isset($existing_property['description']) ? $existing_property['description'] : '')); ?></textarea>
                    </div>
                    <!-- Price Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="bi bi-currency-exchange"></i> Fiyat Bilgileri</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Fiyat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" id="price" name="price" required 
                                       placeholder="6.400.000" style="font-size: 1.2rem; font-weight: 600;"
                                       value="<?php echo htmlspecialchars($_POST['price'] ?? ($edit_mode && isset($existing_property['price']) ? number_format($existing_property['price'], 0, ',', '.') : '')); ?>">
                                <span class="input-group-text">TL</span>
                            </div>
                        </div>
                    </div>
                    <!-- Area Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="area_gross" class="form-label">m² (Brüt)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="area_gross" name="area_gross" 
                                       placeholder="120" min="1" max="10000"
                                       value="<?php echo htmlspecialchars($_POST['area_gross'] ?? ($edit_mode && isset($existing_property['area_gross']) ? $existing_property['area_gross'] : '')); ?>">
                                <span class="input-group-text">m²</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="area_net" class="form-label">m² (Net)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="area_net" name="area_net" 
                                       placeholder="95" min="1" max="10000"
                                       value="<?php echo htmlspecialchars($_POST['area_net'] ?? ($edit_mode && isset($existing_property['area_net']) ? $existing_property['area_net'] : '')); ?>">
                                <span class="input-group-text">m²</span>
                            </div>
                        </div>
                    </div>
                    <!-- Basic Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-info-circle"></i> Temel Bilgiler
                    </h6>
                    <div class="row">
                        <!-- Total Room Count -->
                        <div class="col-md-2 mb-3">
                            <label for="room_count" class="form-label">Toplam Oda Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="room_count" name="room_count" required>
                                <option value="">Seçiniz</option>
                                <?php for($i = 0; $i <= 7; $i++): ?>
                                <option value="<?= $i ?>" <?= ($edit_mode && isset($existing_property['room_count']) && $existing_property['room_count'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <!-- Bedroom Count -->
                        <div class="col-md-2 mb-3">
                            <label for="bedrooms" class="form-label">Yatak Odası Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="bedrooms" name="bedrooms" required>
                                <option value="">Seçiniz</option>
                                <?php for($i = 0; $i <= 7; $i++): ?>
                                <option value="<?= $i ?>" <?= ($edit_mode && isset($existing_property['bedrooms']) && $existing_property['bedrooms'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <!-- Living Room Count -->
                        <div class="col-md-2 mb-3">
                            <label for="living_room_count" class="form-label">Salon Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="living_room_count" name="living_room_count" required>
                                <option value="">Seçiniz</option>
                                <?php for($i = 0; $i <= 7; $i++): ?>
                                <option value="<?= $i ?>" <?= ($edit_mode && isset($existing_property['living_room_count']) && $existing_property['living_room_count'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <!-- Building Age -->
                        <div class="col-md-3 mb-3">
                            <label for="building_age" class="form-label">Bina Yaşı <span class="text-danger">*</span></label>
                            <select class="form-select" id="building_age" name="building_age" required>
                                <option value="">Seçiniz</option>
                                <option value="0" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '0') ? 'selected' : '' ?>>0 (Sıfır Bina)</option>
                                <option value="1" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '1') ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '2') ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '3') ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '4') ? 'selected' : '' ?>>4</option>
                                <option value="5" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '5') ? 'selected' : '' ?>>5</option>
                                <option value="6-10" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '6-10') ? 'selected' : '' ?>>6-10</option>
                                <option value="11-15" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '11-15') ? 'selected' : '' ?>>11-15</option>
                                <option value="16-20" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '16-20') ? 'selected' : '' ?>>16-20</option>
                                <option value="21-25" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '21-25') ? 'selected' : '' ?>>21-25</option>
                                <option value="26-30" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '26-30') ? 'selected' : '' ?>>26-30</option>
                                <option value="31+" <?= ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '31+') ? 'selected' : '' ?>>31 ve üzeri</option>
                            </select>
                        </div>
                        <!-- Floor -->
                        <div class="col-md-3 mb-3">
                            <label for="floor_location" class="form-label">Bulunduğu Kat <span class="text-danger">*</span></label>
                            <select class="form-select" id="floor_location" name="floor_location" required>
                                <option value="">Seçiniz</option>
                                <option value="Bodrum Kat" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == 'Bodrum Kat') ? 'selected' : '' ?>>Bodrum Kat</option>
                                <option value="Zemin Kat" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == 'Zemin Kat') ? 'selected' : '' ?>>Zemin Kat</option>
                                <option value="Bahçe Katı" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == 'Bahçe Katı') ? 'selected' : '' ?>>Bahçe Katı</option>
                                <option value="Yüksek Zemin" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == 'Yüksek Zemin') ? 'selected' : '' ?>>Yüksek Zemin</option>
                                <option value="Asma Kat" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == 'Asma Kat') ? 'selected' : '' ?>>Asma Kat</option>
                                <option value="1" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '1') ? 'selected' : '' ?>>1. Kat</option>
                                <option value="2" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '2') ? 'selected' : '' ?>>2. Kat</option>
                                <option value="3" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '3') ? 'selected' : '' ?>>3. Kat</option>
                                <option value="4" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '4') ? 'selected' : '' ?>>4. Kat</option>
                                <option value="5" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '5') ? 'selected' : '' ?>>5. Kat</option>
                                <option value="6" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '6') ? 'selected' : '' ?>>6. Kat</option>
                                <option value="7" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '7') ? 'selected' : '' ?>>7. Kat</option>
                                <option value="8" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '8') ? 'selected' : '' ?>>8. Kat</option>
                                <option value="9" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '9') ? 'selected' : '' ?>>9. Kat</option>
                                <option value="10" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '10') ? 'selected' : '' ?>>10. Kat</option>
                                <option value="11-15" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '11-15') ? 'selected' : '' ?>>11-15. Kat</option>
                                <option value="16-20" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '16-20') ? 'selected' : '' ?>>16-20. Kat</option>
                                <option value="21+" <?= ($edit_mode && isset($existing_property['floor']) && $existing_property['floor'] == '21+') ? 'selected' : '' ?>>21 ve üzeri</option>
                            </select>
                        </div>
                        <!-- Building Floors -->
                        <div class="col-md-3 mb-3">
                            <label for="building_floors" class="form-label">Bina Kat Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="building_floors" name="building_floors" required>
                                <option value="">Seçiniz</option>
                                <option value="1" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '1') ? 'selected' : '' ?>>1 Kat</option>
                                <option value="2" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '2') ? 'selected' : '' ?>>2 Kat</option>
                                <option value="3" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '3') ? 'selected' : '' ?>>3 Kat</option>
                                <option value="4" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '4') ? 'selected' : '' ?>>4 Kat</option>
                                <option value="5" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '5') ? 'selected' : '' ?>>5 Kat</option>
                                <option value="6" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '6') ? 'selected' : '' ?>>6 Kat</option>
                                <option value="7" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '7') ? 'selected' : '' ?>>7 Kat</option>
                                <option value="8" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '8') ? 'selected' : '' ?>>8 Kat</option>
                                <option value="9" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '9') ? 'selected' : '' ?>>9 Kat</option>
                                <option value="10" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '10') ? 'selected' : '' ?>>10 Kat</option>
                                <option value="11-15" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '11-15') ? 'selected' : '' ?>>11-15 Kat</option>
                                <option value="16-20" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '16-20') ? 'selected' : '' ?>>16-20 Kat</option>
                                <option value="21-30" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '21-30') ? 'selected' : '' ?>>21-30 Kat</option>
                                <option value="31+" <?= ($edit_mode && isset($existing_property['building_floors']) && $existing_property['building_floors'] == '31+') ? 'selected' : '' ?>>31+ Kat</option>
                            </select>
                        </div>
                        <!-- Heating -->
                        <div class="col-md-3 mb-3">
                            <label for="heating" class="form-label">Isıtma <span class="text-danger">*</span></label>
                            <select class="form-select" id="heating" name="heating" required>
                                <option value="">Seçiniz</option>
                                <option value="Yok" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Yok') ? 'selected' : '' ?>>Yok</option>
                                <option value="Soba" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Soba') ? 'selected' : '' ?>>Soba</option>
                                <option value="Doğalgaz Sobası" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Doğalgaz Sobası') ? 'selected' : '' ?>>Doğalgaz Sobası</option>
                                <option value="Kat Kaloriferi" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Kat Kaloriferi') ? 'selected' : '' ?>>Kat Kaloriferi</option>
                                <option value="Merkezi Sistem" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Merkezi Sistem') ? 'selected' : '' ?>>Merkezi Sistem</option>
                                <option value="Kombi (Doğalgaz)" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Kombi (Doğalgaz)') ? 'selected' : '' ?>>Kombi (Doğalgaz)</option>
                                <option value="Kombi (Elektrik)" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Kombi (Elektrik)') ? 'selected' : '' ?>>Kombi (Elektrik)</option>
                                <option value="Yerden Isıtma" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Yerden Isıtma') ? 'selected' : '' ?>>Yerden Isıtma</option>
                                <option value="Klima" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Klima') ? 'selected' : '' ?>>Klima</option>
                                <option value="Fancoil Ünitesi" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Fancoil Ünitesi') ? 'selected' : '' ?>>Fancoil Ünitesi</option>
                                <option value="Güneş Enerjisi" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Güneş Enerjisi') ? 'selected' : '' ?>>Güneş Enerjisi</option>
                                <option value="Jeotermal" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Jeotermal') ? 'selected' : '' ?>>Jeotermal</option>
                                <option value="Şömine" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Şömine') ? 'selected' : '' ?>>Şömine</option>
                            </select>
                        </div>
                    </div>
                    <!-- Second Row -->
                    <div class="row">
                        <!-- Bathroom Count -->
                        <div class="col-md-3 mb-3">
                            <label for="bathroom_count" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="bathroom_count" name="bathroom_count" required>
                                <option value="">Seçiniz</option>
                                <option value="1" <?= ($edit_mode && isset($existing_property['bathrooms']) && $existing_property['bathrooms'] == '1') ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= ($edit_mode && isset($existing_property['bathrooms']) && $existing_property['bathrooms'] == '2') ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= ($edit_mode && isset($existing_property['bathrooms']) && $existing_property['bathrooms'] == '3') ? 'selected' : '' ?>>3</option>
                            </select>
                        </div>
                        <!-- Elevator -->
                        <div class="col-md-3 mb-3">
                            <label for="elevator" class="form-label">Asansör <span class="text-danger">*</span></label>
                            <select class="form-select" id="elevator" name="elevator" required>
                                <option value="">Seçiniz</option>
                                <option value="Var" <?= ($edit_mode && isset($existing_property['elevator']) && $existing_property['elevator'] == 'Var') ? 'selected' : '' ?>>Var</option>
                                <option value="Yok" <?= ($edit_mode && isset($existing_property['elevator']) && $existing_property['elevator'] == 'Yok') ? 'selected' : '' ?>>Yok</option>
                            </select>
                        </div>
                        <!-- Parking -->
                        <div class="col-md-3 mb-3">
                            <label for="parking" class="form-label">Otopark <span class="text-danger">*</span></label>
                            <!-- DEBUG: <?= $edit_mode ? "Edit mode - Parking: [" . htmlspecialchars($existing_property['parking'] ?? 'NULL') . "]" : "Add mode" ?> -->
                            <select class="form-select" id="parking" name="parking" required>
                                <option value="">Seçiniz</option>
                                <option value="Otopark Yok" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Otopark Yok') ? 'selected' : '' ?>>Otopark Yok</option>
                                <option value="Acik Otopark" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Acik Otopark') ? 'selected' : '' ?>>Açık Otopark</option>
                                <option value="Kapali Otopark" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Kapali Otopark') ? 'selected' : '' ?>>Kapalı Otopark</option>
                                <option value="Otopark Var" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Otopark Var') ? 'selected' : '' ?>>Otopark Var</option>
                            </select>
                        </div>
                        <!-- Usage Status -->
                        <div class="col-md-3 mb-3">
                            <label for="usage_status" class="form-label">Kullanım Durumu <span class="text-danger">*</span></label>
                            <!-- DEBUG: <?= $edit_mode ? "Edit mode - Usage Status: [" . htmlspecialchars($existing_property['usage_status'] ?? 'NULL') . "]" : "Add mode" ?> -->
                            <select class="form-select" id="usage_status" name="usage_status" required>
                                <option value="">Seçiniz</option>
                                <option value="Bos" <?= ($edit_mode && isset($existing_property['usage_status']) && $existing_property['usage_status'] == 'Bos') ? 'selected' : '' ?>>Boş</option>
                                <option value="Kiracili" <?= ($edit_mode && isset($existing_property['usage_status']) && $existing_property['usage_status'] == 'Kiracili') ? 'selected' : '' ?>>Kiracılı</option>
                                <option value="Malik Kullaniminda" <?= ($edit_mode && isset($existing_property['usage_status']) && $existing_property['usage_status'] == 'Malik Kullaniminda') ? 'selected' : '' ?>>Malik Kullanımında</option>
                                <option value="Yatirim Amacli" <?= ($edit_mode && isset($existing_property['usage_status']) && $existing_property['usage_status'] == 'Yatirim Amacli') ? 'selected' : '' ?>>Yatırım Amaçlı</option>
                            </select>
                        </div>
                    </div>
                    <!-- Third Row -->
                    <div class="row">
                        <!-- Dues -->
                        <div class="col-md-3 mb-3">
                            <label for="dues" class="form-label">Aidat (TL)</label>
                            <input type="number" class="form-control" id="dues" name="dues" 
                                   placeholder="0" min="0" max="999999"
                                   value="<?php echo htmlspecialchars($_POST['dues'] ?? ($edit_mode && isset($existing_property['dues']) ? $existing_property['dues'] : '')); ?>">
                        </div>
                        <!-- Furnished -->
                        <div class="col-md-3 mb-3">
                            <label for="furnished" class="form-label">Eşyalı <span class="text-danger">*</span></label>
                            <select class="form-select" id="furnished" name="furnished" required>
                                <option value="">Seçiniz</option>
                                <option value="1" <?= ($edit_mode && isset($existing_property['furnished']) && $existing_property['furnished'] == '1') ? 'selected' : '' ?>>Evet</option>
                                <option value="0" <?= ($edit_mode && isset($existing_property['furnished']) && $existing_property['furnished'] == '0') ? 'selected' : '' ?>>Hayır</option>
                            </select>
                        </div>
                        <!-- Credit Eligible -->
                        <div class="col-md-3 mb-3">
                            <label for="credit_eligible" class="form-label">Krediye Uygun</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="credit_eligible" name="credit_eligible" <?= ($edit_mode && isset($existing_property['credit_eligible']) && $existing_property['credit_eligible'] == '1') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="credit_eligible">
                                    Evet, krediye uygun
                                </label>
                            </div>
                        </div>
                        <!-- Featured Property -->
                        <?php if ($user_data['role'] === 'admin'): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning bg-light">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-crown me-2"></i>
                                        <strong>Admin Özel Özellik</strong>
                                        <span class="badge bg-dark ms-2">PREMIUM</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <label for="is_featured" class="form-label fw-bold">
                                        <i class="fas fa-star text-warning me-1"></i>Anasayfada Öne Çıkart
                                    </label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input featured-checkbox" type="checkbox" value="1" id="is_featured" name="is_featured"
                                               <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) || ($edit_mode && isset($existing_property['featured']) && $existing_property['featured'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label featured-label" for="is_featured">
                                            <span class="text-primary fw-bold">
                                                <i class="fas fa-fire text-danger me-1"></i>Evet, anasayfada öne çıkart
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Bu ilan anasayfada öne çıkarılacak ve daha fazla görüntülenecek
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Deed Status -->
                        <div class="col-md-3 mb-3">
                            <label for="deed_status" class="form-label">Tapu Durumu <span class="text-danger">*</span></label>
                            <select class="form-select" id="deed_status" name="deed_status" required>
                                <option value="">Seçiniz</option>
                                <option value="Kat Mulkiyeti" <?= ($edit_mode && isset($existing_property['deed_status']) && $existing_property['deed_status'] == 'Kat Mulkiyeti') ? 'selected' : '' ?>>Kat Mülkiyeti</option>
                                <option value="Kat Irtifaki" <?= ($edit_mode && isset($existing_property['deed_status']) && $existing_property['deed_status'] == 'Kat Irtifaki') ? 'selected' : '' ?>>Kat İrtifakı</option>
                                <option value="Arsa Payi" <?= ($edit_mode && isset($existing_property['deed_status']) && $existing_property['deed_status'] == 'Arsa Payi') ? 'selected' : '' ?>>Arsa Payı</option>
                                <option value="Mustakil Tapu" <?= ($edit_mode && isset($existing_property['deed_status']) && $existing_property['deed_status'] == 'Mustakil Tapu') ? 'selected' : '' ?>>Müstakil Tapu</option>
                            </select>
                        </div>
                    </div>
                    <!-- Fourth Row -->
                    <div class="row">
                        <!-- Exchange -->
                        <div class="col-md-4 mb-3">
                            <label for="exchange" class="form-label">Takaslı <span class="text-danger">*</span></label>
                            <select class="form-select" id="exchange" name="exchange" required>
                                <option value="">Seçiniz</option>
                                <option value="Evet" <?= ($edit_mode && isset($existing_property['exchange']) && $existing_property['exchange'] == 'Evet') ? 'selected' : '' ?>>Evet</option>
                                <option value="Hayır" <?= ($edit_mode && isset($existing_property['exchange']) && $existing_property['exchange'] == 'Hayır') ? 'selected' : '' ?>>Hayır</option>
                            </select>
                        </div>
                    </div>
                    <!-- Address Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-geo-alt"></i> Adres Bilgileri
                    </h6>
                    <div class="row">
                        <!-- City -->
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">İl <span class="text-danger">*</span></label>
                            <select class="form-select" id="city" name="city" required>
                                <option value="İstanbul" selected>İstanbul</option>
                            </select>
                        </div>
                        <!-- District -->
                        <div class="col-md-4 mb-3">
                            <label for="district" class="form-label">İlçe <span class="text-danger">*</span></label>
                            <select class="form-select" id="district" name="district" required>
                                <option value="">İlçe Seçiniz</option>
                            </select>
                        </div>
                        <!-- Neighborhood -->
                        <div class="col-md-4 mb-3">
                            <label for="neighborhood" class="form-label">Mahalle</label>
                            <select class="form-select" id="neighborhood" name="neighborhood">
                                <option value="">Mahalle Seçiniz</option>
                            </select>
                        </div>
                    </div>
                    <!-- Location Type -->
                    <div class="mb-4">
                        <label class="form-label">Konum Tipi <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="site" id="location_site" required 
                                           <?= ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'site') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="location_site">
                                        <i class="bi bi-buildings"></i> Site İçerisinde
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="standalone" id="location_standalone" required 
                                           <?= ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'standalone') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="location_standalone">
                                        <i class="bi bi-house"></i> Müstakil/Site Dışı
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Site Name -->
                    <div class="mb-4" id="site-name-section" style="display: none;">
                        <label for="site_name" class="form-label">Site Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               placeholder="Örn: Bahçeşehir Premium Sitesi"
                               value="<?php echo htmlspecialchars($_POST['site_name'] ?? ($edit_mode && isset($existing_property['site_name']) ? $existing_property['site_name'] : '')); ?>">
                    </div>
                    <!-- Address Details -->
                    <div class="mb-4" id="address-details-section" style="display: none;">
                        <label for="address_details" class="form-label">Adres Detayları <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address_details" name="address_details" rows="3" 
                                  placeholder="Sokak, apartman adı, kapı numarası vb. detayları yazınız..."><?php echo htmlspecialchars($_POST['address_details'] ?? ($edit_mode && isset($existing_property['address']) ? $existing_property['address'] : '')); ?></textarea>
                    </div>
                    <!-- Özellikler Bölümü -->
                    <div class="mt-5">
                        <h6 class="text-primary mb-4 border-bottom pb-2">
                            <i class="fas fa-list-check"></i> Özellikler
                            <small class="text-muted ms-2">İlan için uygun olan özellikleri seçin</small>
                        </h6>
                        <div class="row">
                            <!-- İç Özellikler -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-home me-2"></i>İç Özellikler
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Klima" id="ic_klima" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_klima">Klima</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Buzdolabı" id="ic_buzdolabi" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_buzdolabi">Buzdolabı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çamaşır Makinesi" id="ic_camasir" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_camasir">Çamaşır Makinesi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Bulaşık Makinesi" id="ic_bulasik" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_bulasik">Bulaşık Makinesi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Fırın" id="ic_firin" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_firin">Fırın</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Ocak" id="ic_ocak" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_ocak">Ocak</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Ankastre Mutfak" id="ic_ankastre" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_ankastre">Ankastre Mutfak</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Duşakabin" id="ic_dusakabin" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_dusakabin">Duşakabin</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Jakuzi" id="ic_jakuzi" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_jakuzi">Jakuzi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Amerikan Mutfak" id="ic_amerikan" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_amerikan">Amerikan Mutfak</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Ayrı Mutfak" id="ic_ayri_mutfak" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_ayri_mutfak">Ayrı Mutfak</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Gömme Dolap" id="ic_gomme" name="ic_ozellikler[]">
                                                    <label class="form-check-label" for="ic_gomme">Gömme Dolap</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Dış Özellikler -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">
                                            <i class="fas fa-tree me-2"></i>Dış Özellikler
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Balkon" id="dis_balkon" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_balkon">Balkon</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Teras" id="dis_teras" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_teras">Teras</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Bahçe" id="dis_bahce" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_bahce">Bahçe</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Müstakil Bahçe" id="dis_mustakil" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_mustakil">Müstakil Bahçe</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Barbeku" id="dis_barbeku" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_barbeku">Barbeku</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çamaşır Kurutma" id="dis_camasir_kurutma" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_camasir_kurutma">Çamaşır Kurutma</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Veranda" id="dis_veranda" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_veranda">Veranda</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çiçek Bahçesi" id="dis_cicek" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_cicek">Çiçek Bahçesi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Meyve Bahçesi" id="dis_meyve" name="dis_ozellikler[]">
                                                    <label class="form-check-label" for="dis_meyve">Meyve Bahçesi</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Muhit Özellikleri -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">
                                            <i class="fas fa-map-marker-alt me-2"></i>Muhit Özellikleri
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Okul" id="muhit_okul" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_okul">Okul</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Hastane" id="muhit_hastane" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_hastane">Hastane</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Alışveriş Merkezi" id="muhit_avm" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_avm">Alışveriş Merkezi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Park" id="muhit_park" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_park">Park</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Cami" id="muhit_cami" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_cami">Cami</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Pazar Yeri" id="muhit_pazar" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_pazar">Pazar Yeri</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Eczane" id="muhit_eczane" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_eczane">Eczane</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Banka" id="muhit_banka" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_banka">Banka</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Restoran" id="muhit_restoran" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_restoran">Restoran</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Kafe" id="muhit_kafe" name="muhit_ozellikleri[]">
                                                    <label class="form-check-label" for="muhit_kafe">Kafe</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Ulaşım Özellikleri -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning">
                                            <i class="fas fa-bus me-2"></i>Ulaşım Özellikleri
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Otobüs Durağı" id="ulasim_otobus" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_otobus">Otobüs Durağı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Minibüs Durağı" id="ulasim_minibus" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_minibus">Minibüs Durağı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Metro" id="ulasim_metro" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_metro">Metro</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Metrobüs" id="ulasim_metrobus" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_metrobus">Metrobüs</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Tramvay" id="ulasim_tramvay" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_tramvay">Tramvay</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Tren İstasyonu" id="ulasim_tren" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_tren">Tren İstasyonu</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Deniz Otobüsü" id="ulasim_deniz" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_deniz">Deniz Otobüsü</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Havaalanı" id="ulasim_havalimani" name="ulasim_ozellikleri[]">
                                                    <label class="form-check-label" for="ulasim_havalimani">Havaalanı</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Manzara Özellikleri -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-secondary">
                                            <i class="fas fa-mountain me-2"></i>Manzara Özellikleri
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Deniz Manzarası" id="manzara_deniz" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_deniz">Deniz Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Göl Manzarası" id="manzara_gol" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_gol">Göl Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Dağ Manzarası" id="manzara_dag" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_dag">Dağ Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Orman Manzarası" id="manzara_orman" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_orman">Orman Manzarası</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Şehir Manzarası" id="manzara_sehir" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_sehir">Şehir Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Boğaz Manzarası" id="manzara_bogaz" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_bogaz">Boğaz Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Park Manzarası" id="manzara_park" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_park">Park Manzarası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Avlu Manzarası" id="manzara_avlu" name="manzara_ozellikleri[]">
                                                    <label class="form-check-label" for="manzara_avlu">Avlu Manzarası</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Konut Tipi Özellikleri -->
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-danger">
                                            <i class="fas fa-building me-2"></i>Konut Tipi Özellikleri
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Sitede" id="konut_sitede" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_sitede">Sitede</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Müstakil" id="konut_mustakil" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_mustakil">Müstakil</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Dubleks" id="konut_dubleks" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_dubleks">Dubleks</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Tripleks" id="konut_tripleks" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_tripleks">Tripleks</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çatı Dubleks" id="konut_cati" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_cati">Çatı Dubleks</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çatı Katı" id="konut_cati_kati" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_cati_kati">Çatı Katı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Villa" id="konut_villa" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_villa">Villa</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Rezidans" id="konut_rezidans" name="konut_tipi_ozellikleri[]">
                                                    <label class="form-check-label" for="konut_rezidans">Rezidans</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Olanaklar -->
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title text-dark">
                                            <i class="fas fa-star me-2"></i>Olanaklar
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Güvenlik" id="olanak_guvenlik" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_guvenlik">Güvenlik</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Kapıcı" id="olanak_kapici" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_kapici">Kapıcı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Spor Salonu" id="olanak_spor" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_spor">Spor Salonu</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Yüzme Havuzu" id="olanak_havuz" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_havuz">Yüzme Havuzu</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Sauna" id="olanak_sauna" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_sauna">Sauna</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çocuk Oyun Alanı" id="olanak_cocuk" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_cocuk">Çocuk Oyun Alanı</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Tenis Kortu" id="olanak_tenis" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_tenis">Tenis Kortu</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Basketbol Sahası" id="olanak_basketbol" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_basketbol">Basketbol Sahası</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Kameriyeli Bahçe" id="olanak_kameriye" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_kameriye">Kameriyeli Bahçe</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çardak" id="olanak_cardak" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_cardak">Çardak</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Jeneratör" id="olanak_jenerator" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_jenerator">Jeneratör</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Kablo TV" id="olanak_kablo" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_kablo">Kablo TV</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Uydu TV" id="olanak_uydu" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_uydu">Uydu TV</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="İnternet" id="olanak_internet" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_internet">İnternet</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Geniş Bant İnternet" id="olanak_genis_bant" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_genis_bant">Geniş Bant İnternet</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Alarm Sistemi" id="olanak_alarm" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_alarm">Alarm Sistemi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Kamera Sistemi" id="olanak_kamera" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_kamera">Kamera Sistemi</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Konsiyerj" id="olanak_konsiyerj" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_konsiyerj">Konsiyerj</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Vale Hizmeti" id="olanak_vale" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_vale">Vale Hizmeti</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Çamaşırhane" id="olanak_camasirhane" name="olanaklar[]">
                                                    <label class="form-check-label" for="olanak_camasirhane">Çamaşırhane</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Photo Upload Section -->
                    <div class="mt-5">
                        <h6 class="text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-camera"></i> Fotoğraflar
                            <small class="text-muted ms-2">İlk fotoğraf kapak fotoğrafı olacaktır</small>
                        </h6>
                        <div class="photo-gallery-container">
                            <!-- Existing Photos (Edit Mode) - CLOUDFLARE ONLY -->
                            <?php if ($edit_mode && !empty($existing_images)): ?>
                                <!-- $existing_images already filtered to Cloudflare-only -->
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-cloud text-primary me-2"></i>
                                        Cloudflare Fotoğraflar
                                        <span class="badge bg-primary ms-2"><?= count($existing_images) ?></span>
                                        <small class="text-muted ms-2">(Sadece CF resimler gösteriliyor)</small>
                                    </h6>
                                    <div class="existing-photos-grid">
                                        <?php foreach ($existing_images as $index => $image): ?>
                                            <?php 
                                            // Try to use local thumbnail first, fallback to Cloudflare
                                            $thumbnailUrl = null;
                                            $cloudflareId = null;
                                            if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $image)) {
                                                // Pure Cloudflare ID
                                                $cloudflareId = $image;
                                                $display_url = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $image . "/public";
                                            } else {
                                                // Extract Cloudflare ID from full URL
                                                if (preg_match('/\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\//', $image, $matches)) {
                                                    $cloudflareId = $matches[1];
                                                    $display_url = $image;
                                                } else {
                                                    $cloudflareId = null;
                                                    $display_url = $image;
                                                }
                                            }
                                            // Check for local thumbnail
                                            if ($cloudflareId && $edit_id) {
                                                $thumbnailPath = "/uploads/thumbnails/thumb_{$edit_id}_{$index}_{$cloudflareId}.jpg";
                                                $fullThumbnailPath = __DIR__ . '/..' . $thumbnailPath;
                                                if (file_exists($fullThumbnailPath)) {
                                                    $thumbnailUrl = $thumbnailPath;
                                                    error_log("Found local thumbnail: $thumbnailPath");
                                                } else {
                                                    error_log("Thumbnail not found: $fullThumbnailPath");
                                                }
                                            }
                                            ?>
                                            <div class="existing-photo-item cloudflare-photo" data-image="<?= htmlspecialchars($image) ?>" data-index="<?= $index ?>" onclick="previewCloudflareImage('<?= htmlspecialchars($cloudflareId) ?>', <?= $index + 1 ?>)" style="cursor: pointer;" title="Resmi önizlemek için tıklayın">
                                                <?php if ($thumbnailUrl): ?>
                                                    <!-- Use local thumbnail -->
                                                    <img src="<?= $thumbnailUrl ?>" 
                                                         alt="Thumbnail <?= $index + 1 ?>" 
                                                         class="img-fluid">
                                                <?php else: ?>
                                                    <!-- Fallback to Cloudflare with error handling -->
                                                    <img src="<?= $display_url ?>" 
                                                         alt="Cloudflare fotoğraf <?= $index + 1 ?>" 
                                                         class="img-fluid"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <!-- Error fallback display -->
                                                    <div class="image-error-fallback" style="display: none; align-items: center; justify-content: center; height: 100%; background: #f8f9fa; border: 2px dashed #dee2e6;">
                                                        <div class="text-center">
                                                            <i class="fas fa-image text-muted" style="font-size: 2rem;"></i>
                                                            <div class="small text-muted mt-1">Resim #<?= $index + 1 ?></div>
                                                            <div class="small text-muted">Creating thumbnail...</div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <!-- Cloudflare Badge -->
                                                <div class="cloudflare-badge">CF</div>
                                                <!-- Photo Controls -->
                                                <div class="photo-controls">
                                                    <?php if ($index !== 0): ?>
                                                        <button type="button" class="btn-make-main" onclick="makeMainPhoto(<?= $index ?>)" title="Ana resim yap">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn-remove-photo" onclick="removeExistingPhoto(<?= $index ?>)" title="Resmi kaldır">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <?php if ($index === 0): ?>
                                                    <div class="main-photo-badge">
                                                        <i class="fas fa-star"></i> ANA
                                                    </div>
                                                <?php endif; ?>
                                                <div class="photo-number"><?= $index + 1 ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-primary">
                                            <i class="fas fa-cloud me-1"></i>
                                            Cloudflare resimler gösteriliyor. Local resimler otomatik migrate edilecek.
                                        </small>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="mb-4">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Cloudflare-Only Mod:</strong> Sadece Cloudflare resimleri desteklenmektedir. 
                                        Mevcut local resimler otomatik olarak Cloudflare'e migrate edilecektir.
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- Upload Area - CLOUDFLARE ENHANCED -->
            <div class="upload-area" id="uploadArea">
                <div class="upload-content">
                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                    <h5>Fotoğrafları Sürükleyin veya Seçin</h5>
                    <p class="text-muted">Birden fazla fotoğraf seçebilirsiniz • JPG, PNG, WEBP • Max 5MB/fotoğraf</p>
                    <!-- Cloudflare Status Indicator -->
                    <div class="cloudflare-status mb-3" id="cloudflareStatus">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="cloudflare-icon me-2">
                                <i class="fas fa-cloud"></i>
                            </div>
                            <small class="text-muted" id="cloudflareStatusText">Cloudflare Images Hazır</small>
                            <div class="cloudflare-indicator ms-2" id="cloudflareIndicator">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click()">
                        <i class="fas fa-images me-2"></i>Fotoğraf Seç
                    </button>
                </div>
                <input type="file" id="photoInput" name="property_images[]" multiple accept="image/*" class="d-none">
                <!-- Upload Progress Bar -->
                <div class="upload-progress mt-3" id="uploadProgress" style="display: none;">
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" id="progressBar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" id="progressText">Yükleniyor...</small>
                        <small class="text-muted" id="progressPercentage">0%</small>
                    </div>
                </div>
            </div>                            <!-- Selected Photos -->
                            <div class="selected-photos mt-4" id="selectedPhotos" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="fas fa-images text-primary me-2"></i>
                                        Seçilen Fotoğraflar 
                                        <span class="badge bg-primary ms-2" id="photoCounter">0</span>
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllPhotos()">
                                        <i class="fas fa-trash me-1"></i>Tümünü Temizle
                                    </button>
                                </div>
                                <div class="photos-grid" id="photosGrid">
                                    <!-- Photos will be added dynamically -->
                                </div>
                                <!-- Upload Progress List - RESTORED for animation -->
                                <div class="upload-progress-list mt-3" id="uploadProgressList" style="display: none;">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-rocket me-2"></i>
                                        🚀 PARALEL UPLOAD: Hızlı Yükleme
                                    </h6>
                                    <div class="progress-items" id="progressItems">
                                        <!-- Progress items will be added dynamically -->
                                    </div>
                                </div>
                            </div>
                            <!-- Info -->
                            <div class="photo-info mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    • İlk fotoğraf ana fotoğraf olarak kullanılacak<br>
                                    • Fotoğraf sıralamasını değiştirmek için sürükle-bırak kullanın<br>
                                    • Maksimum dosya boyutu: 10MB (Cloudflare Pro plan)<br>
                                    • Fotoğraf sayısı sınırı yok
                                </small>
                            </div>
                            <!-- Overall Upload Progress - RESTORED -->
                            <div class="overall-upload-progress mt-3" id="overallUploadProgress" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-muted mb-0">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>
                                        🚀 Paralel Upload İlerlemesi
                                    </h6>
                                    <small class="text-muted" id="overallProgressText">0/0 tamamlandı</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                         role="progressbar" 
                                         style="width: 0%" 
                                         id="overallProgressBar">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <!-- Form Submit Button -->
                    <div class="mt-4">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Bilgileriniz güvenli şekilde işlenecektir
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Formu Sıfırla
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-<?= $edit_mode ? 'save' : 'check-circle' ?> me-2"></i><?= $edit_mode ? 'Güncelle' : 'İlanı Yayınla' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Enhanced Property Form JavaScript -->
    <script>
        // Set edit mode globally for JS modules
        window.editMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
        // Debug bilgileri (sadece geliştirme aşamasında)
        console.log('PHP Debug - Uploaded images:', <?php echo json_encode($images_string); ?>);
        console.log('PHP Debug - Main image:', <?php echo json_encode($main_image); ?>);
    </script>
    <!-- FORCE CACHE CLEAR - EMERGENCY UPDATE -->
    <?php 
    $emergency_version = '2.0.' . time() . '.' . rand(1000, 9999);
    ?>
    <script>
    // Emergency cache clear
    if ('caches' in window) {
        caches.keys().then(function(names) {
            names.forEach(function(name) {
                caches.delete(name);
            });
        });
    }
    </script>
    <!-- External JavaScript Modules with SUPER EMERGENCY Cache Busting -->
    <?php $super_emergency = '3.0.' . time() . '.' . rand(10000, 99999); ?>
    <script>
        // SUPER EMERGENCY: Force clear all browser caches
        if ('caches' in window) {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
                console.log('🧹 Service worker caches cleared');
            });
        }
        // Clear all storage
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
            console.log('🧹 Local storage cleared');
        }
        console.log('🚨 SUPER EMERGENCY CACHE BUST: <?php echo $super_emergency; ?>');
    </script>
    <script src="assets/js/property-wizard.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/property-wizard.js')); ?>"></script>
    <script src="assets/js/photo-upload-system.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/photo-upload-system.js')); ?>"></script>
    <script src="assets/js/location-manager.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/location-manager.js')); ?>"></script>
    <script src="assets/js/cloudflare-images.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/cloudflare-images.js')); ?>"></script>
    <script src="assets/js/form-handlers.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/form-handlers.js')); ?>"></script>
    <script src="assets/js/add-property.js?v=<?php echo $super_emergency; ?>&bust=<?php echo md5(filemtime(__DIR__ . '/../assets/js/add-property.js')); ?>"></script>
    <!-- Form Submit Event Listener -->
    <script>
        // Minimal form submit handler
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('propertyForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Update existing images hidden input before submit
                    const updatedExistingImagesInput = document.getElementById('updatedExistingImages');
                    if (updatedExistingImagesInput && window.existingPhotosData) {
                        updatedExistingImagesInput.value = JSON.stringify(window.existingPhotosData);
                        console.log('Updated existing photos:', window.existingPhotosData);
                    }
                    // Enhanced photo upload feedback
                    const photoInput = document.getElementById('photoInput');
                    const photoCount = photoInput.files.length;
                    if (photoCount > 0) {
                        console.log(`🚀 Starting upload process: ${photoCount} photos`);
                        // Start real upload progress tracking
                        if (window.startRealUploadProgress) {
                            window.startRealUploadProgress();
                        }
                    }
                    // Log file details for debugging
                    for (let i = 0; i < photoCount; i++) {
                        const file = photoInput.files[i];
                        console.log(`📁 File ${i+1}: ${file.name} (${(file.size/1024/1024).toFixed(2)}MB)`);
                    }
                    // Warning if no photos
                    if (photoCount === 0 && (!window.existingPhotosData || window.existingPhotosData.length === 0)) {
                        const confirmSubmit = confirm('Hiç fotoğraf yok. Devam etmek istiyor musunuz?');
                        if (!confirmSubmit) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
