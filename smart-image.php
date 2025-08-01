<?php
// CLOUDFLARE-ONLY Smart Image Service
// Bu servis sadece Cloudflare Images'ı desteklemektedir
// Local image support tamamen kaldırılmıştır

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cloudflare Images konfigürasyonu yükle
try {
    require_once 'includes/cloudflare-images-config.php';
    require_once 'includes/cloudflare-images-multi-domain.php';
} catch (Exception $e) {
    http_response_code(503);
    exit('Cloudflare Images service unavailable');
}

// Güvenlik kontrolü
if (!isset($_GET['img'])) {
    http_response_code(404);
    exit('Image not found');
}

$requested_image = $_GET['img'];
$width = isset($_GET['width']) ? intval($_GET['width']) : 800;
$height = isset($_GET['height']) ? intval($_GET['height']) : 600;
$debug = isset($_GET['debug']) ? true : false;

if ($debug) {
    echo "DEBUG: CLOUDFLARE-ONLY mode active<br>";
    echo "DEBUG: Requested image: $requested_image<br>";
    echo "DEBUG: Requested size: {$width}x{$height}<br>";
}

// CLOUDFLARE-ONLY: Check if it's a Cloudflare image
if (strpos($requested_image, 'https://imagedelivery.net/') === 0) {
    // Already a Cloudflare URL, redirect
    if ($debug) {
        echo "DEBUG: Direct Cloudflare URL detected, redirecting<br>";
    } else {
        header('Location: ' . $requested_image);
        exit;
    }
} else if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $requested_image)) {
    // Cloudflare Image ID, generate URL
    $variant = 'public';
    if ($width <= 200) $variant = 'thumbnail';
    else if ($width <= 400) $variant = 'small';
    else if ($width <= 800) $variant = 'medium';
    else $variant = 'large';
    
    $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $requested_image . "/" . $variant;
    
    if ($debug) {
        echo "DEBUG: Cloudflare ID detected: $requested_image<br>";
        echo "DEBUG: Generated URL: $cloudflareUrl<br>";
        echo "DEBUG: Selected variant: $variant<br>";
    } else {
        header('Location: ' . $cloudflareUrl);
        exit;
    }
} else {
    // Local image request - NOT SUPPORTED in Cloudflare-only mode
    if ($debug) {
        echo "DEBUG: Local image requested but NOT SUPPORTED in Cloudflare-only mode<br>";
        echo "DEBUG: Image: $requested_image<br>";
    }
    http_response_code(410); // Gone
    exit('Local images not supported. Only Cloudflare Images are available.');
}

if ($debug) {
    echo "<br>DEBUG: Process completed without redirect";
}
?>
