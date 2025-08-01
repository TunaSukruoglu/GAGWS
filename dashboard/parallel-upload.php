<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 504 TIMEOUT FIX - URGENT
ini_set('max_execution_time', 300); // 5 dakika per resim
ini_set('memory_limit', '256M');
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '100M');
set_time_limit(300);

// Security check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
require_once '../includes/cloudflare-images-multi-domain.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$uploadStart = microtime(true);
$file = $_FILES['image'];
$fileName = $file['name'];
$tmpName = $file['tmp_name'];
$fileSize = $file['size'];

try {
    // File validation
    if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
        throw new Exception('File too large: ' . round($fileSize / (1024 * 1024), 1) . 'MB');
    }
    
    $mimeType = mime_content_type($tmpName);
    if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
        throw new Exception('Invalid file type: ' . $mimeType);
    }
    
    // Get image dimensions
    $imageInfo = getimagesize($tmpName);
    if ($imageInfo === false) {
        throw new Exception('Invalid image file');
    }
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Upload to Cloudflare
    $cloudflare = new MultiDomainCloudflareImages();
    $uploadResult = $cloudflare->simpleUpload($tmpName, [
        'originalName' => $fileName,
        'uploadTime' => date('Y-m-d H:i:s'),
        'parallel' => true,
        'fileSize' => $fileSize
    ]);
    
    if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success'] && isset($uploadResult['image_id'])) {
        $uploadTime = round((microtime(true) - $uploadStart) * 1000, 2);
        $speedMBps = round(($fileSize / 1024 / 1024) / ($uploadTime / 1000), 2);
        
        $response = [
            'success' => true,
            'image_id' => $uploadResult['image_id'],
            'url' => "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $uploadResult['image_id'] . "/public",
            'filename' => $fileName,
            'size' => $fileSize,
            'upload_time_ms' => $uploadTime,
            'speed_mbps' => $speedMBps,
            'width' => $width,
            'height' => $height
        ];
        
        echo json_encode($response);
    } else {
        throw new Exception('Cloudflare upload failed');
    }
    
} catch (Exception $e) {
    $uploadTime = round((microtime(true) - $uploadStart) * 1000, 2);
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'filename' => $fileName,
        'upload_time_ms' => $uploadTime
    ]);
}
?>
