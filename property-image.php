<?php
// Property image serving service
// Bu dosya property resimlerini güvenli bir şekilde serve eder

// Get image path from query parameter
$image_path = isset($_GET['path']) ? $_GET['path'] : '';

if (empty($image_path)) {
    // Default property image
    $default_image = 'images/default.png';
    if (file_exists($default_image)) {
        $image_path = $default_image;
    } else {
        http_response_code(404);
        exit('Image not found');
    }
} else {
    // Sanitize path to prevent directory traversal
    $image_path = basename($image_path);
    
    // İlk önce uploads/properties/ klasöründe ara (yeni ilanlar için)
    $full_path = 'uploads/properties/' . $image_path;
    
    // Eğer uploads'da yoksa, images/properties/ klasöründe ara (eski ilanlar ve profil resimleri için)
    if (!file_exists($full_path)) {
        $full_path = 'images/properties/' . $image_path;
    }
    
    if (!file_exists($full_path)) {
        // Try fallback to default
        $default_image = 'images/default.png';
        if (file_exists($default_image)) {
            $full_path = $default_image;
        } else {
            http_response_code(404);
            exit('Image not found');
        }
    }
    
    $image_path = $full_path;
}

// Get file info
$file_info = pathinfo($image_path);
$extension = strtolower($file_info['extension']);

// Set appropriate content type
switch ($extension) {
    case 'jpg':
    case 'jpeg':
        $content_type = 'image/jpeg';
        break;
    case 'png':
        $content_type = 'image/png';
        break;
    case 'gif':
        $content_type = 'image/gif';
        break;
    case 'webp':
        $content_type = 'image/webp';
        break;
    default:
        $content_type = 'application/octet-stream';
}

// Security check: ensure it's actually an image
$image_info = @getimagesize($image_path);
if ($image_info === false && $image_path !== 'images/default.png') {
    http_response_code(404);
    exit('Invalid image file');
}

// Set cache headers
header('Content-Type: ' . $content_type);
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($image_path)) . ' GMT');

// Handle conditional requests
$etag = md5_file($image_path);
header('ETag: "' . $etag . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304);
    exit();
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    $file_modified_time = filemtime($image_path);
    
    if ($file_modified_time <= $if_modified_since) {
        http_response_code(304);
        exit();
    }
}

// Output the image
header('Content-Length: ' . filesize($image_path));
readfile($image_path);
?>
