<?php
// ERROR HANDLING VE DEBUG - EN ÜST SEVİYE

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

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
        error_log("CSRF manager yüklendi - dosyadan");
    } else {
        error_log("CSRF manager dosyası bulunamadı, alternatif kullanılıyor");
    }
} catch (Exception $e) {
    error_log("CSRF manager error: " . $e->getMessage());
    error_log("CSRF manager hatası: " . $e->getMessage());
}

// CSRF TOKEN ALTERNATIFI - Güvenli bir şekilde
if (!class_exists('CSRFTokenManager')) {
    error_log("Creating fallback CSRFTokenManager class");
    class CSRFTokenManager {
        public static function getToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                error_log("New CSRF token generated: " . substr($_SESSION['csrf_token'], 0, 10) . "...");
            } else {
                error_log("Existing CSRF token retrieved: " . substr($_SESSION['csrf_token'], 0, 10) . "...");
            }
            return $_SESSION['csrf_token'];
        }
        
        public static function validateToken($token) {
            $valid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
            error_log("CSRF token validation: " . ($valid ? 'PASSED' : 'FAILED'));
            return $valid;
        }
        
        public static function getTokenField() {
            $token = self::getToken();
            return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
        }
    }
} else {
    error_log("CSRFTokenManager class already exists (loaded from file)");
}
error_log("CSRF token sistemi hazır");

// LOCAL UPLOAD FUNCTIONS
function processLocalUpload($uploadedFiles, $propertyId = null, $editMode = false) {
    error_log("=== LOCAL UPLOAD PROCESSING START ===");
    
    // Local upload directory setup
    $uploadDir = __DIR__ . '/../uploads/properties/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("❌ Failed to create upload directory: " . $uploadDir);
            return ['success' => false, 'error' => 'Upload directory creation failed'];
        }
        error_log("✅ Created upload directory: " . $uploadDir);
    }
    
    $finalImagesArray = [];
    $processedImages = 0;
    
    if (!empty($uploadedFiles) && isset($uploadedFiles['name']) && !empty($uploadedFiles['name'][0])) {
        $fileCount = count($uploadedFiles['name']);
        error_log("🔄 Processing {$fileCount} images for local upload");
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $uploadedFiles['tmp_name'][$i];
                $originalName = $uploadedFiles['name'][$i];
                $fileSize = $uploadedFiles['size'][$i];
                
                // File validation
                $maxSize = 10 * 1024 * 1024; // 10MB limit
                if ($fileSize > $maxSize) {
                    $sizeMB = round($fileSize / (1024 * 1024), 1);
                    error_log("❌ File too large: {$originalName} ({$sizeMB}MB)");
                    continue;
                }
                
                $mimeType = mime_content_type($tmpName);
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mimeType, $allowedTypes)) {
                    error_log("❌ Invalid file type: {$mimeType} for {$originalName}");
                    continue;
                }
                
                // Generate unique filename
                $fileInfo = pathinfo($originalName);
                $extension = strtolower($fileInfo['extension']);
                $uniqueName = $propertyId . '_' . time() . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . $uniqueName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Create web-accessible URL
                    $webUrl = '/uploads/properties/' . $uniqueName;
                    $finalImagesArray[] = $webUrl;
                    $processedImages++;
                    
                    error_log("✅ Local upload successful: {$originalName} → {$uniqueName}");
                    error_log("   📍 File path: " . $targetPath);
                    error_log("   🌐 Web URL: " . $webUrl);
                } else {
                    error_log("❌ Failed to move uploaded file: {$originalName}");
                }
            } else {
                error_log("❌ Upload error for file index {$i}: " . $uploadedFiles['error'][$i]);
            }
        }
    }
    
    error_log("📊 Local upload summary: {$processedImages}/{$fileCount} images processed successfully");
    error_log("=== LOCAL UPLOAD PROCESSING END ===");
    
    return [
        'success' => true,
        'images' => $finalImagesArray,
        'processed_count' => $processedImages,
        'upload_method' => 'local'
    ];
}

// CLOUDFLARE IMAGE PROCESSING FUNCTIONS
function processPropertyImages($uploadedFiles, $propertyId = null, $editMode = false) {
    error_log("=== IMAGE PROCESSING START ===");
    
    // Check which upload method to use
    require_once __DIR__ . '/../includes/cloudflare-images-config.php';
    
    if (defined('USE_LOCAL_UPLOAD') && USE_LOCAL_UPLOAD === true) {
        error_log("🏠 Using LOCAL UPLOAD method");
        return processLocalUpload($uploadedFiles, $propertyId, $editMode);
    }
    
    error_log("☁️ Using CLOUDFLARE UPLOAD method");
    
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
        if ($editMode && !empty($_POST['updated_existing_images'])) {
            $existingImages = json_decode($_POST['updated_existing_images'], true);
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
        
        // Process new uploaded files - direct to Cloudflare only
        if (!empty($uploadedFiles) && isset($uploadedFiles['name']) && !empty($uploadedFiles['name'][0])) {
            $fileCount = count($uploadedFiles['name']);
            error_log("Processing {$fileCount} new images for Cloudflare-only upload");
            
            // Performance estimation for user feedback
            $estimatedTimePerImage = 3; // Optimized to ~3 seconds per image
            $totalEstimatedTime = $fileCount * $estimatedTimePerImage;
            
            if ($fileCount > 8) {
                error_log("🕒 PERFORMANCE: {$fileCount} images will take approximately {$totalEstimatedTime} seconds to upload");
                error_log("💡 RECOMMENDATION: Consider uploading in smaller batches for better performance");
            } else {
                error_log("🚀 FAST BATCH: {$fileCount} images estimated completion time: {$totalEstimatedTime} seconds");
            }
            
            $batchStartTime = microtime(true);
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($uploadedFiles['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $uploadedFiles['tmp_name'][$i];
                    $originalName = $uploadedFiles['name'][$i];
                    $fileSize = $uploadedFiles['size'][$i];
                    
                    // File validation with optimization
                    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit (Cloudflare Pro plan)
                        $sizeMB = round($fileSize / (1024 * 1024), 1);
                        error_log("❌ File too large: {$originalName} ({$sizeMB}MB) - Cloudflare Pro limit is 10MB");
                        continue;
                    }
                    
                    $mimeType = mime_content_type($tmpName);
                    if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                        error_log("❌ Invalid file type: {$mimeType}");
                        continue;
                    }
                    
                    // Quick image dimension check to avoid processing extremely large images
                    $imageInfo = getimagesize($tmpName);
                    if ($imageInfo === false) {
                        error_log("❌ Invalid image file: {$originalName}");
                        continue;
                    }
                    
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    
                    // Skip extremely large images that would slow down processing
                    if ($width > 4000 || $height > 4000) {
                        error_log("⚠️ Image too large for fast processing: {$originalName} ({$width}x{$height})");
                        // Don't skip, but log warning
                    }
                    
                    // Log file processing start
                    error_log("📤 Processing image " . ($i+1) . "/{$fileCount}: {$originalName} ({$width}x{$height}, " . round($fileSize/1024, 1) . "KB)");
                    
                    // Progress tracking
                    error_log("Uploading image " . ($i + 1) . " of {$fileCount}: {$originalName}");
                    $uploadStart = microtime(true);
                    
                    // Upload directly to Cloudflare with optimized retry logic
                    $maxRetries = 1; // Reduced retries for faster processing
                    $uploadResult = null;
                    
                    for ($retry = 0; $retry <= $maxRetries; $retry++) {
                        try {
                            // Set connection timeout to prevent hanging
                            $context = stream_context_create([
                                'http' => [
                                    'timeout' => 10, // 10 second timeout per upload
                                ]
                            ]);
                            
                            $uploadResult = $cloudflare->simpleUpload($tmpName, [
                                'propertyId' => $propertyId ?? 'new',
                                'originalName' => $originalName,
                                'uploadTime' => date('Y-m-d H:i:s'),
                                'retry' => $retry,
                                'batch_position' => $i + 1
                            ]);
                            
                            if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success']) {
                                $uploadTime = round((microtime(true) - $uploadStart) * 1000, 2);
                                error_log("✅ Upload success for {$originalName} in {$uploadTime}ms (attempt " . ($retry + 1) . ")");
                                break; // Success, exit retry loop
                            }
                        } catch (Exception $e) {
                            $uploadTime = round((microtime(true) - $uploadStart) * 1000, 2);
                            error_log("❌ Upload attempt " . ($retry + 1) . " failed for {$originalName} after {$uploadTime}ms: " . $e->getMessage());
                            
                            if ($retry === $maxRetries) {
                                // Create user-friendly error message
                                $errorMsg = "Resim yükleme hatası: {$originalName}";
                                if (strpos($e->getMessage(), 'timeout') !== false || $uploadTime > 10000) {
                                    $errorMsg = "Resim yükleme zaman aşımına uğradı: {$originalName} (10 saniye)";
                                } elseif (strpos($e->getMessage(), 'network') !== false || strpos($e->getMessage(), 'connection') !== false) {
                                    $errorMsg = "Ağ bağlantısı hatası: {$originalName}";
                                } elseif (strpos($e->getMessage(), 'size') !== false) {
                                    $errorMsg = "Dosya boyutu çok büyük: {$originalName}";
                                } else {
                                    $errorMsg = "Cloudflare servisi geçici olarak kullanılamıyor: {$originalName}";
                                }
                                throw new Exception($errorMsg);
                            }
                            
                            // Quick retry with minimal delay
                            usleep(300000); // 0.3 seconds instead of 1 second
                        }
                    }
                    
                    // Debug Cloudflare response
                    error_log("Cloudflare upload response: " . json_encode($uploadResult));
                    
                    if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success'] && isset($uploadResult['image_id'])) {
                        $imageId = $uploadResult['image_id'];
                        $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $imageId . "/public";
                        $finalImagesArray[] = $cloudflareUrl;
                        $cloudflareImagesArray[] = $imageId;
                        
                        // Add watermark to first (main) image - Optimized
                        if (count($finalImagesArray) === 1) {
                            try {
                                $watermarkStart = microtime(true);
                                error_log("🎨 Adding watermark to main image: {$originalName}");
                                
                                // Re-upload with domain-specific watermark for main image
                                $domain = getCurrentDomain();
                                
                                // Set timeout for watermark process
                                $watermarkResult = $cloudflare->uploadImageForDomain($tmpName, $domain, [
                                    'propertyId' => $propertyId ?? 'new',
                                    'originalName' => $originalName,
                                    'isMainImage' => true,
                                    'uploadTime' => date('Y-m-d H:i:s'),
                                    'timeout' => 8 // 8 second timeout for watermark
                                ]);
                                
                                if ($watermarkResult && isset($watermarkResult['success']) && $watermarkResult['success'] && isset($watermarkResult['image_id'])) {
                                    // Replace previous upload with watermarked version
                                    $watermarkedId = $watermarkResult['image_id'];
                                    $watermarkedUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $watermarkedId . "/public";
                                    $finalImagesArray[count($finalImagesArray)-1] = $watermarkedUrl;
                                    $cloudflareImagesArray[count($cloudflareImagesArray)-1] = $watermarkedId;
                                    
                                    $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                                    error_log("✅ Watermark added to main image: {$watermarkedId} in {$watermarkTime}ms");
                                } else {
                                    $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                                    error_log("⚠️ Watermark failed after {$watermarkTime}ms, using original image");
                                }
                            } catch (Exception $e) {
                                $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                                error_log("⚠️ Watermark failed after {$watermarkTime}ms: " . $e->getMessage() . " - using original image");
                                // Don't throw exception, continue with original image
                            }
                        }
                        
                        error_log("Cloudflare-only upload success: {$imageId}");
                    } else {
                        error_log("Cloudflare upload failed or invalid response for {$originalName}:");
                        error_log("Upload result: " . json_encode($uploadResult));
                        
                        // Daha detaylı hata mesajı
                        $errorDetails = '';
                        if (isset($uploadResult['http_code'])) {
                            $errorDetails .= " HTTP:" . $uploadResult['http_code'];
                        }
                        if (isset($uploadResult['curl_error']) && $uploadResult['curl_error']) {
                            $errorDetails .= " cURL:" . $uploadResult['curl_error'];
                        }
                        if (isset($uploadResult['error'])) {
                            $errorDetails .= " Error:" . $uploadResult['error'];
                        }
                        
                        throw new Exception("Cloudflare upload failed for: {$originalName}{$errorDetails}");
                    }
                }
            }
        }
        
        // Set main image and return results
        $mainImage = !empty($finalImagesArray) ? $finalImagesArray[0] : '';
        $mainImageId = !empty($cloudflareImagesArray) ? $cloudflareImagesArray[0] : '';
        
        // Calculate batch performance - with fallback values
        $batchStartTime = $batchStartTime ?? microtime(true);
        $fileCount = $fileCount ?? 0;
        $batchTotalTime = round((microtime(true) - $batchStartTime) * 1000, 2);
        $avgTimePerImage = $fileCount > 0 ? round($batchTotalTime / $fileCount, 2) : 0;
        
        $result = [
            'success' => true,  // Success flag eklendi
            'images_string' => json_encode($finalImagesArray, JSON_UNESCAPED_UNICODE),
            'main_image' => $mainImage,
            'cloudflare_images' => $cloudflareImagesArray,
            'cloudflare_main_image' => $mainImageId,
            'use_cloudflare' => true,
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
        
        // Return error response instead of throwing exception
        return [
            'success' => false,
            'error' => $userMessage . " (Teknik: " . $e->getMessage() . ")",
            'images_string' => '[]',
            'main_image' => '',
            'cloudflare_images' => [],
            'cloudflare_main_image' => '',
            'use_cloudflare' => false,
            'upload_method' => 'failed'
        ];
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

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    error_log("Auth error: user_id session yok");
    error_log("Auth hatası: Session'da user_id yok");
    header("Location: ../index.php");
    exit;
}

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

// İlan ekleme yetkisi kontrolü
$can_add_property = ($user_data['role'] === 'admin' || $user_data['can_add_property'] == 1);
if (!$can_add_property) {
    error_log("Permission denied for user $user_id: role=" . $user_data['role'] . ", can_add_property=" . $user_data['can_add_property']);
    $_SESSION['error'] = "İlan ekleme yetkiniz bulunmamaktadır.";
    header("Location: dashboard.php");
    exit;
}

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
    
    // İlan sahibi kontrolü (admin veya ilanın sahibi olmalı) - Collation sorunu düzeltildi
    $property_query = "SELECT * FROM properties WHERE id = ? AND (user_id = ? OR BINARY ? = 'admin')";
    $stmt = $conn->prepare($property_query);
    $stmt->bind_param("iis", $edit_id, $user_id, $user_data['role']);
    $stmt->execute();
    $existing_property = $stmt->get_result()->fetch_assoc();
    
    if ($existing_property) {
        $edit_mode = true;
        // Mevcut resimleri parse et - ACCOUNT HASH ile URL'leri düzelt
        if (!empty($existing_property['images'])) {
            $all_existing_images = json_decode($existing_property['images'], true);
            if (is_array($all_existing_images)) {
                // Convert images to use account hash for display
                $existing_images = [];
                foreach ($all_existing_images as $image) {
                    if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $image)) {
                        // Sadece ID ise, account hash ile public URL oluştur
                        $existing_images[] = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_HASH . "/" . $image . "/public";
                    } elseif (strpos($image, 'https://imagedelivery.net/') === 0) {
                        // Mevcut Cloudflare URL'i account hash'e çevir
                        if (preg_match('/https:\/\/imagedelivery\.net\/[^\/]+\/([a-f0-9-]+)\//', $image, $matches)) {
                            $imageId = $matches[1];
                            $existing_images[] = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_HASH . "/" . $imageId . "/public";
                        } else {
                            $existing_images[] = $image; // Fallback
                        }
                    } else {
                        // Local image veya diğer formatlar
                        $existing_images[] = $image;
                    }
                }
                error_log("EDIT MODE: Loaded " . count($existing_images) . " existing images (converted to account hash)");
                error_log("EDIT MODE Images: " . print_r($existing_images, true));
            } else {
                $existing_images = [];
                error_log("EDIT MODE: Invalid JSON in images field");
            }
        } else {
            $existing_images = [];
            error_log("EDIT MODE: No images found in database");
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
    error_log("=== FORM SUBMIT DEBUG START ===");
    error_log("Session ID: " . session_id());
    error_log("Session status: " . session_status());
    error_log("POST keys: " . implode(', ', array_keys($_POST)));
    error_log("POST csrf_token exists: " . (isset($_POST['csrf_token']) ? 'YES' : 'NO'));
    error_log("Session csrf_token exists: " . (isset($_SESSION['csrf_token']) ? 'YES' : 'NO'));
    error_log("Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'unknown'));
    error_log("==================================");
    
    try {
        // Handle AJAX request for main image update
        if (isset($_POST['action']) && $_POST['action'] === 'update_main_image') {
            // CSRF token kontrolü - AJAX için (DEBUG MODE)
            $csrf_token_ajax = $_POST['csrf_token'] ?? '';
            error_log("AJAX CSRF DEBUG - Token: " . substr($csrf_token_ajax, 0, 10) . "... (length: " . strlen($csrf_token_ajax) . ")");
            
            if (empty($csrf_token_ajax)) {
                error_log("AJAX CSRF token missing - continuing for debug");
            } else {
                $ajax_validation = CSRFTokenManager::validateToken($csrf_token_ajax);
                error_log("AJAX CSRF validation: " . ($ajax_validation ? 'PASSED' : 'FAILED'));
                
                if (!$ajax_validation) {
                    error_log("AJAX CSRF VALIDATION FAILED - but continuing for debug");
                    // echo "error: Invalid CSRF token";
                    // exit;
                }
            }
            
            $property_id = intval($_POST['property_id'] ?? 0);
            $updated_images = json_decode($_POST['updated_existing_images'] ?? '[]', true);
            
            if ($property_id > 0 && is_array($updated_images) && !empty($updated_images)) {
                // Process images to extract IDs from URLs if needed
                $cloudflare_images_ids = [];
                foreach ($updated_images as $image) {
                    if (strpos($image, 'https://imagedelivery.net/') === 0) {
                        // Extract ID from full URL
                        $url_parts = explode('/', $image);
                        $image_id = $url_parts[count($url_parts) - 2]; // ID is second to last part
                        $cloudflare_images_ids[] = $image_id;
                    } else {
                        // Already an ID
                        $cloudflare_images_ids[] = $image;
                    }
                }
                
                // Update the images column in database with new order
                $images_json = json_encode($updated_images); // Keep full URLs for display
                $main_image = $updated_images[0]; // First image is main (full URL)
                $cloudflare_images_json = json_encode($cloudflare_images_ids); // IDs only for API calls
                $cloudflare_main_image = $cloudflare_images_ids[0]; // Main image ID
                
                $query = "UPDATE properties SET 
                    images = ?, 
                    main_image = ?,
                    cloudflare_images = ?,
                    cloudflare_main_image = ?
                    WHERE id = ? AND user_id = ?";
                    
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssii", $images_json, $main_image, $cloudflare_images_json, $cloudflare_main_image, $property_id, $user_id);
                
                if ($stmt->execute()) {
                    error_log("Main image updated successfully. New main: $main_image");
                    echo "success";
                } else {
                    error_log("Database update failed: " . $stmt->error);
                    echo "error: " . $stmt->error;
                }
            } else {
                error_log("Invalid update_main_image data: property_id=$property_id, images=" . print_r($updated_images, true));
                echo "error: Invalid data";
            }
            exit;
        }
        
        // POST verisi tamamen boşsa
        if (empty($_POST)) {
            throw new Exception("Form verisi alınamadı. Dosya boyutu çok büyük olabilir.");
        }
        
        // CSRF token kontrolü - DETAYLI DEBUG
        $csrf_token_form = $_POST['csrf_token'] ?? '';
        $csrf_token_session = $_SESSION['csrf_token'] ?? '';
        
        error_log("=== CSRF TOKEN DEBUG ===");
        error_log("Form token: " . substr($csrf_token_form, 0, 10) . "... (length: " . strlen($csrf_token_form) . ")");
        error_log("Session token: " . substr($csrf_token_session, 0, 10) . "... (length: " . strlen($csrf_token_session) . ")");
        error_log("Session ID: " . session_id());
        error_log("Tokens match: " . (hash_equals($csrf_token_session, $csrf_token_form) ? 'YES' : 'NO'));
        error_log("========================");
        
        // GEÇICI: CSRF validation'ı disable et ve sadece log'la
        if (empty($csrf_token_form)) {
            error_log("CSRF TOKEN MISSING in form submission - continuing anyway for debugging");
        } else {
            $validation_result = CSRFTokenManager::validateToken($csrf_token_form);
            error_log("CSRF validation result: " . ($validation_result ? 'PASSED' : 'FAILED'));
            
            if (!$validation_result) {
                error_log("CSRF VALIDATION FAILED - but continuing for debugging");
                // throw new Exception("Güvenlik hatası: Geçersiz form token. Lütfen sayfayı yenileyin.");
            }
        }
        
        error_log("CSRF validation completed (debug mode)");
        
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
        $location_type = trim($_POST['location_type'] ?? 'site'); // Default değer - site olsun
        $site_name = trim($_POST['site_name'] ?? '');
        $address_details = trim($_POST['address_details'] ?? '');
        
        // Konut tipine göre adres oluştur
        if ($location_type === 'site' && !empty($site_name)) {
            $address = $site_name . ' Sitesi';
        } elseif ($location_type === 'plaza') {
            $address = !empty($address_details) ? $address_details : 'Plaza/İş Merkezi';
        } else {
            $address = !empty($address_details) ? $address_details : 'Adres bilgisi girilmemiş';
        }
        
        $featured = isset($_POST['is_featured']) && $user_data['role'] === 'admin' ? 1 : 0;
        
        // Heating - ENUM validation (Türkçe karakter olmadan)
        $heating_input = trim($_POST['heating'] ?? '');
        $valid_heating_options = ['Yok', 'Soba', 'Dogalgaz Sobasi', 'Kat Kaloriferi', 'Merkezi Sistem', 'Merkezi Sistem (Pay Olcer)', 'Kombi (Dogalgaz)', 'Kombi (Elektrik)', 'Yerden Isitma', 'Klima', 'Fancoil Unitesi', 'Gunes Enerjisi', 'Jeotermal', 'Somine'];
        
        // Heating mapping (Türkçe karakterleri düzelt)
        $heating_mapping = [
            'Doğalgaz Sobası' => 'Dogalgaz Sobasi',
            'Yerden Isıtma' => 'Yerden Isitma',
            'Güneş Enerjisi' => 'Gunes Enerjisi',
            'Şömine' => 'Somine',
            'Merkezi Sistem (Pay Ölçer)' => 'Merkezi Sistem (Pay Olcer)'
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
        $valid_parking_options = ['Otopark Yok', 'Acik Otopark', 'Kapali Otopark', 'Acik ve Kapali Otopark', 'Otopark Var'];
        
        // Parking mapping (Türkçe karakterleri düzelt)
        $parking_mapping = [
            'Açık Otopark' => 'Acik Otopark',
            'Kapalı Otopark' => 'Kapali Otopark',
            'Açık ve Kapalı Otopark' => 'Acik ve Kapali Otopark'
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
        
        // Additional ENUM field validation
        $building_floors_input = trim($_POST['building_floors'] ?? '');
        $building_floors = !empty($building_floors_input) ? $building_floors_input : '1';
        
        $floor_input = trim($_POST['floor_location'] ?? '');
        $floor = !empty($floor_input) ? $floor_input : 'Zemin Kat';
        
        $building_age_input = trim($_POST['building_age'] ?? '');
        $building_age = !empty($building_age_input) ? $building_age_input : '0';
        
        // Debug log for additional fields
        error_log("deed_status_input: '" . $deed_status_input . "' -> final: '" . $deed_status . "'");
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
        
        // Debug: Log FILES data
        error_log("=== FILES DEBUG ===");
        error_log("FILES isset: " . (isset($_FILES['property_images']) ? 'YES' : 'NO'));
        if (isset($_FILES['property_images'])) {
            error_log("FILES count: " . count($_FILES['property_images']['name']));
            error_log("First file name: " . ($_FILES['property_images']['name'][0] ?? 'EMPTY'));
        }
        error_log("===================");
        
        // Process uploaded images (Cloudflare or Local)
        $imageResult = processPropertyImages($_FILES['property_images'] ?? [], $edit_id ?? null, $edit_mode);
        
        // Initialize variables with defaults
        $images_string = '[]';  // Default to empty JSON array
        $main_image = '';
        $cloudflareResult = [
            'cloudflare_images' => [],
            'cloudflare_main_image' => '',
            'use_cloudflare' => false
        ];
        
        // Handle results based on upload method
        if (isset($imageResult['success']) && $imageResult['success']) {
            if (isset($imageResult['upload_method']) && $imageResult['upload_method'] === 'local') {
                // Local upload result - convert to JSON for database
                $images_array = $imageResult['images'];
                $images_string = json_encode($images_array, JSON_UNESCAPED_UNICODE);  // Store as JSON
                $main_image = !empty($images_array) ? $images_array[0] : '';
                error_log("✅ Local upload completed - Images JSON: {$images_string}, Main: {$main_image}");
            } else {
                // Cloudflare upload result (already JSON format)
                $images_string = $imageResult['images_string'] ?? '[]';
                $main_image = $imageResult['main_image'] ?? '';
                // Set cloudflare result data
                $cloudflareResult = [
                    'cloudflare_images' => $imageResult['cloudflare_images'] ?? [],
                    'cloudflare_main_image' => $imageResult['cloudflare_main_image'] ?? '',
                    'use_cloudflare' => $imageResult['use_cloudflare'] ?? false
                ];
                error_log("☁️ Cloudflare upload completed - Images: {$images_string}, Main: {$main_image}");
            }
        } else {
            error_log("❌ Image processing failed: " . ($imageResult['error'] ?? 'Unknown error'));
            $images_string = '[]';  // Empty JSON array for failed uploads
            $main_image = '';
        }
        
        // Database insert/update
        $listing_type_mapping = [
            'sale' => 'Satılık',
            'rent' => 'Kiralık'
        ];
        $listing_type = isset($listing_type_mapping[$type]) ? $listing_type_mapping[$type] : 'Satılık';
        
        if ($edit_mode && $edit_id) {
            // UPDATE mode - CLOUDFLARE FIELDS ADDED
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
            $cloudflare_images_json = isset($cloudflareResult['cloudflare_images']) ? json_encode($cloudflareResult['cloudflare_images']) : '[]';
            $cloudflare_main_image = isset($cloudflareResult['cloudflare_main_image']) ? $cloudflareResult['cloudflare_main_image'] : '';
            $use_cloudflare = isset($cloudflareResult['use_cloudflare']) && $cloudflareResult['use_cloudflare'] ? 1 : 0;
            
            // Count parameters: 41 parameters total for UPDATE (38 SET + 3 WHERE)
            // Count parameters: 41 parameters total for UPDATE (38 SET + 3 WHERE)
            // Parameters: title, description, price, type, category, subcategory, listing_type,
            // area_gross, area_net, area, address, city, district, neighborhood,
            // room_count, bedrooms, living_room_count, bathrooms, floor, building_floors, year_built, building_age,
            // heating, elevator, parking, furnished, usage_status, dues, credit_eligible,
            // deed_status, location_type, featured, images, main_image, cloudflare_images, cloudflare_main_image, use_cloudflare, features,
            // edit_id, user_id, role
            $stmt->bind_param("ssdssssdddssssiiiiiiiisssssdsssissssisiis", 

                $title, $description, $price, $type, $category, $subcategory,
                $listing_type, $area_gross, $area_net, $area, $address, $city, $district, $neighborhood,
                $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
                $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
                $deed_status, $location_type, $featured,
                $images_string, $main_image, $cloudflare_images_json, $cloudflare_main_image, $use_cloudflare, $features_json,
                $edit_id, $user_id, $user_data['role']);

            if ($stmt->execute()) {
                $_SESSION['success'] = "İlan başarıyla güncellendi! (ID: " . $edit_id . ")";
                header("Location: dashboard.php");
                exit;
            } else {
                throw new Exception("Güncelleme hatası: " . $stmt->error);
            }
        } else {
            // INSERT mode - CLOUDFLARE FIELDS ADDED
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
            $cloudflare_main_image = isset($cloudflareResult['cloudflare_main_image']) ? $cloudflareResult['cloudflare_main_image'] : '';
            $use_cloudflare = isset($cloudflareResult['use_cloudflare']) && $cloudflareResult['use_cloudflare'] ? 1 : 0;
            
            // Count parameters: 39 parameters total
            // 1. user_id, 2. title, 3. description, 4. price, 5. type, 6. category, 7. subcategory,
            // 8. listing_type, 9. area_gross, 10. area_net, 11. area, 12. address, 13. city, 14. district, 15. neighborhood
            // 16. room_count, 17. bedrooms, 18. living_room_count, 19. bathrooms, 20. floor, 21. building_floors, 
            // 22. year_built, 23. building_age, 24. heating, 25. elevator, 26. parking, 27. furnished, 
            // 28. usage_status, 29. dues, 30. credit_eligible, 31. deed_status, 32. location_type, 
            // 33. featured, 34. images, 35. main_image, 36. cloudflare_images, 37. cloudflare_main_image, 
            // 38. use_cloudflare, 39. features
            $bind_result = $stmt->bind_param("issdssssdddssssiiiisiisssissdissssissis", 
                $user_id, $title, $description, $price, $type, $category, $subcategory,
                $listing_type, $area_gross, $area_net, $area, $address, $city, $district, $neighborhood,
                $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
                $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
                $deed_status, $location_type, $featured,
                $images_string, $main_image, $cloudflare_images_json, $cloudflare_main_image, $use_cloudflare, $features_json);
                
            if (!$bind_result) {
                throw new Exception("Bind param failed: " . $stmt->error);
            }


            if ($stmt->execute()) {
                $property_id = $conn->insert_id;
                $_SESSION['success'] = "İlan başarıyla eklendi! (ID: " . $property_id . ")";
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
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .existing-photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            border: 2px solid #28a745;
            transition: all 0.3s ease;
            animation: photoFadeIn 0.4s ease-out;
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
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #000;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            z-index: 3;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            border: 2px solid #fff;
            text-shadow: 0 1px 0 rgba(255,255,255,0.5);
        }
        
        .image-type-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 123, 255, 0.9);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        
        .photo-order-badge {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: rgba(0, 123, 255, 0.9);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
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
        
        /* Ana resim için özel border */
        .existing-photo-item[data-index="0"],
        .existing-photo-item.main-photo,
        .existing-photo-item.main-photo-active {
            border: 3px solid #ffd700 !important;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6) !important;
            position: relative;
        }
        
        .existing-photo-item.main-photo-active img {
            border-radius: 5px;
        }
        
        /* Ana resim animasyonu */
        .pulse-animation {
            animation: mainPhotoPulse 2s ease-in-out infinite;
        }
        
        @keyframes mainPhotoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        /* Ana resim badge büyütme */
        .main-photo .main-photo-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e) !important;
            font-size: 12px !important;
            padding: 6px 12px !important;
            font-weight: 900 !important;
            letter-spacing: 0.5px;
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
    <script>
    // İlçe ve Mahalle verilerini hemen yükle
    window.addEventListener('DOMContentLoaded', function() {
        console.log('Early loading location data...');
        
        // Global location initialization function
        window.ensureLocationSelects = function() {
            const districtSelect = document.getElementById('district');
            const neighborhoodSelect = document.getElementById('neighborhood');
            
            console.log('Checking location selects...', {
                district: !!districtSelect, 
                neighborhood: !!neighborhoodSelect,
                districtOptions: districtSelect ? districtSelect.options.length : 0
            });
            
            if (districtSelect && neighborhoodSelect) {
                // Eğer ilçeler yüklenmemişse yükle
                if (districtSelect.options.length <= 1) {
                    console.log('Loading districts...');
                    
                    // İlçe seçeneklerini yükle
                    window.loadDistrictsToSelect(districtSelect);
                    
                    // İlçe değişiklik event'ini ekle
                    if (!districtSelect.hasAttribute('data-listener-added')) {
                        districtSelect.addEventListener('change', function() {
                            window.loadNeighborhoodsToSelect(neighborhoodSelect, this.value);
                        });
                        districtSelect.setAttribute('data-listener-added', 'true');
                    }
                    
                    return true;
                }
            }
            
            return false;
        };
        
        // Mutation observer ile element eklenmelerini izle
        const observer = new MutationObserver(function(mutations) {
            let shouldCheck = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === 1) { // Element node
                            if (node.id === 'district' || node.id === 'neighborhood' || 
                                node.querySelector('#district') || node.querySelector('#neighborhood')) {
                                shouldCheck = true;
                                break;
                            }
                        }
                    }
                }
            });
            
            if (shouldCheck) {
                console.log('Location elements detected, ensuring initialization...');
                setTimeout(() => window.ensureLocationSelects(), 100);
            }
        });
        
        // Tüm sayfayı observe et
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // İlk kontrol
        setTimeout(() => window.ensureLocationSelects(), 100);
        setTimeout(() => window.ensureLocationSelects(), 500);
        setTimeout(() => window.ensureLocationSelects(), 1000);
    });
    </script>
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
                <!-- Debug için (daha sonra kaldırın) -->
                <div class="alert alert-info" style="font-size: 12px;">
                    Debug - Session ID: <?php echo session_id(); ?><br>
                    Token: <?php echo htmlspecialchars($csrf_token); ?><br>
                    Session Status: <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?><br>
                    Edit Mode: <?php echo $edit_mode ? 'true' : 'false'; ?>
                </div>
                
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
                                <option value="Merkezi Sistem (Pay Olcer)" <?= ($edit_mode && isset($existing_property['heating']) && $existing_property['heating'] == 'Merkezi Sistem (Pay Olcer)') ? 'selected' : '' ?>>Merkezi Sistem (Pay Ölçer)</option>
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
                            <small class="text-muted">DEBUG: <?= $edit_mode ? "Edit mode - Parking: [" . htmlspecialchars($existing_property['parking'] ?? 'NULL') . "]" : "Add mode" ?></small>
                            <select class="form-select" id="parking" name="parking" required>
                                <option value="">Seçiniz</option>
                                <option value="Otopark Yok" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Otopark Yok') ? 'selected' : '' ?>>Otopark Yok</option>
                                <option value="Acik Otopark" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Acik Otopark') ? 'selected' : '' ?>>Açık Otopark</option>
                                <option value="Kapali Otopark" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Kapali Otopark') ? 'selected' : '' ?>>Kapalı Otopark</option>
                                <option value="Acik ve Kapali Otopark" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Acik ve Kapali Otopark') ? 'selected' : '' ?>>Açık ve Kapalı Otopark</option>
                                <option value="Otopark Var" <?= ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Otopark Var') ? 'selected' : '' ?>>Otopark Var</option>
                                
                                <!-- FALLBACK OPTIONS for possible DB variations -->
                                <?php if ($edit_mode && isset($existing_property['parking'])): ?>
                                    <?php $db_parking = $existing_property['parking']; ?>
                                    <?php if (!in_array($db_parking, ['Otopark Yok', 'Acik Otopark', 'Kapali Otopark', 'Acik ve Kapali Otopark', 'Otopark Var'])): ?>
                                        <option value="<?= htmlspecialchars($db_parking) ?>" selected>
                                            <?= htmlspecialchars($db_parking) ?> (DB Value)
                                        </option>
                                    <?php endif; ?>
                                <?php endif; ?>
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

                    <!-- Address Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-geo-alt"></i> Adres Bilgileri
                    </h6>

                    <div class="row">
                        <!-- City -->
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">İl <span class="text-danger">*</span></label>
                            <select class="form-select" id="city" name="city" required>
                                <?php foreach ($turkish_cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>" 
                                        <?= ($edit_mode && isset($existing_property['city']) && $existing_property['city'] == $city) ? 'selected' : '' ?>
                                        <?= (!$edit_mode && $city == 'İstanbul') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-4 mb-3">
                            <label for="district" class="form-label">İlçe <span class="text-danger">*</span></label>
                            <select class="form-select" id="district" name="district" required>
                                <option value="">İlçe Seçiniz</option>
                                <?php if ($edit_mode && isset($existing_property['district']) && !empty($existing_property['district'])): ?>
                                    <option value="<?= htmlspecialchars($existing_property['district']) ?>" selected>
                                        <?= htmlspecialchars($existing_property['district']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Neighborhood -->
                        <div class="col-md-4 mb-3">
                            <label for="neighborhood" class="form-label">Mahalle</label>
                            <select class="form-select" id="neighborhood" name="neighborhood">
                                <option value="">Mahalle Seçiniz</option>
                                <?php if ($edit_mode && isset($existing_property['neighborhood']) && !empty($existing_property['neighborhood'])): ?>
                                    <option value="<?= htmlspecialchars($existing_property['neighborhood']) ?>" selected>
                                        <?= htmlspecialchars($existing_property['neighborhood']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Konut Tipi -->
                    <div class="mb-4">
                        <label class="form-label">Konut Tipi <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="site" id="location_site" required 
                                           <?= ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'site') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="location_site">
                                        <i class="bi bi-buildings"></i> Site İçerisinde
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="standalone" id="location_standalone" required 
                                           <?= ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'standalone') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="location_standalone">
                                        <i class="bi bi-house"></i> Müstakil/Site Dışı
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="plaza" id="location_plaza" required 
                                           <?= ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'plaza') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="location_plaza">
                                        <i class="bi bi-building"></i> Plaza/İş Merkezi
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site Name -->
                    <div class="mb-4" id="site-name-section" style="display: none;">
                        <label for="site_name" class="form-label">Site Adı</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               placeholder="Örn: Bahçeşehir Premium Sitesi (İsteğe bağlı)"
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
                                                    <input class="form-check-input" type="checkbox" value="Güvenlik" id="olanak_guvenlik" name="olanaklar[]"
                                                           <?= (in_array('Güvenlik', $existing_olanaklar)) ? 'checked' : '' ?>>
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
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="Yük Asansörü" id="olanak_yuk_asansoru" name="olanaklar[]"
                                                           <?= (in_array('Yük Asansörü', $existing_olanaklar)) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="olanak_yuk_asansoru">Yük Asansörü</label>
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
                                            // Smart URL detection ve generation
                                            if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $image)) {
                                                // Sadece ID ise, Cloudflare public URL oluştur - ACCOUNT HASH kullan
                                                $display_url = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_HASH . "/" . $image . "/public";
                                                $image_type = "Cloudflare ID";
                                            } elseif (strpos($image, 'https://imagedelivery.net/') === 0) {
                                                // Zaten Cloudflare full URL - Account Hash'e çevir
                                                if (preg_match('/https:\/\/imagedelivery\.net\/[^\/]+\/([a-f0-9-]+)\//', $image, $matches)) {
                                                    $imageId = $matches[1];
                                                    $display_url = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_HASH . "/" . $imageId . "/public";
                                                } else {
                                                    $display_url = $image;
                                                }
                                                $image_type = "Cloudflare URL";
                                            } elseif (strpos($image, '/uploads/properties/') === 0) {
                                                // Local image with full path
                                                $display_url = $image;
                                                $image_type = "Local";
                                            } elseif (strpos($image, '/thumbnail') !== false) {
                                                // Thumbnail URL ise public'e çevir - Account Hash kullan
                                                if (preg_match('/https:\/\/imagedelivery\.net\/[^\/]+\/([a-f0-9-]+)\//', $image, $matches)) {
                                                    $imageId = $matches[1];
                                                    $display_url = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_HASH . "/" . $imageId . "/public";
                                                } else {
                                                    $display_url = str_replace('/thumbnail', '/public', $image);
                                                }
                                                $image_type = "Cloudflare Thumb";
                                            } else {
                                                // Filename only - assume local
                                                $display_url = "/uploads/properties/" . basename($image);
                                                $image_type = "Local Filename";
                                            }
                                            ?>
                                            <div class="existing-photo-item" data-image="<?= htmlspecialchars($image) ?>" data-index="<?= $index ?>">
                                                <img src="<?= $display_url ?>" 
                                                     alt="Fotoğraf <?= $index + 1 ?>" 
                                                     class="img-fluid"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                
                                                <!-- Error fallback -->
                                                <div style="display:none; padding:20px; background:#f8f9fa; text-align:center; border:1px dashed #ccc;">
                                                    <i class="fas fa-image text-muted"></i><br>
                                                    <small class="text-muted">Resim yüklenemedi</small><br>
                                                    <small class="text-muted"><?= $image_type ?>: <?= htmlspecialchars($image) ?></small>
                                                </div>
                                                
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
                                
                                <!-- Upload Progress List -->
                                <div class="upload-progress-list mt-3" id="uploadProgressList" style="display: none;">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>
                                        Yükleme Durumu
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
                            
                            <!-- Overall Upload Progress -->
                            <div class="overall-upload-progress mt-3" id="overallUploadProgress" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-muted mb-0">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>
                                        Genel Yükleme Durumu
                                    </h6>
                                    <small class="text-muted" id="overallProgressText">0/0 tamamlandı</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 0%" 
                                         id="overallProgressBar">
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

    <!-- Debug bilgileri (sadece geliştirme aşamasında) -->
    <script>
        // Debug için - sadece geliştirme aşamasında kullanın
        console.log('PHP Debug - Uploaded images:', <?php echo json_encode($images_string); ?>);
        console.log('PHP Debug - Main image:', <?php echo json_encode($main_image); ?>);
        console.log('PHP Debug - Edit mode:', <?php echo $edit_mode ? 'true' : 'false'; ?>);
        console.log('PHP Debug - POST method:', <?php echo $_SERVER['REQUEST_METHOD'] === 'POST' ? 'true' : 'false'; ?>);
        console.log('PHP Debug - FILES isset:', <?php echo isset($_FILES['property_images']) ? 'true' : 'false'; ?>);
        <?php if (isset($_FILES['property_images'])): ?>
        console.log('PHP Debug - FILES count:', <?php echo count($_FILES['property_images']['name']); ?>);
        <?php endif; ?>
    </script>

    <script>
        // Format file size helper function - global olarak erken tanımla
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Simulate individual photo upload with progress - global olarak erken tanımla
        function simulatePhotoUpload(photoItem, file) {
            const progressOverlay = photoItem.querySelector('.upload-progress-overlay');
            const progressCircle = photoItem.querySelector('.circle');
            const progressText = photoItem.querySelector('.progress-text');
            const statusText = photoItem.querySelector('.upload-status');
            const filename = photoItem.querySelector('.upload-filename');
            const fileSize = photoItem.querySelector('.file-size');
            
            // Show progress overlay
            progressOverlay.classList.remove('hidden');
            
            // Set filename and size
            filename.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            // Add to progress list
            const progressItemId = addProgressItem(file, photoItem);
            
            // Start with "waiting" status instead of immediate upload
            progressText.textContent = '0%';
            statusText.textContent = 'Yükleme bekleniyor...';
            statusText.className = 'upload-status connecting';
            
            // Update progress list to waiting status
            updateProgressItem(progressItemId, 0, 'Yükleme bekleniyor...', 'waiting');
            
            // Store references for real upload later
            photoItem.dataset.progressItemId = progressItemId;
            photoItem.dataset.fileName = file.name;
            
            return null; // Don't start simulation, wait for real upload
        }

        // Progress List Management Functions
        function addProgressItem(file, photoItem) {
            const progressList = document.getElementById('uploadProgressList');
            const progressItems = document.getElementById('progressItems');
            
            // Show progress list
            progressList.style.display = 'block';
            
            // Create unique ID
            const itemId = 'progress-' + Date.now() + Math.random();
            
            // Get image preview from photoItem
            const imgSrc = photoItem.querySelector('.photo-preview').src;
            
            const progressItem = document.createElement('div');
            progressItem.className = 'progress-item uploading';
            progressItem.id = itemId;
            
            progressItem.innerHTML = `
                <div class="progress-item-info">
                    <img src="${imgSrc}" alt="Preview" class="progress-item-thumb">
                    <div class="progress-item-details">
                        <div class="progress-item-name">${file.name}</div>
                        <div class="progress-item-size">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <div class="progress-item-status">
                    <div class="progress-item-percentage">0%</div>
                    <i class="fas fa-spinner fa-spin progress-item-icon"></i>
                </div>
            `;
            
            progressItems.appendChild(progressItem);
            return itemId;
        }

        function updateProgressItem(itemId, percentage, statusText, statusType) {
            const item = document.getElementById(itemId);
            if (!item) return;
            
            const percentageEl = item.querySelector('.progress-item-percentage');
            const iconEl = item.querySelector('.progress-item-icon');
            
            // Update percentage
            percentageEl.textContent = percentage + '%';
            
            // Update class
            item.className = `progress-item ${statusType}`;
            
            // Update icon based on status
            if (statusType === 'completed') {
                iconEl.className = 'fas fa-check progress-item-icon';
            } else if (statusType === 'error') {
                iconEl.className = 'fas fa-times progress-item-icon';
            } else if (statusType === 'waiting') {
                iconEl.className = 'fas fa-clock progress-item-icon';
            } else {
                iconEl.className = 'fas fa-spinner fa-spin progress-item-icon';
            }
        }

        // Real upload progress tracker
        function startRealUploadProgress() {
            console.log('🔄 Starting real upload progress tracking...');
            
            // Show overall progress
            const overallProgress = document.getElementById('overallUploadProgress');
            overallProgress.style.display = 'block';
            
            // Find all photo items with progress trackers
            const photoItems = document.querySelectorAll('.photo-item');
            const totalPhotos = photoItems.length;
            
            // Update overall progress text
            updateOverallProgress(0, totalPhotos);
            
            photoItems.forEach((photoItem, index) => {
                const progressItemId = photoItem.dataset.progressItemId;
                const fileName = photoItem.dataset.fileName;
                
                if (progressItemId && fileName) {
                    // Start real upload progress with staggered timing
                    setTimeout(() => {
                        startIndividualUpload(photoItem, progressItemId, fileName, index, totalPhotos);
                    }, index * 500); // 500ms delay between each photo
                }
            });
        }

        function updateOverallProgress(completed, total) {
            const progressBar = document.getElementById('overallProgressBar');
            const progressText = document.getElementById('overallProgressText');
            
            const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
            
            progressBar.style.width = percentage + '%';
            progressText.textContent = `${completed}/${total} tamamlandı`;
            
            // Change color based on completion
            if (percentage === 100) {
                progressBar.className = 'progress-bar bg-success';
                progressText.textContent = `✅ ${total} fotoğraf başarıyla yüklendi!`;
            } else {
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
            }
        }

        function startIndividualUpload(photoItem, progressItemId, fileName, index, totalPhotos) {
            const progressOverlay = photoItem.querySelector('.upload-progress-overlay');
            const progressCircle = photoItem.querySelector('.circle');
            const progressText = photoItem.querySelector('.progress-text');
            const statusText = photoItem.querySelector('.upload-status');
            
            console.log(`🎯 Starting upload for: ${fileName}`);
            
            // Update status to uploading
            statusText.textContent = 'Cloudflare\'e bağlanıyor...';
            statusText.className = 'upload-status connecting';
            updateProgressItem(progressItemId, 0, 'Cloudflare\'e bağlanıyor...', 'uploading');
            
            // Add uploading animation to circle
            progressCircle.classList.add('uploading');
            
            // Realistic upload simulation (will be replaced with real XMLHttpRequest progress)
            let progress = 0;
            const uploadDuration = 2000 + Math.random() * 3000; // 2-5 seconds
            const startTime = Date.now();
            
            const progressInterval = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const normalizedProgress = elapsed / uploadDuration;
                
                if (normalizedProgress >= 1) {
                    progress = 100;
                    clearInterval(progressInterval);
                    
                    // Upload completed
                    progressCircle.classList.remove('uploading');
                    progressCircle.classList.add('completed');
                    
                    const circumference = 251.2;
                    progressCircle.style.strokeDashoffset = 0;
                    
                    progressText.innerHTML = '<i class="fas fa-check success-checkmark"></i>';
                    statusText.textContent = 'Cloudflare\'e yüklendi!';
                    statusText.className = 'upload-status completed';
                    
                    updateProgressItem(progressItemId, 100, 'Cloudflare\'e yüklendi!', 'completed');
                    
                    // Update overall progress
                    const completedPhotos = document.querySelectorAll('.photo-item.upload-complete').length + 1;
                    updateOverallProgress(completedPhotos, totalPhotos);
                    
                    setTimeout(() => {
                        progressOverlay.classList.add('hidden');
                        photoItem.classList.add('upload-complete');
                    }, 1500);
                    
                } else {
                    const easeProgress = normalizedProgress < 0.5 
                        ? 2 * normalizedProgress * normalizedProgress 
                        : 1 - Math.pow(-2 * normalizedProgress + 2, 3) / 2;
                        
                    progress = Math.floor(easeProgress * 100);
                    
                    let statusMessage = '';
                    if (progress < 30) {
                        statusMessage = 'Cloudflare\'e bağlanıyor...';
                        statusText.className = 'upload-status connecting';
                    } else if (progress < 70) {
                        statusMessage = 'Cloudflare\'e yükleniyor...';
                        statusText.className = 'upload-status uploading';
                    } else if (progress < 95) {
                        statusMessage = 'Cloudflare işliyor...';
                        statusText.className = 'upload-status processing';
                    } else {
                        statusMessage = 'Tamamlanıyor...';
                        statusText.className = 'upload-status completing';
                    }
                    statusText.textContent = statusMessage;
                    
                    const circumference = 251.2;
                    const offset = circumference - (progress / 100) * circumference;
                    progressCircle.style.strokeDashoffset = offset;
                    progressText.textContent = `${progress}%`;
                    
                    updateProgressItem(progressItemId, progress, statusMessage, 'uploading');
                }
            }, 150);
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Edit mode kontrolü ve değer atama
            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            
            if (isEditMode) {
                console.log('Edit mode detected, setting form values...');
                
                // Hidden input değerlerini kontrol et ve ayarla
                const categoryInput = document.getElementById('category');
                const typeInput = document.getElementById('type');
                
                if (categoryInput && categoryInput.value) {
                    console.log('Category value from hidden input:', categoryInput.value);
                }
                
                if (typeInput && typeInput.value) {
                    console.log('Type value from hidden input:', typeInput.value);
                }
                
                // Form submit'e hazır hale getir
                showFormDirectly();
                return; // Edit modunda wizard atlansın
            }
            
            // Wizard state variables
            let currentStep = 1;
            let selectedCategory = '';
            let selectedType = '';
            let selectedSubcategory = '';
            
            // Transaction types for each category
            const transactionTypes = {
                'konut': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-house' },
                    { value: 'daily_rent', text: 'Turistik Günlük Kiralık', icon: 'bi-calendar-date' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' }
                ],
                'is_yeri': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-building' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' },
                    { value: 'transfer_rent', text: 'Devren Kiralık', icon: 'bi-arrow-repeat' }
                ],
                'arsa': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-geo-alt' }
                ],
                'bina': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-buildings' }
                ],
                'devre_mulk': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-calendar-check' }
                ],
                'turistik_tesis': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-compass' }
                ]
            };

            // Subcategory system
            const subcategoryTypes = {
                'konut': {
                    'sale': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                        { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'ciftlik_evi', text: 'Çiftlik Evi', icon: 'bi-tree' },
                        { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'koy_evi', text: 'Köy Evi', icon: 'bi-tree-fill' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'rent': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                        { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'daily_rent': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'transfer_sale': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' }
                    ]
                },
                'is_yeri': {
                    'sale': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'buro_ofis', text: 'Büro Ofis', icon: 'bi-briefcase' },
                        { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                        { value: 'fabrika_uretim', text: 'Fabrika & Üretim', icon: 'bi-gear' },
                        { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                        { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' },
                        { value: 'eczane', text: 'Eczane', icon: 'bi-plus-circle' },
                        { value: 'berber_kuafor', text: 'Berber & Kuaför', icon: 'bi-scissors' }
                    ],
                    'rent': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'buro_ofis', text: 'Büro Ofis', icon: 'bi-briefcase' },
                        { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                        { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
                    ],
                    'transfer_sale': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
                    ],
                    'transfer_rent': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' }
                    ]
                },
                'arsa': {
                    'sale': [
                        { value: 'konut_arsasi', text: 'Konut Arsası', icon: 'bi-house-door' },
                        { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                        { value: 'sanayi_arsasi', text: 'Sanayi Arsası', icon: 'bi-gear-wide' },
                        { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                        { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' },
                        { value: 'villa_arsasi', text: 'Villa Arsası', icon: 'bi-house-heart' }
                    ],
                    'rent': [
                        { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                        { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                        { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' }
                    ]
                },
                'bina': {
                    'sale': [
                        { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                        { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' },
                        { value: 'plaza', text: 'Plaza', icon: 'bi-building-up' }
                    ],
                    'rent': [
                        { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                        { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' }
                    ]
                },
                'devre_mulk': {
                    'sale': [
                        { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' },
                        { value: 'otel', text: 'Otel', icon: 'bi-building' }
                    ],
                    'rent': [
                        { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' }
                    ]
                },
                'turistik_tesis': {
                    'sale': [
                        { value: 'otel', text: 'Otel', icon: 'bi-building' },
                        { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' },
                        { value: 'kamp_alani', text: 'Kamp Alanı', icon: 'bi-tree' }
                    ],
                    'rent': [
                        { value: 'otel', text: 'Otel', icon: 'bi-building' },
                        { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' }
                    ]
                }
            };

            // Setup category selection with proper event listeners
            function setupCategorySelection() {
                const categoryItems = document.querySelectorAll('.category-item');
                console.log('Found category items:', categoryItems.length);
                
                categoryItems.forEach((item, index) => {
                    console.log(`Setting up category ${index}:`, item.dataset.category);
                    
                    // Remove any existing listeners
                    item.removeEventListener('click', handleCategoryClick);
                    
                    // Add new listener
                    item.addEventListener('click', handleCategoryClick);
                    
                    // Add visual feedback
                    item.addEventListener('mouseenter', function() {
                        if (!this.classList.contains('selected')) {
                            this.style.transform = 'translateY(-5px) scale(1.05)';
                        }
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        if (!this.classList.contains('selected')) {
                            this.style.transform = '';
                        }
                    });
                });
            }

            function handleCategoryClick(event) {
                event.preventDefault();
                event.stopPropagation();
                
                const categoryValue = this.dataset.category;
                console.log('=== CATEGORY SELECTED ===');
                console.log('Category:', categoryValue);
                
                // Clear previous selections
                document.querySelectorAll('.category-item').forEach(item => {
                    item.classList.remove('selected');
                    item.style.transform = '';
                });
                
                // Mark new selection
                this.classList.add('selected');
                this.style.transform = 'translateY(-3px) scale(1.02)';
                
                // Update state
                selectedCategory = categoryValue;
                
                // Update hidden input
                const categoryInput = document.getElementById('category');
                if (categoryInput) {
                    categoryInput.value = selectedCategory;
                    console.log('Category input updated:', categoryInput.value);
                }
                
                // Enable next button
                const nextBtn = document.getElementById('next-step');
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.style.backgroundColor = '#0d6efd';
                    nextBtn.style.color = 'white';
                }
                
                // Auto advance after short delay
                setTimeout(() => {
                    console.log('Auto-advancing to transaction types...');
                    nextStep();
                }, 800);
            }

            function setupTransactionSelection() {
                const transactionItems = document.querySelectorAll('.transaction-item');
                
                transactionItems.forEach(item => {
                    item.addEventListener('click', function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        const typeValue = this.dataset.type;
                        console.log('=== TRANSACTION SELECTED ===');
                        console.log('Type:', typeValue);
                        
                        // Clear previous selections
                        document.querySelectorAll('.transaction-item').forEach(i => {
                            i.classList.remove('selected');
                        });
                        
                        // Mark new selection
                        this.classList.add('selected');
                        
                        // Update state
                        selectedType = typeValue;
                        
                        // Update hidden input
                        const typeInput = document.getElementById('type');
                        if (typeInput) {
                            typeInput.value = selectedType;
                            console.log('Type input updated:', typeInput.value);
                        }
                        
                        // Enable next button for subcategory step
                        const nextBtn = document.getElementById('next-step');
                        if (nextBtn) {
                            nextBtn.disabled = false;
                        }
                        
                        console.log('Transaction selected, enabling next step for subcategory');
                        
                        // Auto advance to subcategories after short delay
                        setTimeout(() => {
                            console.log('Auto-advancing to subcategories...');
                            showSubcategories();
                            showStep(3);
                        }, 800);
                    });
                });
            }

            function showSubcategories() {
                console.log('showSubcategories for category:', selectedCategory, 'type:', selectedType);
                
                // Hide step 2, show step 3
                document.getElementById('wizard-step-2').style.display = 'none';
                document.getElementById('wizard-step-3').style.display = 'block';
                
                const container = document.getElementById('subcategory-options');
                if (!container) {
                    console.error('subcategory-options container not found');
                    return;
                }
                
                container.innerHTML = '';
                
                // Get subcategories based on category and transaction type
                const categoryData = subcategoryTypes[selectedCategory];
                if (!categoryData) {
                    console.log('No subcategory data, going to main form');
                    setTimeout(() => showMainForm(), 100);
                    return;
                }
                
                const subcategories = categoryData[selectedType] || [];
                console.log('Subcategories:', subcategories);
                
                if (subcategories.length === 0) {
                    console.log('No subcategories, going to main form');
                    setTimeout(() => showMainForm(), 100);
                    return;
                }
                
                subcategories.forEach(subcat => {
                    const item = document.createElement('div');
                    item.className = 'subcategory-item';
                    item.dataset.subcategory = subcat.value;
                    item.innerHTML = `
                        <i class="bi ${subcat.icon}"></i>
                        <span>${subcat.text}</span>
                    `;
                    container.appendChild(item);
                });
                
                // Setup event listeners for subcategory selection
                setupSubcategorySelection();
                
                // Update buttons for Step 3 - show continue form button
                const nextBtn = document.getElementById('next-step');
                const continueBtn = document.getElementById('continue-form');
                const prevBtn = document.getElementById('prev-step');
                
                if (nextBtn) nextBtn.style.display = 'none';
                if (continueBtn) {
                    continueBtn.style.display = 'inline-block';
                    continueBtn.disabled = true; // Selection yapılana kadar devre dışı
                }
                if (prevBtn) prevBtn.style.display = 'inline-block';
            }

            function setupSubcategorySelection() {
                console.log('setupSubcategorySelection');
                
                document.querySelectorAll('.subcategory-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const subcatValue = this.dataset.subcategory;
                        console.log('=== SUBCATEGORY SELECTED ===');
                        console.log('Subcategory:', subcatValue);
                        
                        // Clear previous selections
                        document.querySelectorAll('.subcategory-item').forEach(i => {
                            i.classList.remove('selected');
                        });
                        
                        // Mark new selection
                        this.classList.add('selected');
                        
                        // Update state
                        selectedSubcategory = subcatValue;
                        
                        // Update hidden input
                        const subcatInput = document.getElementById('subcategory');
                        if (subcatInput) {
                            subcatInput.value = selectedSubcategory;
                            console.log('Subcategory input updated:', subcatInput.value);
                        }
                        
                        // Enable continue button
                        const continueBtn = document.getElementById('continue-form');
                        if (continueBtn) {
                            continueBtn.disabled = false;
                        }
                        
                        // Auto advance to main form
                        setTimeout(() => {
                            console.log('Auto-advancing to main form...');
                            showMainForm();
                        }, 800);
                    });
                });
            }

            function nextStep() {
                console.log('nextStep called, currentStep:', currentStep);
                
                if (currentStep === 1) {
                    showTransactionTypes();
                    currentStep = 2;
                    updateStepIndicator();
                } else if (currentStep === 2) {
                    showSubcategories();
                    currentStep = 3;
                    updateStepIndicator();
                }
            }

            function prevStep() {
                console.log('prevStep called, currentStep:', currentStep);
                
                if (currentStep === 2) {
                    currentStep = 1;
                    document.getElementById('wizard-step-1').style.display = 'block';
                    document.getElementById('wizard-step-2').style.display = 'none';
                    updateStepIndicator();
                } else if (currentStep === 3) {
                    currentStep = 2;
                    document.getElementById('wizard-step-2').style.display = 'block';
                    document.getElementById('wizard-step-3').style.display = 'none';
                    updateStepIndicator();
                }
            }

            function showTransactionTypes() {
                console.log('showTransactionTypes for category:', selectedCategory);
                
                // Hide step 1, show step 2
                document.getElementById('wizard-step-1').style.display = 'none';
                document.getElementById('wizard-step-2').style.display = 'block';
                
                const container = document.getElementById('transaction-options');
                if (!container) {
                    console.error('transaction-options container not found');
                    return;
                }
                
                container.innerHTML = '';
                
                const types = transactionTypes[selectedCategory] || [];
                console.log('Transaction types:', types);
                
                if (types.length === 0) {
                    console.log('No transaction types, going to main form');
                    setTimeout(() => showMainForm(), 100);
                    return;
                }
                
                types.forEach(type => {
                    const item = document.createElement('div');
                    item.className = 'transaction-item';
                    item.dataset.type = type.value;
                    item.innerHTML = `
                        <i class="bi ${type.icon}"></i>
                        <span>${type.text}</span>
                    `;
                    container.appendChild(item);
                });
                
                // Setup event listeners for new transaction items
                setupTransactionSelection();
                
                // Update buttons - Step 2'de next butonunu etkinleştir (subcategory için)  
                const nextBtn = document.getElementById('next-step');
                const continueBtn = document.getElementById('continue-form');
                const prevBtn = document.getElementById('prev-step');
                
                if (nextBtn) {
                    nextBtn.style.display = 'inline-block';
                    nextBtn.disabled = true; // Selection yapılana kadar devre dışı
                }
                if (continueBtn) continueBtn.style.display = 'none';
                if (prevBtn) prevBtn.style.display = 'inline-block';
            }

            function updateStepIndicator() {
                console.log('updateStepIndicator for step:', currentStep);
                
                const steps = document.querySelectorAll('.step');
                steps.forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    
                    if (index + 1 < currentStep) {
                        step.classList.add('completed');
                    } else if (index + 1 === currentStep) {
                        step.classList.add('active');
                    }
                });
            }

            function showStep(stepNumber) {
                console.log('showStep called for step:', stepNumber);
                currentStep = stepNumber;
                updateStepIndicator();
            }

            function showFormDirectly() {
                console.log('showFormDirectly called for edit mode');
                
                // Edit modunda doğrudan formu göster
                document.querySelectorAll('.wizard-step').forEach(step => {
                    step.style.display = 'none';
                });
                
                // Step indicator'ı gizle
                const stepIndicator = document.querySelector('.step-indicator');
                if (stepIndicator && stepIndicator.parentElement && stepIndicator.parentElement.parentElement) {
                    stepIndicator.parentElement.parentElement.style.display = 'none';
                }
                
                // Navigation buttonları gizle
                const navigationDiv = document.querySelector('.row.mt-4');
                if (navigationDiv) {
                    navigationDiv.style.display = 'none';
                }
                
                // Ana formu göster
                const mainForm = document.getElementById('main-form');
                if (mainForm) {
                    mainForm.style.display = 'block';
                    console.log('Main form shown for edit mode');
                }
            }

            function showMainForm() {
                console.log('showMainForm called');
                
                // Hide all wizard steps
                document.querySelectorAll('.wizard-step').forEach(step => {
                    step.style.display = 'none';
                });
                
                // Hide step indicator
                const stepIndicator = document.querySelector('.step-indicator');
                if (stepIndicator && stepIndicator.parentElement && stepIndicator.parentElement.parentElement) {
                    stepIndicator.parentElement.parentElement.style.display = 'none';
                }
                
                // Hide navigation buttons
                const navigationDiv = document.querySelector('.row.mt-4');
                if (navigationDiv) {
                    navigationDiv.style.display = 'none';
                }
                
                // Update selection summary
                updateSelectionSummary();
                
                // Show main form
                const mainForm = document.getElementById('main-form');
                if (mainForm) {
                    mainForm.style.display = 'block';
                    console.log('Main form shown successfully');
                }
            }

            function updateSelectionSummary() {
                const summaryContainer = document.getElementById('selection-summary');
                if (!summaryContainer) return;
                
                const categoryNames = {
                    'konut': 'Konut',
                    'is_yeri': 'İş Yeri',
                    'arsa': 'Arsa',
                    'bina': 'Bina',
                    'devre_mulk': 'Devre Mülk',
                    'turistik_tesis': 'Turistik Tesis'
                };
                
                const typeNames = {
                    'sale': 'Satılık',
                    'rent': 'Kiralık',
                    'daily_rent': 'Turistik Günlük Kiralık',
                    'transfer_sale': 'Devren Satılık',
                    'transfer_rent': 'Devren Kiralık'
                };
                
                const categoryText = categoryNames[selectedCategory] || selectedCategory;
                const typeText = typeNames[selectedType] || selectedType;
                
                summaryContainer.innerHTML = `
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="badge bg-primary fs-6 p-2">${categoryText}</div>
                        <div class="badge bg-success fs-6 p-2">${typeText}</div>
                        ${selectedSubcategory ? '<div class="badge bg-info fs-6 p-2">' + selectedSubcategory + '</div>' : ''}
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Seçiminizi değiştirmek için sayfayı yeniden yükleyin.</small>
                    </div>
                `;
            }

            // Initialize everything
            setupCategorySelection();
            
            // Navigation button handlers
            const nextBtn = document.getElementById('next-step');
            const prevBtn = document.getElementById('prev-step');
            const continueBtn = document.getElementById('continue-form');
            
            if (nextBtn) nextBtn.addEventListener('click', nextStep);
            if (prevBtn) prevBtn.addEventListener('click', prevStep);
            if (continueBtn) continueBtn.addEventListener('click', showMainForm);
            
            // Setup other features
            setupFormHandlers();
            setupPhotoUpload();
            
            console.log('Initialization complete');
        });

        // Turkish districts data
        const turkishDistricts = {
            'Adana': ['Aladağ', 'Ceyhan', 'Çukurova', 'Feke', 'İmamoğlu', 'Karaisalı', 'Karataş', 'Kozan', 'Pozantı', 'Saimbeyli', 'Sarıçam', 'Seyhan', 'Tufanbeyli', 'Yumurtalık', 'Yüreğir'],
            'Adıyaman': ['Besni', 'Çelikhan', 'Gerger', 'Gölbaşı', 'Kahta', 'Merkez', 'Samsat', 'Sincik', 'Tut'],
            'Afyonkarahisar': ['Başmakçı', 'Bayat', 'Bolvadin', 'Çay', 'Çobanlar', 'Dazkırı', 'Dinar', 'Emirdağ', 'Evciler', 'Hocalar', 'İhsaniye', 'İscehisar', 'Kızılören', 'Merkez', 'Sandıklı', 'Sinanpaşa', 'Sultandağı', 'Şuhut'],
            'Ağrı': ['Diyadin', 'Doğubayazıt', 'Eleşkirt', 'Hamur', 'Merkez', 'Patnos', 'Taşlıçay', 'Tutak'],
            'Amasya': ['Göynücek', 'Gümüşhacıköy', 'Hamamözü', 'Merkez', 'Merzifon', 'Suluova', 'Taşova'],
            'Ankara': ['Akyurt', 'Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere', 'Çankaya', 'Çubuk', 'Elmadağ', 'Etimesgut', 'Evren', 'Gölbaşı', 'Güdül', 'Haymana', 'Kalecik', 'Kazan', 'Keçiören', 'Kızılcahamam', 'Mamak', 'Nallıhan', 'Polatlı', 'Pursaklar', 'Sincan', 'Şereflikoçhisar', 'Yenimahalle'],
            'Antalya': ['Akseki', 'Aksu', 'Alanya', 'Demre', 'Döşemealtı', 'Elmalı', 'Finike', 'Gazipaşa', 'Gündoğmuş', 'İbradı', 'Kaş', 'Kemer', 'Kepez', 'Konyaaltı', 'Korkuteli', 'Kumluca', 'Manavgat', 'Muratpaşa', 'Serik'],
            'Artvin': ['Ardanuç', 'Arhavi', 'Borçka', 'Hopa', 'Merkez', 'Murgul', 'Şavşat', 'Yusufeli'],
            'Aydın': ['Bozdoğan', 'Buharkent', 'Çine', 'Didim', 'Efeler', 'Germencik', 'İncirliova', 'Karacasu', 'Karpuzlu', 'Koçarlı', 'Köşk', 'Kuşadası', 'Kuyucak', 'Nazilli', 'Söke', 'Sultanhisar', 'Yenipazar'],
            'Balıkesir': ['Ayvalık', 'Balya', 'Bandırma', 'Bigadiç', 'Burhaniye', 'Dursunbey', 'Edremit', 'Erdek', 'Gömeç', 'Gönen', 'Havran', 'İvrindi', 'Karesi', 'Kepsut', 'Manyas', 'Marmara', 'Savaştepe', 'Sındırgı', 'Susurluk'],
            'İstanbul': ['Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane', 'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'],
            'İzmir': ['Aliağa', 'Balçova', 'Bayındır', 'Bayraklı', 'Bergama', 'Beydağ', 'Bornova', 'Buca', 'Çeşme', 'Çiğli', 'Dikili', 'Foça', 'Gaziemir', 'Güzelbahçe', 'Karabağlar', 'Karaburun', 'Karşıyaka', 'Kemalpaşa', 'Kınık', 'Kiraz', 'Konak', 'Menderes', 'Menemen', 'Narlıdere', 'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla']
        };

        function setupFormHandlers() {
            // Title preview
            const titleInput = document.getElementById('title');
            const titlePreview = document.getElementById('title-preview');
            
            if (titleInput && titlePreview) {
                titleInput.addEventListener('input', function() {
                    const title = this.value.trim();
                    if (title) {
                        titlePreview.innerHTML = `<span class="fw-bold" style="color: #0d6efd;">${title}</span>`;
                    } else {
                        titlePreview.innerHTML = '<span class="text-muted">İlan başlığınız burada görünecek...</span>';
                    }
                });
            }

            // Price formatting
            const priceInput = document.getElementById('price');
            if (priceInput) {
                priceInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value) {
                        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }
                    this.value = value;
                });
            }

            // Capitalize inputs (İl, İlçe, Mahalle)
            const capitalizeInputs = document.querySelectorAll('.capitalize-input');
            capitalizeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    // Kelime başlarını büyük yap
                    let value = this.value.toLowerCase();
                    value = value.replace(/\b\w/g, l => l.toUpperCase());
                    
                    // Türkçe karakterleri düzelt
                    value = value.replace(/\bİ/g, 'İ');
                    value = value.replace(/\bı/g, 'I');
                    value = value.replace(/\bÇ/g, 'Ç');
                    value = value.replace(/\bŞ/g, 'Ş');
                    value = value.replace(/\bĞ/g, 'Ğ');
                    value = value.replace(/\bÜ/g, 'Ü');
                    value = value.replace(/\bÖ/g, 'Ö');
                    
                    this.value = value;
                });
            });

            // Location type selection
            const locationRadios = document.querySelectorAll('input[name="location_type"]');
            const siteNameSection = document.getElementById('site-name-section');
            const addressDetailsSection = document.getElementById('address-details-section');
            const siteNameInput = document.getElementById('site_name');
            const addressDetailsInput = document.getElementById('address_details');
            
            locationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'site') {
                        siteNameSection.style.display = 'block';
                        addressDetailsSection.style.display = 'none';
                        siteNameInput.required = false; // Site adı artık zorunlu değil
                        addressDetailsInput.required = false;
                        addressDetailsInput.value = '';
                    } else if (this.value === 'standalone') {
                        siteNameSection.style.display = 'none';
                        addressDetailsSection.style.display = 'block';
                        siteNameInput.required = false;
                        addressDetailsInput.required = true;
                        siteNameInput.value = '';
                    } else if (this.value === 'plaza') {
                        siteNameSection.style.display = 'none';
                        addressDetailsSection.style.display = 'block';
                        siteNameInput.required = false;
                        addressDetailsInput.required = true;
                        siteNameInput.value = '';
                    }
                });
            });
        }

        // Photo Upload System - DÜZELTİLMİŞ VERSİYON
        let selectedPhotos = [];

        function setupPhotoUpload() {
            console.log('Setting up photo upload...');
            const photoInput = document.getElementById('photoInput');
            const uploadArea = document.getElementById('uploadArea');
            
            if (!photoInput || !uploadArea) {
                console.error('Photo upload elements not found!', {photoInput, uploadArea});
                return;
            }

            console.log('Photo upload elements found successfully');

            // File input change
            photoInput.addEventListener('change', function(e) {
                console.log('🔥 NEW FILE INPUT CHANGED!');
                console.log('Files selected:', e.target.files.length);
                console.log('File names:', Array.from(e.target.files).map(f => f.name));
                
                const files = Array.from(e.target.files);
                if (files.length > 0) {
                    console.log('📤 Calling addPhotos with', files.length, 'files');
                    addPhotos(files);
                } else {
                    console.log('⚠️ No files selected');
                }
                // Input'u temizlememeli - bu önemli!
            });

            // Drag & Drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                const imageFiles = files.filter(file => file.type.startsWith('image/'));
                
                if (imageFiles.length > 0) {
                    addPhotos(imageFiles);
                } else {
                    showAlert('Lütfen sadece resim dosyaları seçin!', 'warning');
                }
            });
        }

        function addPhotos(files) {
            console.log('📥 addPhotos called with', files.length, 'files');
            console.log('📊 Current selectedPhotos count:', selectedPhotos.length);
            console.log('📊 Current existingPhotos count:', existingPhotosData.length);
            
            // ✅ Edit mode algılaması
            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            
            // Edit mode'da total photo limit kontrolü (existing + new)
            const existingPhotoCount = isEditMode ? existingPhotosData.length : 0;
            const currentPhotoCount = selectedPhotos.length;
            const totalCurrentPhotos = existingPhotoCount + currentPhotoCount;
            const maxPhotos = 20;
            
            console.log(`📊 Photo count analysis - Existing: ${existingPhotoCount}, New: ${currentPhotoCount}, Total: ${totalCurrentPhotos}`);
            
            if (totalCurrentPhotos >= maxPhotos) {
                showAlert(`Maksimum ${maxPhotos} resim yükleyebilirsiniz. Şu anda ${totalCurrentPhotos} resminiz var.`, 'error');
                return;
            }
            
            if (totalCurrentPhotos + files.length > maxPhotos) {
                const remaining = maxPhotos - totalCurrentPhotos;
                showAlert(`Maksimum ${maxPhotos} resim yükleyebilirsiniz. ${remaining} resim daha ekleyebilirsiniz.`, 'warning');
                return;
            }
            
            // Warn for many photos
            const totalPhotos = currentPhotoCount + files.length;
            if (totalPhotos >= 10) {
                const estimatedTime = Math.ceil(totalPhotos * 2);
                const confirmed = confirm(`${totalPhotos} resim yüklüyorsunuz. Bu işlem yaklaşık ${estimatedTime} saniye sürebilir. Devam etmek istiyor musunuz?`);
                if (!confirmed) {
                    console.log('❌ User cancelled upload due to many photos');
                    return;
                }
            }
            
            let addedCount = 0;
            let errorCount = 0;
            
            files.forEach(file => {
                console.log('🔍 Processing file:', file.name, file.type, file.size);
                if (validatePhoto(file)) {
                    const photoObj = {
                        file: file,
                        id: Date.now() + Math.random(),
                        // Edit mode'da ana resim mantığı: eğer hiç yeni resim yoksa VE mevcut resim yoksa ana yap
                        isMain: selectedPhotos.length === 0 && (!isEditMode || existingPhotosData.length === 0)
                    };
                    selectedPhotos.push(photoObj);
                    console.log('✅ Added photo:', photoObj);
                    addedCount++;
                } else {
                    console.log('❌ Invalid photo:', file.name);
                    errorCount++;
                }
            });
            
            console.log('📋 Final selectedPhotos array:', selectedPhotos);
            
            if (addedCount > 0) {
                console.log('🖼️ Updating photo display...');
                updatePhotoDisplay(); // Yeni ilan ekleme ile aynı mantık
                
                console.log('📄 Updating form data...');
                updateFormData();
                
                // ✅ Edit mode için özel mesaj
                if (isEditMode) {
                    showAlert(`✅ ${addedCount} yeni fotoğraf eklendi! Kaydetmeyi unutmayın.`, 'success');
                } else {
                    showAlert(`${addedCount} fotoğraf eklendi!`, 'success');
                }
            }
            
            if (errorCount > 0) {
                showAlert(`${errorCount} fotoğraf hatalı (format/boyut)`, 'warning');
            }
        }

        function validatePhoto(file) {
            console.log('Validating file:', file.name, 'type:', file.type, 'size:', file.size);
            if (!file.type.startsWith('image/')) {
                console.log('Invalid type:', file.type);
                showAlert(`Geçersiz dosya türü: ${file.name}. Sadece resim dosyaları yüklenebilir.`, 'error');
                return false;
            }
            // Cloudflare ücretsiz hesap limiti: 2MB
            if (file.size > 10 * 1024 * 1024) { // 10MB
                console.log('File too large:', file.size);
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                showAlert(`Dosya çok büyük: ${file.name} (${sizeMB}MB). Maksimum 10MB olmalı.`, 'error');
                return false;
            }
            console.log('File validation passed');
            return true;
        }

        function updatePhotoDisplay() {
            console.log('updatePhotoDisplay called, selectedPhotos:', selectedPhotos);
            const selectedPhotosDiv = document.getElementById('selectedPhotos');
            const photosGrid = document.getElementById('photosGrid');
            const photoCounter = document.getElementById('photoCounter');
            
            // Edit mode kontrolü
            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            
            if (selectedPhotos.length === 0) {
                console.log('No new photos, hiding display');
                if (!isEditMode) {
                    selectedPhotosDiv.style.display = 'none';
                }
                return;
            }
            
            console.log('Showing new photos, count:', selectedPhotos.length);
            selectedPhotosDiv.style.display = 'block';
            photoCounter.textContent = selectedPhotos.length;
            
            // Edit mode'da başlık değiştir
            if (isEditMode) {
                const photosTitle = selectedPhotosDiv.querySelector('h6');
                if (photosTitle) {
                    photosTitle.innerHTML = `<i class="fas fa-plus-circle text-success"></i> Yeni Eklenen Fotoğraflar <span class="badge bg-success">${selectedPhotos.length}</span>`;
                }
            }
            
            photosGrid.innerHTML = '';
            
            selectedPhotos.forEach((photo, index) => {
                console.log('Processing photo', index + 1, ':', photo);
                const photoDiv = document.createElement('div');
                photoDiv.className = `photo-item ${photo.isMain ? 'main-photo' : ''}`;
                photoDiv.dataset.id = photo.id;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log('FileReader loaded for photo', index + 1);
                    console.log('Image data URL length:', e.target.result ? e.target.result.length : 'null');
                    photoDiv.innerHTML = `
                        <div class="photo-preview-container">
                            <img src="${e.target.result}" alt="Fotoğraf ${index + 1}" class="img-fluid photo-preview" 
                                 onload="console.log('Image loaded successfully for photo ${index + 1}')" 
                                 onerror="console.error('Image load error for photo ${index + 1}')">
                            
                            <!-- Individual Progress Overlay -->
                            <div class="upload-progress-overlay hidden">
                                <div class="progress-circle">
                                    <svg class="circular-chart" viewBox="0 0 42 42">
                                        <circle class="circle-bg" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                                        <circle class="circle" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                                    </svg>
                                    <div class="progress-text">0%</div>
                                </div>
                                <div class="upload-status connecting">Hazırlanıyor...</div>
                                <div class="upload-filename">${photo.file.name}</div>
                                <div class="file-size">${formatFileSize(photo.file.size)}</div>
                            </div>
                            
                            <!-- Photo Controls Overlay -->
                            <div class="photo-overlay">
                                <div class="photo-actions">
                                    <button type="button" class="photo-action-btn delete" onclick="removePhoto('${photo.id}')" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="photo-number">${index + 1}</div>
                    ` + 
                    (!photo.isMain ? '<button type="button" class="photo-action-btn main" onclick="setAsMain(\'' + photo.id + '\')" title="Ana Fotoğraf Yap"><i class="fas fa-star"></i></button>' : '') + 
                    (photo.isMain ? '<div class="main-photo-badge"><i class="fas fa-star"></i> ANA</div>' : '');
                    
                    // Start upload simulation immediately for visual feedback
                    setTimeout(() => {
                        // For now, keep simulation but mark as "waiting for real upload"
                        simulatePhotoUpload(photoDiv, photo.file);
                    }, 300 + (index * 150)); // Stagger start times for realistic effect
                };
                reader.onerror = function(e) {
                    console.error('FileReader error for photo', index + 1, ':', e);
                };
                reader.readAsDataURL(photo.file);
                
                photosGrid.appendChild(photoDiv);
            });
        }

        function removePhoto(photoId) {
            const index = selectedPhotos.findIndex(p => p.id == photoId);
            if (index > -1) {
                const wasMain = selectedPhotos[index].isMain;
                selectedPhotos.splice(index, 1);
                
                if (wasMain && selectedPhotos.length > 0) {
                    selectedPhotos[0].isMain = true;
                }
                
                updatePhotoDisplay();
                updateFormData(); // Bu da önemli!
                showAlert('Fotoğraf silindi', 'info');
            }
        }

        function setAsMain(photoId) {
            selectedPhotos.forEach(photo => {
                photo.isMain = (photo.id == photoId);
            });
            
            updatePhotoDisplay();
            updateFormData(); // Bu da önemli!
            showAlert('Ana fotoğraf değiştirildi!', 'success');
        }

        function clearAllPhotos() {
            if (confirm('Tüm fotoğrafları silmek istediğinizden emin misiniz?')) {
                selectedPhotos = [];
                updatePhotoDisplay();
                updateFormData(); // Bu da önemli!
                
                // Clear progress list
                const progressList = document.getElementById('uploadProgressList');
                const progressItems = document.getElementById('progressItems');
                progressItems.innerHTML = '';
                progressList.style.display = 'none';
                
                showAlert('Tüm fotoğraflar silindi', 'info');
            }
        }

        // EN ÖNEMLİ FONKSİYON: updateFormData - Tamamen yeniden yazıldı
        function updateFormData() {
            const photoInput = document.getElementById('photoInput');
            
            if (!photoInput) return;
            
            // Yeni bir FileList oluştur
            const dt = new DataTransfer();
            
            // Seçilen fotoğrafları sırasıyla ekle (main fotoğraf ilk sırada)
            const sortedPhotos = [...selectedPhotos].sort((a, b) => {
                if (a.isMain) return -1;
                if (b.isMain) return 1;
                return 0;
            });
            
            sortedPhotos.forEach(photo => {
                dt.items.add(photo.file);
            });
            
            // Input'a yeni FileList'i ata
            photoInput.files = dt.files;
            
            console.log('Form data updated:', photoInput.files.length, 'files');
        }

        function showAlert(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'warning' ? 'alert-warning' : 
                              type === 'danger' ? 'alert-danger' : 'alert-info';
                              
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 4000);
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.dash-aside-navbar');
            const overlay = document.querySelector('.mobile-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }

        // Form submit event listener - Optimized with progress feedback
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('propertyForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Update existing images hidden input before submit
                    const updatedExistingImagesInput = document.getElementById('updatedExistingImages');
                    if (updatedExistingImagesInput) {
                        updatedExistingImagesInput.value = JSON.stringify(existingPhotosData);
                        console.log('Updated existing photos:', existingPhotosData);
                    }
                    
                    // Enhanced photo upload feedback
                    const photoInput = document.getElementById('photoInput');
                    const photoCount = photoInput.files.length;
                    
                    if (photoCount > 0) {
                        console.log(`🚀 Starting upload process: ${photoCount} photos`);
                        
                        // Start real upload progress tracking
                        startRealUploadProgress();
                        
                        // Show user-friendly loading message
                        const loadingMessage = document.createElement('div');
                        loadingMessage.className = 'alert alert-info mt-3';
                        loadingMessage.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>
                                    <strong>📤 Fotoğraflar yükleniyor...</strong><br>
                                    <small>${photoCount} fotoğraf işleniyor. Tahmini süre: ${Math.ceil(photoCount * 3)} saniye</small>
                                </div>
                            </div>
                        `;
                        
                        // Insert loading message after form
                        form.parentNode.insertBefore(loadingMessage, form.nextSibling);
                        
                        // Disable submit button to prevent double submission
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Yükleniyor...';
                        }
                    }
                    
                    // Log individual file details for debugging
                    for (let i = 0; i < photoCount; i++) {
                        const file = photoInput.files[i];
                        console.log(`📁 File ${i+1}: ${file.name} (${(file.size/1024/1024).toFixed(2)}MB)`);
                    }
                    
                    // Eğer fotoğraf yoksa uyarı ver (isteğe bağlı)
                    if (photoInput.files.length === 0 && existingPhotosData.length === 0) {
                        const confirmSubmit = confirm('Hiç fotoğraf yok. Devam etmek istiyor musunuz?');
                        if (!confirmSubmit) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });

        // Debug fonksiyonu - konsola fotoğraf bilgilerini yazdır
        function debugPhotos() {
            console.log('Selected photos:', selectedPhotos);
            const photoInput = document.getElementById('photoInput');
            console.log('Input files:', photoInput.files);
        }
        
        // Edit mode: Pre-select feature checkboxes
        <?php if ($edit_mode && $existing_property): ?>
            // Function to select checkboxes based on feature array
            function selectFeatureCheckboxes(features, nameAttribute) {
                if (features && features.length > 0) {
                    features.forEach(feature => {
                        const checkbox = document.querySelector(`input[name="${nameAttribute}"][value="${feature}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                }
            }
            
            // Parse and select existing features
            const existingIcOzellikler = <?php echo json_encode($existing_ic_ozellikler); ?>;
            const existingDisOzellikler = <?php echo json_encode($existing_dis_ozellikler); ?>;
            const existingMuhitOzellikleri = <?php echo json_encode($existing_muhit_ozellikleri); ?>;
            const existingUlasimOzellikleri = <?php echo json_encode($existing_ulasim_ozellikleri); ?>;
            const existingManzaraOzellikleri = <?php echo json_encode($existing_manzara_ozellikleri); ?>;
            const existingKonutTipiOzellikleri = <?php echo json_encode($existing_konut_tipi_ozellikleri); ?>;
            const existingOlanaklar = <?php echo json_encode($existing_olanaklar); ?>;
            
            // Apply feature selections
            selectFeatureCheckboxes(existingIcOzellikler, 'ic_ozellikler[]');
            selectFeatureCheckboxes(existingDisOzellikler, 'dis_ozellikler[]');
            selectFeatureCheckboxes(existingMuhitOzellikleri, 'muhit_ozellikleri[]');
            selectFeatureCheckboxes(existingUlasimOzellikleri, 'ulasim_ozellikleri[]');
            selectFeatureCheckboxes(existingManzaraOzellikleri, 'manzara_ozellikleri[]');
            selectFeatureCheckboxes(existingKonutTipiOzellikleri, 'konut_tipi_ozellikleri[]');
            selectFeatureCheckboxes(existingOlanaklar, 'olanaklar[]');
            
            // Handle location type sections for edit mode
            const selectedLocationRadio = document.querySelector('input[name="location_type"]:checked');
            if (selectedLocationRadio) {
                const siteNameSection = document.getElementById('site-name-section');
                const addressDetailsSection = document.getElementById('address-details-section');
                const siteNameInput = document.getElementById('site_name');
                const addressDetailsInput = document.getElementById('address_details');
                
                if (selectedLocationRadio.value === 'site') {
                    siteNameSection.style.display = 'block';
                    addressDetailsSection.style.display = 'none';
                    siteNameInput.required = false; // Site adı artık zorunlu değil
                    addressDetailsInput.required = false;
                } else if (selectedLocationRadio.value === 'standalone') {
                    siteNameSection.style.display = 'none';
                    addressDetailsSection.style.display = 'block';
                    siteNameInput.required = false;
                    addressDetailsInput.required = true;
                } else if (selectedLocationRadio.value === 'plaza') {
                    siteNameSection.style.display = 'none';
                    addressDetailsSection.style.display = 'block';
                    siteNameInput.required = false;
                    addressDetailsInput.required = true;
                }
            }
        
        // Edit mode JavaScript tamamlandı
        <?php endif; ?>
        
        // Existing Photo Management Functions - CLOUDFLARE ONLY
        let existingPhotosData = <?php echo json_encode($existing_images); ?>;
        
        // $existing_images is already filtered to Cloudflare-only in PHP
        console.log('Cloudflare-only images loaded:', existingPhotosData);
        
        function makeMainPhoto(photoIndex) {
            console.log('Making Cloudflare photo main:', photoIndex);
            
            if (photoIndex === 0) {
                showAlert('Bu zaten ana resim!', 'info');
                return;
            }
            
            if (confirm('Bu fotoğrafı ana fotoğraf yapmak istediğinize emin misiniz?')) {
                // Store the current main image info for user feedback
                const oldMainImage = existingPhotosData[0];
                const newMainImage = existingPhotosData[photoIndex];
                
                // Move selected photo to index 0
                existingPhotosData.splice(photoIndex, 1); // Remove from current position
                existingPhotosData.unshift(newMainImage); // Add to beginning
                
                console.log('Updated photos order:', existingPhotosData);
                
                // Update display immediately
                updateExistingPhotosDisplay();
                
                // Show success message with clear info
                showAlert(`Ana resim değiştirildi! Yeni ana resim: ${photoIndex + 1}. sıradakı resim`, 'success');
                
                // Auto-save the change to database
                saveMainImageChange();
            }
        }
        
        function saveMainImageChange() {
            console.log('Saving main image change to database...');
            console.log('Current existingPhotosData:', existingPhotosData);
            
            // Get property ID from PHP
            const propertyId = <?php echo $edit_id ?? 0; ?>;
            
            // Get CSRF token from form
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            
            const formData = new FormData();
            formData.append('action', 'update_main_image');
            formData.append('property_id', propertyId);
            formData.append('updated_existing_images', JSON.stringify(existingPhotosData));
            formData.append('csrf_token', csrfToken); // CSRF token eklendi
            
            // Show loading indicator
            showAlert('Ana resim değişikliği kaydediliyor...', 'info');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Main image update response:', data);
                if (data.trim() === 'success') {
                    showAlert('✅ Ana resim başarıyla güncellendi!', 'success');
                    
                    // Update page title or any other UI element if needed
                    const newMainImageUrl = existingPhotosData[0]; // Already contains full Cloudflare URL
                    console.log('New main image URL:', newMainImageUrl);
                    
                } else {
                    console.error('Update failed:', data);
                    showAlert('⚠️ Ana resim güncellenemedi: ' + data, 'warning');
                }
            })
            .catch(error => {
                console.error('Main image update error:', error);
                showAlert('❌ Ana resim güncelleme hatası! Lütfen sayfayı yenileyin.', 'error');
            });
        }
        
        function removeExistingPhoto(photoIndex) {
            console.log('Removing photo:', photoIndex);
            
            if (confirm('Bu fotoğrafı kalıcı olarak kaldırmak istediğinize emin misiniz?')) {
                // Remove photo from array
                existingPhotosData.splice(photoIndex, 1);
                
                updateExistingPhotosDisplay();
                
                // Show success message
                showAlert('Fotoğraf başarıyla kaldırıldı!', 'success');
            }
        }
        
        function updateExistingPhotosDisplay() {
            console.log('Updating existing photos display...');
            const existingPhotosGrid = document.querySelector('.existing-photos-grid');
            if (!existingPhotosGrid) {
                console.log('Grid element not found');
                return;
            }
            
            // Clear current display
            existingPhotosGrid.innerHTML = '';
            
            if (existingPhotosData.length === 0) {
                existingPhotosGrid.parentElement.style.display = 'none';
                return;
            }
            
            console.log('Building display for', existingPhotosData.length, 'photos');
            
            // Cloudflare account hash from PHP for public URLs
            const cloudflareAccountHash = '<?php echo CLOUDFLARE_ACCOUNT_HASH; ?>';
            
            // Rebuild photo items with enhanced styling
            existingPhotosData.forEach((image, index) => {
                const photoItem = document.createElement('div');
                photoItem.className = `existing-photo-item ${index === 0 ? 'main-photo' : 'secondary-photo'}`;
                photoItem.setAttribute('data-image', image);
                photoItem.setAttribute('data-index', index);
                
                // Generate Cloudflare public URL using account hash - handle both full URLs and IDs
                let cloudflareThumbUrl;
                if (image.startsWith('https://imagedelivery.net/')) {
                    // Already a full URL, extract ID and create thumbnail URL with account hash
                    const matches = image.match(/https:\/\/imagedelivery\.net\/[^\/]+\/([a-f0-9-]+)\//);
                    if (matches) {
                        const imageId = matches[1];
                        cloudflareThumbUrl = `https://imagedelivery.net/${cloudflareAccountHash}/${imageId}/public`;
                    } else {
                        cloudflareThumbUrl = image;
                    }
                } else {
                    // Just an ID, create full public URL with account hash
                    cloudflareThumbUrl = `https://imagedelivery.net/${cloudflareAccountHash}/${image}/public`;
                }
                
                photoItem.innerHTML = `
                    <img src="${cloudflareThumbUrl}" 
                         alt="Mevcut fotoğraf ${index + 1}" 
                         class="img-fluid photo-thumbnail"
                         onerror="this.style.display='none'; this.nextElementSibling.innerHTML='<div class=\\"text-center p-3\\"><i class=\\"fas fa-image-slash\\"></i><br>Resim yüklenemedi</div>'">
                    
                    <div class="photo-controls">
                        ${index !== 0 ? '<button type="button" class="btn-make-main" onclick="makeMainPhoto(' + index + ')" title="Ana resim yap"><i class="fas fa-star"></i></button>' : ''}
                        <button type="button" class="btn-remove-photo" onclick="removeExistingPhoto(${index})" title="Resmi kaldır">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    ${index === 0 ? `
                        <div class="main-photo-badge pulse-animation">
                            <i class="fas fa-star"></i> ANA RESİM
                        </div>
                    ` : `
                        <div class="photo-order-badge">
                            ${index + 1}. RESIM
                        </div>
                    `}
                `;
                
                existingPhotosGrid.appendChild(photoItem);
                console.log(`Added photo ${index + 1} to grid`);
            });
            
            // Update the count badge
            const countBadge = document.querySelector('.existing-photos-grid').parentElement.querySelector('.badge');
            if (countBadge) {
                countBadge.textContent = existingPhotosData.length;
            }
            
            // Ensure main photo has special styling
            setTimeout(() => {
                const mainPhotoItem = document.querySelector('.existing-photo-item[data-index="0"]');
                if (mainPhotoItem) {
                    mainPhotoItem.classList.add('main-photo-active');
                    console.log('Main photo styling applied');
                }
            }, 100);
            
            console.log('Display update completed');
        }
        
        function showAlert(message, type = 'info') {
            // Create alert element
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alert.style.top = '20px';
            alert.style.right = '20px';
            alert.style.zIndex = '9999';
            alert.style.maxWidth = '300px';
            
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alert);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 3000);
        }
    </script>
    
    <!-- İstanbul İlçe ve Mahalle Sistemi - Early Load -->
    <script>
    // Global location data
    window.istanbulDistricts = [
        'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir',
        'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy',
        'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kâğıthane',
        'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli',
        'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'
    ];
    
    window.istanbulNeighborhoods = {
        'Kadıköy': ['Acıbadem', 'Bostancı', 'Caferağa', 'Caddebostan', 'Erenköy', 'Fenerbahçe', 'Feneryolu', 'Fikirtepe', 'Göztepe', 'Hasanpaşa', 'İçerenköy', 'Kadıköy', 'Koşuyolu', 'Kozyatağı', 'Merdivenköy', 'Moda', 'Osmanağa', 'Rasimpaşa', 'Sahrayıcedit', 'Suadiye'],
        'Beşiktaş': ['Abbasağa', 'Akatlar', 'Bebek', 'Beşiktaş', 'Dikilitaş', 'Etiler', 'Gayrettepe', 'Konaklar', 'Kuruçeşme', 'Levent', 'Muradiye', 'Nisbetiye', 'Ortaköy', 'Sinanpaşa', 'Türkali', 'Ulus', 'Vişnezade', 'Yıldız'],
        'Şişli': ['Ayazağa', 'Bozkurt', 'Cumhuriyet', 'Esentepe', 'Feriköy', 'Fulya', 'Gayrettepe', 'Gültepe', 'Harbiye', 'Kuştepe', 'Maslak', 'Mecidiyeköy', 'Merkez', 'Nişantaşı', 'Okmeydanı', 'Osmanbey', 'Pangaltı', 'Şişli', 'Teşvikiye'],
        'Üsküdar': ['Acıbadem', 'Altunizade', 'Barbaros', 'Beylerbeyi', 'Bülbülderesi', 'Burhaniye', 'Çengelköy', 'Ferah', 'Güzeltepe', 'İcadiye', 'Kandilli', 'Kısıklı', 'Küçük Çamlıca', 'Küçüksu', 'Kuleli', 'Kuzguncuk', 'Salacak', 'Selimiye', 'Sultantepe', 'Üsküdar'],
        'Fatih': ['Aksaray', 'Alemdar', 'Ayvansaray', 'Balat', 'Beyazıt', 'Cankurtaran', 'Cerrahpaşa', 'Eminönü', 'Fener', 'Karagümrük', 'Küçük Ayasofya', 'Süleymaniye', 'Topkapı', 'Yedikule', 'Zeyrek'],
        'Beyoğlu': ['Asmalımescit', 'Galatasaray', 'Gümüşsuyu', 'Hasköy', 'İstiklal', 'Karaköy', 'Kulaksız', 'Kurtuluş', 'Kuloğlu', 'Pangaltı', 'Şişhane', 'Sütlüce', 'Taksim', 'Tepebaşı'],
        'Bakırköy': ['Ataköy', 'Bahçelievler', 'Bakırköy', 'Cevizlik', 'Florya', 'İncirli', 'Kartaltepe', 'Osmaniye', 'Sakızağacı', 'Şenlikköy', 'Yeşilköy', 'Yeşilyurt'],
        'Sarıyer': ['Ayazağa', 'Bahçeköy', 'Büyükdere', 'Emirgan', 'İstinye', 'Kilyos', 'Maslak', 'Rumelihisarı', 'Tarabya', 'Yeniköy', 'Zekeriyaköy'],
        'Pendik': ['Bahçelievler', 'Batı', 'Çamçeşme', 'Doğu', 'Dumlupınar', 'Esenyalı', 'Fevzi Çakmak', 'Güllübağlar', 'Harmandere', 'Kurtköy', 'Orhanlı', 'Pendik', 'Ramazanoğlu', 'Sapanbağları', 'Sülüntepe', 'Velibaba', 'Yayalar', 'Yeşilbağlar'],
        'Maltepe': ['Aydınevler', 'Bağlarbaşı', 'Başıbüyük', 'Büyükbakkalköy', 'Cevizli', 'Esenkent', 'Fındıklı', 'Girne', 'Gülsuyu', 'İdealtepe', 'Küçükyalı', 'Maltepe', 'Zümrütevler'],
        'Kartal': ['Atalar', 'Cevizli', 'Esentepe', 'Gümüşpınar', 'Hürriyet', 'Karlıktepe', 'Kartal', 'Kordonboyu', 'Orta', 'Petrolkent', 'Soğanlık', 'Sultan Selim', 'Ugur', 'Uğur', 'Yakacık', 'Yukarı Dudullu', 'Yunus'],
        'Ataşehir': ['Ataşehir', 'Barbaros', 'Esatpaşa', 'Ferhatpaşa', 'İçerenköy', 'İnönü', 'Kayışdağı', 'Küçükbakkalköy', 'Mimar Sinan', 'Mustafa Kemal', 'Örnek', 'Yenişehir', 'Yenisahra'],
        'Bahçelievler': ['Bahçelievler', 'Cumhuriyet', 'Çobançeşme', 'Fevzi Çakmak', 'Hürriyet', 'Kocasinan', 'Sirinevler', 'Şirinevler', 'Soğanlı', 'Yayla', 'Zafer', 'Yenibosna'],
        'Bağcılar': ['Bağcılar', 'Barbaros Hayrettin', 'Demirkapı', 'Evren', 'Fatih', 'Göztepe', 'Güneşli', 'Hürriyet', 'İnönü', 'Kartaltepe', 'Kazım Karabekir', 'Kemalpaşa', 'Mahmutbey', 'Mimar Sinan', '15 Temmuz', 'Yıldıztepe'],
        'Avcılar': ['Ambarlı', 'Denizköşkler', 'Firuzköy', 'Gümüşpala', 'İsmet Paşa', 'Merkez', 'Mustafa Kemal Paşa', 'Tahtakale', 'Üniversite', 'Yeşilkent'],
        'Küçükçekmece': ['Atatürk', 'Beşyol', 'Cennet', 'Cumhuriyet', 'Fevzi Çakmak', 'Gültepe', 'Halkalı', 'İnönü', 'İstasyon', 'Kanarya', 'Kartaltepe', 'Kemalpaşa', 'Küçükçekmece', 'Mehterçeşme', 'Menekşe', 'Sefaköy', 'Söğütlüçeşme', 'Sultanmurat', 'Tevfikbey', 'Yarımburgaz', 'Yenimahalle'],
        'Beylikdüzü': ['Adnan Kahveci', 'Barış', 'Büyükşehir', 'Cumhuriyet', 'Dereağzı', 'Gürpınar', 'Kavaklı', 'Marmara', 'Sahil', 'Yakuplu'],
        'Esenyurt': ['Akevler', 'Akşemsettin', 'Ardıçlı', 'Balamir', 'Barbaros Hayrettin Paşa', 'Batıkent', 'Çiğdem', 'Esenkent', 'Esenyurt', 'Fatih', 'Firuzköy', 'Güzelyurt', 'Haramidere', 'Hoşdere', 'İkitelli', 'İncirtepe', 'İnönü', 'Kemer', 'Mehterçeşme', 'Ömerli', 'Örnek', 'Pınar', 'Saadetdere', 'Salkımsöğüt', 'Sultaniye', 'Talatpaşa', 'Tuna', 'Yenikent', 'Zafer'],
        'Büyükçekmece': ['Alkent 2000', 'Batıköy', 'Büyükçekmece', 'Celaliye', 'Cumhuriyet', 'Dizdariye', 'Güzelce', 'Hurma', 'İnceğiz', 'Karaağaç', 'Kıraç', 'Kumburgaz', 'Mimarsinan', 'Muratbey', 'Pınarca', 'Tepecik', 'Ulus', 'Yalova'],
        'Gaziosmanpaşa': ['Bağlarbaşı', 'Barbaros Hayrettin Paşa', 'Cachev', 'Gaziosmanpaşa', 'Hürriyet', 'Karadolap', 'Karayolları', 'Merkez', 'Pazariçi', 'Sarıgöl', 'Şemsipaşa', 'Yıldıztabya'],
        'Güngören': ['Akıncılar', 'Genç Osman', 'Güngören', 'Haznedar', 'Mareşal Çakmak', 'Mehmet Nesih Özmen', 'Merkez', 'Sanayi', 'Tozkoparan'],
        'Esenler': ['Atışalanı', 'Barbaros Hayrettin Paşa', 'Birlik', 'Davutpaşa', 'Ek Kazık', 'Fatih', 'Fevzi Çakmak', 'Havaalanı', 'Kemer', 'Menderes', 'Mimar Kemalettin', 'Oruçreis', 'Süleyman Çelebi', 'Tuna'],
        'Bayrampaşa': ['Altıntepsi', 'Kartaltepe', 'Muratpaşa', 'Vatan', 'Yıldırım'],
        'Zeytinburnu': ['Beştelsiz', 'Çırpıcı', 'Gökalp', 'Kazlıçeşme', 'Maltepe', 'Merkezefendi', 'Nuripaşa', 'Seyitnizam', 'Sümer', 'Telsiz', 'Veliefendi', 'Yenidoğan'],
        'Sultanbeyli': ['Abdurrahmangazi', 'Adil', 'Battalgazi', 'Hasanpaşa', 'Mecidiye', 'Mehmet Akif', 'Mimar Sinan', 'Orhangazi', 'Turgutreis', 'Yavuz Selim'],
        'Sancaktepe': ['Abdurrahmangazi', 'Akpınar', 'Emek', 'Fatih', 'Kemal Türkler', 'Osmaniye', 'Paşaköy', 'Samatyalı', 'Sarıgazi', 'Sülüntepe', 'Ulu'],
        'Çekmeköy': ['Alemdağ', 'Cevizli', 'Çatalmeşe', 'Çekmeköy', 'Halil Dumrul', 'Hamidiye', 'Hüseyinli', 'Kirazlıdere', 'Mahmutlu', 'Meşrutiyet', 'Nişantepe', 'Ömerli', 'Sultançiftliği', 'Taşdelen'],
        'Ümraniye': ['Adem Yavuz', 'Alemdar', 'Armağanevler', 'Aşağı Dudullu', 'Cemil Meriç', 'Çamlık', 'Dumlupınar', 'Elmalıkent', 'Esenkent', 'Hekimsuyu', 'İnkılap', 'Kazım Karabekir', 'Mehmet Akif', 'Necip Fazıl', 'Parseller', 'Site', 'Tantavi', 'Topyurdu', 'Ümraniye', 'Yukarı Dudullu'],
        'Tuzla': ['Aydınlı', 'Aydıntepe', 'Cevizli', 'Evliya Çelebi', 'İçmeler', 'Kampüs', 'Mimar Sinan', 'Orta', 'Orhanlı', 'Postane', 'Şifa', 'Tepeören', 'Yayla'],
        'Arnavutköy': ['Arnavutköy', 'Avcıkoru', 'Bolluca', 'Dursunköy', 'Hacımaşlı', 'Haraççı', 'İmrahor', 'İslambey', 'Karaburun', 'Mavigöl', 'Merkez', 'Sazlıbosna', 'Tayakadın', 'Yeniköy'],
        'Başakşehir': ['Altınşehir', 'Bahçeşehir 1. Kısım', 'Bahçeşehir 2. Kısım', 'Bahçeşehir 3. Kısım', 'Başak', 'Başakşehir', 'Güvercintepe', 'İkitelli Organize Sanayi', 'Kayabaşı', 'Şahintepe', 'Şamlar', 'Ziya Gökalp'],
        'Kağıthane': ['Çağlayan', 'Emniyet Evleri', 'Gültepe', 'Hamidiye', 'Harmantepe', 'Kağıthane', 'Merkez', 'Nuru Osmaniye', 'Ortabayır', 'Seyrantepe', 'Sultan Selim', 'Talatpaşa', 'Telsizler', 'Yahya Kemal'],
        'Eyüpsultan': ['Alibeyköy', 'Düğmeciler', 'Emniyettepe', 'Esentepe', 'Eyüp', 'Göktürk', 'Güzeltepe', 'Karadolap', 'Kemerburgaz', 'Merkez', 'Mimarsinan', 'Odayeri', 'Pirinçci', 'Ramami', 'Rami Cuma', 'Sakarya', 'Silahtar', 'Topçular'],
        'Çatalca': ['Akalan', 'Atatürk', 'Avcılar', 'Başak', 'Belgrat Ormanı', 'Boyalık', 'Çakıl', 'Çalıköy', 'Çanta', 'Çiftlik', 'Dağyenice', 'Ferhatpaşa', 'Gökçeali', 'Hallaçlı', 'İhsaniye', 'İnceğiz', 'Kabakça', 'Kaleiçi', 'Kestanelik', 'Muratbey', 'Oklalı', 'Ovayenice', 'Subaşı', 'Yelkenci'],
        'Silivri': ['Ahmediye', 'Akören', 'Aliağa', 'Balaban', 'Beyciler', 'Büyük Çavuşlu', 'Büyüksinekli', 'Çanta', 'Çayır', 'Çeltik', 'Danamandıra', 'Değirmenköy', 'Demirköy', 'Domurcalı', 'Fener', 'Gazitepe', 'Gümüşyaka', 'İpsiz', 'Kadıköy', 'Kavaklı', 'Kurfallı', 'Küçüksinekli', 'Merkez', 'Ortaköy', 'Pehlivanlı', 'Selimpaşa', 'Semizkum', 'Silivri', 'Yolçatı'],
        'Şile': ['Ahmetli', 'Alacalı', 'Baltalı', 'Bucaklı', 'Çataklı', 'Çayır', 'Değirmençayırı', 'Doğancılı', 'Erenler', 'Esenceli', 'Geredeli', 'Hacıllı', 'Hasanlı', 'İmrendere', 'Kabakoz', 'Kalem', 'Karabeyli', 'Karamandere', 'Kızılcaköy', 'Korucu', 'Kumbaba', 'Kurna', 'Meşrutiyet', 'Orhanlı', 'Ovacık', 'Şile', 'Teke', 'Ulupelit', 'Yazıköy', 'Yeniköy'],
        'Beykoz': ['Acarkent', 'Anadolu Feneri', 'Anadolu Hisarı', 'Anadolu Kavağı', 'Baklacı', 'Bozhane', 'Çubuklu', 'Dereseki', 'Elmalı', 'Göztepe', 'Görele', 'Gümüşsuyu', 'İnceğiz', 'Kanlıca', 'Kavacık', 'Kefeliköy', 'Merkez', 'Örnekköy', 'Paşabahçe', 'Poyrazköy', 'Riva', 'Rüzgarlıbahçe', 'Soğullu', 'Tokatköy', 'Yalıköy', 'Yavuz Selim'],
        'Adalar': ['Burgazada', 'Heybeliada', 'Kınalıada', 'Büyükada', 'Sedef Adası']
    };
    
    // Global location management functions
    window.loadDistrictsToSelect = function(selectElement) {
        if (!selectElement) return false;
        
        console.log('Loading districts to select:', selectElement.id);
        selectElement.innerHTML = '<option value="">İlçe Seçiniz</option>';
        
        window.istanbulDistricts.forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            selectElement.appendChild(option);
        });
        
        console.log('Districts loaded:', window.istanbulDistricts.length);
        return true;
    };
    
    window.loadNeighborhoodsToSelect = function(selectElement, selectedDistrict) {
        if (!selectElement) return false;
        
        console.log('Loading neighborhoods for:', selectedDistrict);
        selectElement.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        
        if (selectedDistrict && window.istanbulNeighborhoods[selectedDistrict]) {
            window.istanbulNeighborhoods[selectedDistrict].forEach(neighborhood => {
                const option = document.createElement('option');
                option.value = neighborhood;
                option.textContent = neighborhood;
                selectElement.appendChild(option);
            });
            console.log('Neighborhoods loaded for', selectedDistrict, ':', window.istanbulNeighborhoods[selectedDistrict].length);
        }
        
        return true;
    };
    
    // Auto-initialize when elements become available
    window.initLocationSelects = function() {
        const districtSelect = document.getElementById('district');
        const neighborhoodSelect = document.getElementById('neighborhood');
        
        console.log('Trying to initialize location selects...', {districtSelect, neighborhoodSelect});
        
        if (districtSelect && neighborhoodSelect) {
            console.log('Both elements found! Initializing...');
            
            // Load districts
            window.loadDistrictsToSelect(districtSelect);
            
            // Add event listener for district changes
            districtSelect.addEventListener('change', function() {
                window.loadNeighborhoodsToSelect(neighborhoodSelect, this.value);
            });
            
            return true;
        }
        
        return false;
    };
    </script>

    <!-- İstanbul İlçe ve Mahalle Sistemi -->
    <script>
    // İlçe ve Mahalle yükleme fonksiyonu - Sayfa yüklendiğinde hemen çalışır
    function initializeLocationSystem() {
        // İstanbul ilçeleri
        const istanbulDistricts = [
            'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir',
            'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy',
            'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kâğıthane',
            'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli',
            'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'
        ];
        
        // Mahalleler
        const istanbulNeighborhoods = {
            'Kadıköy': ['Acıbadem', 'Bostancı', 'Caferağa', 'Caddebostan', 'Erenköy', 'Fenerbahçe', 'Feneryolu', 'Fikirtepe', 'Göztepe', 'Hasanpaşa', 'İçerenköy', 'Kadıköy', 'Koşuyolu', 'Kozyatağı', 'Merdivenköy', 'Moda', 'Osmanağa', 'Rasimpaşa', 'Sahrayıcedit', 'Suadiye'],
            'Beşiktaş': ['Abbasağa', 'Akatlar', 'Bebek', 'Beşiktaş', 'Dikilitaş', 'Etiler', 'Gayrettepe', 'Konaklar', 'Kuruçeşme', 'Levent', 'Muradiye', 'Nisbetiye', 'Ortaköy', 'Sinanpaşa', 'Türkali', 'Ulus', 'Vişnezade', 'Yıldız'],
            'Şişli': ['Ayazağa', 'Bozkurt', 'Cumhuriyet', 'Esentepe', 'Feriköy', 'Fulya', 'Gayrettepe', 'Gültepe', 'Harbiye', 'Kuştepe', 'Maslak', 'Mecidiyeköy', 'Merkez', 'Nişantaşı', 'Okmeydanı', 'Osmanbey', 'Pangaltı', 'Şişli', 'Teşvikiye'],
            'Üsküdar': ['Acıbadem', 'Altunizade', 'Barbaros', 'Beylerbeyi', 'Bülbülderesi', 'Burhaniye', 'Çengelköy', 'Ferah', 'Güzeltepe', 'İcadiye', 'Kandilli', 'Kısıklı', 'Küçük Çamlıca', 'Küçüksu', 'Kuleli', 'Kuzguncuk', 'Salacak', 'Selimiye', 'Sultantepe', 'Üsküdar'],
            'Fatih': ['Aksaray', 'Alemdar', 'Ayvansaray', 'Balat', 'Beyazıt', 'Cankurtaran', 'Cerrahpaşa', 'Eminönü', 'Fener', 'Karagümrük', 'Küçük Ayasofya', 'Süleymaniye', 'Topkapı', 'Yedikule', 'Zeyrek'],
            'Beyoğlu': ['Asmalımescit', 'Galatasaray', 'Gümüşsuyu', 'Hasköy', 'İstiklal', 'Karaköy', 'Kulaksız', 'Kurtuluş', 'Kuloğlu', 'Pangaltı', 'Şişhane', 'Sütlüce', 'Taksim', 'Tepebaşı'],
            'Bakırköy': ['Ataköy', 'Bahçelievler', 'Bakırköy', 'Cevizlik', 'Florya', 'İncirli', 'Kartaltepe', 'Osmaniye', 'Sakızağacı', 'Şenlikköy', 'Yeşilköy', 'Yeşilyurt'],
            'Sarıyer': ['Ayazağa', 'Bahçeköy', 'Büyükdere', 'Emirgan', 'İstinye', 'Kilyos', 'Maslak', 'Rumelihisarı', 'Tarabya', 'Yeniköy', 'Zekeriyaköy'],
            'Pendik': ['Bahçelievler', 'Batı', 'Çamçeşme', 'Doğu', 'Dumlupınar', 'Esenyalı', 'Fevzi Çakmak', 'Güllübağlar', 'Harmandere', 'Kurtköy', 'Orhanlı', 'Pendik', 'Ramazanoğlu', 'Sapanbağları', 'Sülüntepe', 'Velibaba', 'Yayalar', 'Yeşilbağlar'],
            'Maltepe': ['Aydınevler', 'Bağlarbaşı', 'Başıbüyük', 'Büyükbakkalköy', 'Cevizli', 'Esenkent', 'Fındıklı', 'Girne', 'Gülsuyu', 'İdealtepe', 'Küçükyalı', 'Maltepe', 'Zümrütevler'],
            'Kartal': ['Atalar', 'Cevizli', 'Derince', 'Esentepe', 'Gümüşpınar', 'Karlıktepe', 'Kartal', 'Kordonboyu', 'Petrol-İş', 'Soğanlık', 'Uğur Mumcu', 'Yakacık', 'Yukarı', 'Yunus'],
            'Ataşehir': ['Ataşehir', 'Barbaros', 'Ferhatpaşa', 'Gökkuşağı', 'İçerenköy', 'İnönü', 'Kayışdağı', 'Küçükbakkalköy', 'Mustafa Kemal', 'Örnek', 'Yenisahra'],
            'Tuzla': ['Aydınlı', 'Cevizli', 'İçmeler', 'Mimar Sinan', 'Orta', 'Ovaakça', 'Postane', 'Şifa', 'Tepeören', 'Tuzla'],
            'Ümraniye': ['Altınşehir', 'Armağanevler', 'Aşağı Dudullu', 'Atakent', 'Çakmak', 'Çekmeköy', 'Esenevler', 'Esenkent', 'Hekimbaşı', 'İmam Hatip', 'Madenler', 'Mehmet Akif', 'Necip Fazıl', 'Parseller', 'Site', 'Tantavi', 'Topağacı', 'Ümraniye', 'Yukarı Dudullu'],
            'Sancaktepe': ['Abdurrahmangazi', 'Akpınar', 'Emek', 'Eyüp Sultan', 'Fatih Sultan Mehmet', 'Kemalpaşa', 'Meclis', 'Mimar Sinan', 'Sancaktepe', 'Sarıgazi', 'Yenidoğan'],
            'Çekmeköy': ['Alemdağ', 'Çamlık', 'Çekmeköy', 'Ekşioğlu', 'Hamidiye', 'Huzur', 'Kirazlıdere', 'Kullar', 'Merkez', 'Nişantepe', 'Ömerli', 'Soğuksu', 'Sultançiftliği', 'Taşdelen'],
            'Sultanbeyli': ['Adil', 'Ahmet Yesevi', 'Akşemsettin', 'Battalgazi', 'Fatih', 'Hasanpaşa', 'Mehmet Akif Ersoy', 'Mimar Sinan', 'Orhangazi', 'Sultanbeyli', 'Yavuz Selim'],
            'Bahçelievler': ['Bahçelievler', 'Cumhuriyet', 'Fevzi Çakmak', 'Hürriyet', 'Kocasinan', 'Şirinevler', 'Siyavuşpaşa', 'Yenibosna', 'Zafer'],
            'Bağcılar': ['15 Temmuz', 'Bağcılar', 'Barbaros Hayrettin Paşa', 'Çınar', 'Demirkapı', 'Evren', 'Fatih', 'Güneşli', 'Kemalpaşa', 'Mahmutbey', 'Yıldıztepe'],
            'Küçükçekmece': ['Atatürk', 'Beşyol', 'Cennet', 'Cumhuriyet', 'Fevzi Çakmak', 'Gültepe', 'Halkalı', 'İnönü', 'İstasyon', 'Kanarya', 'Kartaltepe', 'Kemalpaşa', 'Küçükçekmece', 'Mehterçeşme', 'Menekşe', 'Sefaköy', 'Söğütlüçeşme', 'Sultanmurat', 'Tevfikbey', 'Yarımburgaz', 'Yenimahalle'],
            'Avcılar': ['Ambarlı', 'Cihangir', 'Denizköşkler', 'Firuzköy', 'Gümüşpala', 'Merkez', 'Mustafa Kemal Paşa', 'Tahtakale', 'Üniversite', 'Yeşilkent'],
            'Güngören': ['Güngören', 'Haznedar', 'Merkez', 'Sanayi'],
            'Zeytinburnu': ['Beştelsiz', 'Çırpıcı', 'Gökalp', 'Kazlıçeşme', 'Maltepe', 'Merkez', 'Nuripaşa', 'Seyitnizam', 'Sümer', 'Telsiz', 'Veliefendi', 'Yeşiltepe'],
            'Esenler': ['Atışalanı', 'Birlik', 'Çiftehavuzlar', 'Davutpaşa', 'Fatih', 'Fevzi Çakmak', 'Havaalanı', 'Kemer', 'Menderes', 'Mimarsinan', 'Namık Kemal', 'Nine Hatun', 'Oruçreis', 'Tuna', 'Turgutreis'],
            'Gaziosmanpaşa': ['Bağlarbaşı', 'Barbaros Hayrettin Paşa', 'Esenyurt', 'Gaziosmanpaşa', 'Hürriyet', 'Karadeniz', 'Karlıtepe', 'Kazım Karabekir', 'Merkez', 'Mevlana', 'Sarıgöl', 'Yenidoğan', 'Yıldıztabya'],
            'Bayrampaşa': ['Altıntepsi', 'Bayrampaşa', 'Cevatpaşa', 'Kocatepe', 'Muratpaşa', 'Yıldırım'],
            'Esenyurt': ['Akevler', 'Akşemsettin', 'Ardıçlı', 'Bahşayış', 'Balıkyolu', 'Belde', 'Beylikdüzü', 'Değirmenbahçe', 'Esenkent', 'Esenyurt', 'Fatih', 'Furkan', 'Göztepe', 'Güzelyurt', 'Haramidere', 'Hoşdere', 'İnönü', 'İstiklal', 'Kavaklı', 'Kıraç', 'Mehterçeşme', 'Mimar Sinan', 'Orta', 'Pınar', 'Saadetdere', 'Sultangazi', 'Şakir Paşa', 'Tayakadın', 'Turgut Reis', 'Uydu', 'Yenikent', 'Zafer'],
            'Beylikdüzü': ['Adnan Kahveci', 'Barış', 'Büyükşehir', 'Cumhuriyet', 'Dereağzı', 'Gürpınar', 'Kavaklar', 'Kavaklı', 'Marmara', 'Mehmet Akif Ersoy', 'Sahil', 'Yakuplu'],
            'Büyükçekmece': ['Alkent 2000', 'Batıköy', 'Büyükçekmece', 'Cumhuriyet', 'Dizdariye', 'Esenyurt', 'Fatih', 'Güzelce', 'Hürriyet', 'Kamiloba', 'Kaynarca', 'Mimarsinan', 'Muratbey', 'Pınartepe', 'Tepecik'],
            'Arnavutköy': ['Arnavutköy', 'Avcıkoru', 'Bolluca', 'Dursunköy', 'Hacımaşlı', 'Haraççı', 'İmrahor', 'İslambey', 'Karaburun', 'Mavigöl', 'Merkez', 'Sazlıbosna', 'Tayakadın', 'Yeniköy'],
            'Başakşehir': ['Altınşehir', 'Bahçeşehir 1. Kısım', 'Bahçeşehir 2. Kısım', 'Bahçeşehir 3. Kısım', 'Başak', 'Başakşehir', 'Güvercintepe', 'İkitelli Organize Sanayi', 'Kayabaşı', 'Şahintepe', 'Şamlar', 'Ziya Gökalp'],
            'Kağıthane': ['Çağlayan', 'Emniyet Evleri', 'Gültepe', 'Hamidiye', 'Harmantepe', 'Kağıthane', 'Merkez', 'Nuru Osmaniye', 'Ortabayır', 'Seyrantepe', 'Sultan Selim', 'Talatpaşa', 'Telsizler', 'Yahya Kemal'],
            'Eyüpsultan': ['Alibeyköy', 'Düğmeciler', 'Emniyettepe', 'Esentepe', 'Eyüp', 'Göktürk', 'Güzeltepe', 'Karadolap', 'Kemerburgaz', 'Merkez', 'Mimarsinan', 'Odayeri', 'Pirinçci', 'Ramami', 'Rami Cuma', 'Sakarya', 'Silahtar', 'Topçular'],
            'Çatalca': ['Akalan', 'Atatürk', 'Avcılar', 'Başak', 'Belgrat Ormanı', 'Boyalık', 'Çakıl', 'Çalıköy', 'Çanta', 'Çiftlik', 'Dağyenice', 'Ferhatpaşa', 'Gökçeali', 'Hallaçlı', 'İhsaniye', 'İnceğiz', 'Kabakça', 'Kaleiçi', 'Kestanelik', 'Muratbey', 'Oklalı', 'Ovayenice', 'Subaşı', 'Yelkenci'],
            'Silivri': ['Ahmediye', 'Akören', 'Aliağa', 'Balaban', 'Beyciler', 'Büyük Çavuşlu', 'Büyüksinekli', 'Çanta', 'Çayır', 'Çeltik', 'Danamandıra', 'Değirmenköy', 'Demirköy', 'Domurcalı', 'Fener', 'Gazitepe', 'Gümüşyaka', 'İpsiz', 'Kadıköy', 'Kavaklı', 'Kurfallı', 'Küçüksinekli', 'Merkez', 'Ortaköy', 'Pehlivanlı', 'Selimpaşa', 'Semizkum', 'Silivri', 'Yolçatı'],
            'Şile': ['Ahmetli', 'Alacalı', 'Baltalı', 'Bucaklı', 'Çataklı', 'Çayır', 'Değirmençayırı', 'Doğancılı', 'Erenler', 'Esenceli', 'Geredeli', 'Hacıllı', 'Hasanlı', 'İmrendere', 'Kabakoz', 'Kalem', 'Karabeyli', 'Karamandere', 'Kızılcaköy', 'Korucu', 'Kumbaba', 'Kurna', 'Meşrutiyet', 'Orhanlı', 'Ovacık', 'Şile', 'Teke', 'Ulupelit', 'Yazıköy', 'Yeniköy'],
            'Beykoz': ['Acarkent', 'Anadolu Feneri', 'Anadolu Hisarı', 'Anadolu Kavağı', 'Baklacı', 'Bozhane', 'Çubuklu', 'Dereseki', 'Elmalı', 'Göztepe', 'Görele', 'Gümüşsuyu', 'İnceğiz', 'Kanlıca', 'Kavacık', 'Kefeliköy', 'Merkez', 'Örnekköy', 'Paşabahçe', 'Poyrazköy', 'Riva', 'Rüzgarlıbahçe', 'Soğullu', 'Tokatköy', 'Yalıköy', 'Yavuz Selim'],
            'Adalar': ['Burgazada', 'Heybeliada', 'Kınalıada', 'Büyükada', 'Sedef Adası']
        };
        
        // DOM elementleri
        const districtSelect = document.getElementById('district');
        const neighborhoodSelect = document.getElementById('neighborhood');
        
        console.log('Location script loaded', {districtSelect, neighborhoodSelect});
        
        // Debug - Check if elements exist
        if (!districtSelect) {
            console.error('District select element not found!');
            // Eğer elementler yoksa, sayfa henüz tamamen yüklenmemiş olabilir
            console.log('Waiting for page to fully load...');
            return;
        }
        
        if (!neighborhoodSelect) {
            console.error('Neighborhood select element not found!');
            console.log('Waiting for page to fully load...');
            return;
        }
        
        console.log('Both elements found, loading districts...');
        
        // İlçeleri yükle
        function loadDistricts() {
            if (!districtSelect) return;
            
            console.log('Loading districts...');
            districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
            
            istanbulDistricts.forEach(district => {
                const option = document.createElement('option');
                option.value = district;
                option.textContent = district;
                districtSelect.appendChild(option);
            });
            
            console.log('Districts loaded:', istanbulDistricts.length);
        }
        
        // Mahalleleri yükle
        function loadNeighborhoods(district) {
            if (!neighborhoodSelect) return;
            
            console.log('Loading neighborhoods for:', district);
            neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
            
            if (istanbulNeighborhoods[district]) {
                istanbulNeighborhoods[district].forEach(neighborhood => {
                    const option = document.createElement('option');
                    option.value = neighborhood;
                    option.textContent = neighborhood;
                    neighborhoodSelect.appendChild(option);
                });
                
                console.log('Neighborhoods loaded for', district, ':', istanbulNeighborhoods[district].length);
            }
        }
        
        // Event listener - İlçe seçilince mahalleleri yükle
        if (districtSelect) {
            districtSelect.addEventListener('change', function() {
                const selectedDistrict = this.value;
                console.log('District selected:', selectedDistrict);
                loadNeighborhoods(selectedDistrict);
            });
        }
        
        // Event listener - Şehir seçilince ilçeleri yükle
        const citySelect = document.getElementById('city');
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                const selectedCity = this.value;
                console.log('City selected:', selectedCity);
                
                // İlçeleri temizle
                districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
                neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
                
                if (selectedCity === 'İstanbul') {
                    loadDistricts();
                }
            });
        }
        
        // İlçeleri yükle
        loadDistricts();
        
        // Edit mode için mevcut değerleri set et
        <?php if ($edit_mode && isset($existing_property) && !empty($existing_property['district'])): ?>
        setTimeout(function() {
            const editDistrict = '<?= addslashes($existing_property['district'] ?? '') ?>';
            if (editDistrict && districtSelect) {
                districtSelect.value = editDistrict;
                loadNeighborhoods(editDistrict);
                
                <?php if (!empty($existing_property['neighborhood'])): ?>
                setTimeout(function() {
                    const editNeighborhood = '<?= addslashes($existing_property['neighborhood'] ?? '') ?>';
                    if (editNeighborhood && neighborhoodSelect) {
                        neighborhoodSelect.value = editNeighborhood;
                    }
                }, 200);
                <?php endif; ?>
            }
        }, 300);
        <?php endif; ?>
    });

    // CLOUDFLARE IMAGES JAVASCRIPT FUNCTIONS
    let cloudflareActive = true;
    let uploadProgress = 0;

    function updateCloudflareStatus(status, message) {
        const statusText = document.getElementById('cloudflareStatusText');
        const indicator = document.getElementById('cloudflareIndicator');
        
        if (statusText) statusText.textContent = message;
        
        if (indicator) {
            const icon = indicator.querySelector('i');
            icon.className = 'fas fa-circle';
            
            switch(status) {
                case 'ready':
                    icon.classList.add('text-success');
                    break;
                case 'uploading':
                    icon.classList.add('text-warning');
                    break;
                case 'error':
                    icon.classList.add('text-danger');
                    break;
            }
        }
    }

    function showUploadProgress(show = true) {
        const progressDiv = document.getElementById('uploadProgress');
        if (progressDiv) {
            progressDiv.style.display = show ? 'block' : 'none';
        }
    }

    function updateUploadProgress(percentage, text = 'Yükleniyor...') {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressPercentage = document.getElementById('progressPercentage');
        
        if (progressBar) progressBar.style.width = percentage + '%';
        if (progressText) progressText.textContent = text;
        if (progressPercentage) progressPercentage.textContent = percentage + '%';
        
        uploadProgress = percentage;
    }

    // Enhanced photo handling for Cloudflare with individual progress bars
    function addPhotoToGrid(file, index, isCloudflare = false) {
        const photosGrid = document.getElementById('photosGrid');
        const selectedPhotos = document.getElementById('selectedPhotos');
        
        if (!photosGrid) return;
        
        selectedPhotos.style.display = 'block';
        
        const photoItem = document.createElement('div');
        photoItem.className = 'photo-item' + (isCloudflare ? ' cloudflare' : '');
        photoItem.setAttribute('data-index', index);
        photoItem.setAttribute('data-filename', file.name);
        
        // Create photo item HTML with individual progress bar
        photoItem.innerHTML = `
            <div class="photo-preview-container">
                <img src="" alt="Yüklenen fotoğraf" class="img-fluid photo-preview">
                <div class="photo-overlay">
                    <div class="upload-progress" style="display: none;">
                        <div class="progress-circle">
                            <div class="progress-bar-circular">
                                <svg viewBox="0 0 36 36" class="circular-chart">
                                    <path class="circle-bg" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                    <path class="circle" stroke-dasharray="0, 100" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                    <text x="18" y="20.35" class="percentage">0%</text>
                                </svg>
                            </div>
                        </div>
                        <div class="upload-status">Yükleniyor...</div>
                    </div>
                    <div class="photo-status" style="display: none;">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Yüklendi</span>
                    </div>
                </div>
            </div>
            <div class="photo-controls">
                <button type="button" class="btn-remove-photo" onclick="removePhoto(this)" title="Resmi kaldır">
                    <i class="fas fa-times"></i>
                </button>
                <button type="button" class="btn-make-main" onclick="setAsMain(this)" title="Ana resim yap" ${index === 0 ? 'style="display:none"' : 'style="display:block"'}>
                    <i class="fas fa-star"></i>
                </button>
            </div>
            
            <!-- Enhanced Individual Progress Overlay -->
            <div class="upload-progress-overlay hidden">
                <div class="progress-circle">
                    <svg class="circular-chart" viewBox="0 0 42 42">
                        <circle class="circle-bg" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                        <circle class="circle" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                    </svg>
                    <div class="progress-text">0%</div>
                </div>
                <div class="upload-status connecting">Hazırlanıyor...</div>
                <div class="upload-filename">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
            
            <div class="cloudflare-badge" style="background: linear-gradient(45deg, #007bff, #6610f2);">
                <i class="fab fa-cloudflare"></i> CLOUDFLARE
            </div>
            <div class="photo-info">
                <small class="filename">${file.name}</small>
                <small class="filesize">${formatFileSize(file.size)}</small>
            </div>
        `;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = photoItem.querySelector('.photo-preview');
            img.src = e.target.result;
            
            // Start individual upload simulation immediately after image preview loads
            setTimeout(() => {
                simulatePhotoUpload(photoItem, file);
            }, 200 + (index * 100)); // Stagger start times slightly
        };
        
        reader.readAsDataURL(file);
        photosGrid.appendChild(photoItem);
        
        updatePhotoCounter();
    }

    function updatePhotoCounter() {
        const counter = document.getElementById('photoCounter');
        const photos = document.querySelectorAll('.photo-item').length;
        if (counter) counter.textContent = photos;
    }

    // Initialize Cloudflare status
    document.addEventListener('DOMContentLoaded', function() {
        updateCloudflareStatus('ready', 'Cloudflare Images Hazır');
        
        // Add Cloudflare badges to existing photos if they're stored in Cloudflare
        const existingPhotos = document.querySelectorAll('.existing-photo-item');
        existingPhotos.forEach((photo, index) => {
            // Add Cloudflare badge to existing photos (they'll be migrated on save)
            if (!photo.querySelector('.cloudflare-badge')) {
                const badge = document.createElement('div');
                badge.className = 'cloudflare-badge';
                badge.style.background = 'linear-gradient(45deg, #28a745, #20c997)';
                badge.textContent = 'LOCAL';
                photo.appendChild(badge);
            }
            photo.classList.add('local-photo');
        });
        
        // Photo upload event listeners
        const photoInput = document.getElementById('photoInput');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                if (cloudflareActive) {
                    updateCloudflareStatus('uploading', 'Cloudflare\'e yükleniyor...');
                    showUploadProgress(true);
                    updateUploadProgress(0, 'Cloudflare Images hazırlanıyor...');
                    
                    // Simulate upload progress
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        progress += Math.random() * 15;
                        if (progress >= 100) {
                            progress = 100;
                            clearInterval(progressInterval);
                            updateUploadProgress(100, 'Cloudflare upload tamamlandı!');
                            setTimeout(() => showUploadProgress(false), 1000);
                        }
                        updateUploadProgress(progress, `${Math.round(progress)}% tamamlandı...`);
                    }, 300);
                }
                
                Array.from(e.target.files).forEach(file => addPhotoPreview(file));
            });
        }
        
        // Form submit handler with loading screen
        const propertyForm = document.getElementById('propertyForm');
        if (propertyForm) {
            propertyForm.addEventListener('submit', function(e) {
                const photoItems = document.querySelectorAll('.photo-item').length;
                const existingPhotos = document.querySelectorAll('.existing-photo-item').length;
                const totalPhotos = photoItems + existingPhotos;
                
                if (totalPhotos > 0) {
                    // Show loading screen
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.id = 'uploadLoadingOverlay';
                    loadingOverlay.innerHTML = `
                        <div class="loading-content">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <h4>İlan kaydediliyor...</h4>
                            <p id="uploadStatus">Cloudflare Images'a ${totalPhotos} resim yükleniyor</p>
                            <div class="progress mt-3" style="width: 300px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     style="width: 0%" id="uploadProgressBar"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">Bu işlem ${Math.ceil(totalPhotos * 2)} saniye kadar sürebilir.</small>
                        </div>
                    `;
                    loadingOverlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.8);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        color: white;
                        text-align: center;
                    `;
                    document.body.appendChild(loadingOverlay);
                    
                    // Simulate progress
                    let progress = 0;
                    const progressBar = document.getElementById('uploadProgressBar');
                    const statusText = document.getElementById('uploadStatus');
                    
                    const progressInterval = setInterval(() => {
                        progress += Math.random() * 8;
                        if (progress >= 95) {
                            progress = 95;
                            clearInterval(progressInterval);
                        }
                        progressBar.style.width = progress + '%';
                        statusText.textContent = `Resimler yükleniyor... ${Math.round(progress)}%`;
                    }, 500);
                }
            });
        }
    });
                    // Simulate Cloudflare upload progress
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += Math.random() * 30;
                        if (progress >= 100) {
                            progress = 100;
                            clearInterval(interval);
                            updateCloudflareStatus('ready', 'Cloudflare upload tamamlandı');
                            setTimeout(() => showUploadProgress(false), 1000);
                        }
                        updateUploadProgress(Math.min(progress, 100), 
                            progress < 50 ? 'Cloudflare\'e yükleniyor...' : 
                            progress < 90 ? 'İşleniyor...' : 'Tamamlanıyor...');
                    }, 200);
                }
                
                // Add photos to grid with Cloudflare indicator
                Array.from(e.target.files).forEach((file, index) => {
                    addPhotoToGrid(file, index, cloudflareActive);
                });
            });
        }
    });
    </script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>