<?php
// AJAX Endpoint for parallel image upload progress
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Timeout settings
ini_set('max_execution_time', 300); // 5 minutes per request
ini_set('memory_limit', '512M');
set_time_limit(300);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once '../includes/cloudflare-images-config.php';
        require_once '../includes/cloudflare-images-multi-domain.php';
        
        $cloudflare = new MultiDomainCloudflareImages();
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['file']['tmp_name'];
            $originalName = $_FILES['file']['name'];
            $fileSize = $_FILES['file']['size'];
            
            // Quick validation
            if ($fileSize > 10 * 1024 * 1024) {
                throw new Exception('File too large: ' . round($fileSize / (1024 * 1024), 1) . 'MB');
            }
            
            $mimeType = mime_content_type($tmpName);
            if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                throw new Exception('Invalid file type: ' . $mimeType);
            }
            
            // Upload to Cloudflare
            $uploadResult = $cloudflare->simpleUpload($tmpName, [
                'propertyId' => $_POST['property_id'] ?? 'new',
                'originalName' => $originalName,
                'uploadTime' => date('Y-m-d H:i:s'),
                'parallel' => true
            ]);
            
            if ($uploadResult && isset($uploadResult['success']) && $uploadResult['success'] && isset($uploadResult['image_id'])) {
                $imageId = $uploadResult['image_id'];
                $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $imageId . "/public";
                
                echo json_encode([
                    'success' => true,
                    'image_id' => $imageId,
                    'url' => $cloudflareUrl,
                    'filename' => $originalName,
                    'size' => $fileSize
                ]);
            } else {
                throw new Exception('Cloudflare upload failed');
            }
        } else {
            throw new Exception('No file uploaded or upload error');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
