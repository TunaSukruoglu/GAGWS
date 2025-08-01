<?php
// Akıllı resim servisi - boyutlara göre yönlendirme
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Güvenlik kontrolü
if (!isset($_GET['img'])) {
    http_response_code(404);
    if (isset($_GET['debug'])) {
        echo "DEBUG: No 'img' parameter provided";
    }
    exit('Image not found');
}

$requested_image = $_GET['img'];
$debug = isset($_GET['debug']) ? true : false;

if ($debug) {
    echo "DEBUG: Requested image: $requested_image<br>";
}

// Güvenlik: path traversal saldırılarına karşı koruma
if (strpos($requested_image, '..') !== false || 
    strpos($requested_image, '/') !== false || 
    strpos($requested_image, '\\') !== false) {
    http_response_code(403);
    exit('Access denied');
}

// Desteklenen dosya uzantıları
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$file_extension = strtolower(pathinfo($requested_image, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    http_response_code(400);
    exit('Invalid file type');
}

// Resim yolunu bul
$possible_paths = [
    __DIR__ . '/dashboard/uploads/properties/' . $requested_image,
    __DIR__ . '/uploads/properties/' . $requested_image,
    __DIR__ . '/images/' . $requested_image,
];

$image_path = null;
foreach ($possible_paths as $path) {
    if ($debug) {
        echo "DEBUG: Checking path: $path - " . (file_exists($path) ? 'EXISTS' : 'NOT FOUND') . "<br>";
    }
    if (file_exists($path) && is_file($path)) {
        $image_path = $path;
        break;
    }
}

if (!$image_path) {
    http_response_code(404);
    if ($debug) {
        echo "DEBUG: No image found in any of the possible paths";
    }
    exit('Image not found');
}

// Resim bilgilerini al
$image_info = getimagesize($image_path);
if (!$image_info) {
    http_response_code(500);
    exit('Invalid image');
}

list($original_width, $original_height, $image_type) = $image_info;

// Resim yönlendirmesini belirle
$is_portrait = $original_height > $original_width;
$should_rotate = false;

// EXIF verilerini kontrol et (JPEG için)
if ($image_type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
    $exif = @exif_read_data($image_path);
    if ($exif && isset($exif['Orientation'])) {
        $orientation = $exif['Orientation'];
        // Orientation değerlerine göre rotation gerekip gerekmediğini belirle
        switch ($orientation) {
            case 3:
                $should_rotate = 180;
                break;
            case 6:
                $should_rotate = 90;
                break;
            case 8:
                $should_rotate = -90;
                break;
            default:
                $should_rotate = false;
        }
    }
}

// Debug bilgileri
if ($debug) {
    echo "<h2>Smart Image Debug</h2>";
    echo "<p><strong>Original Size:</strong> {$original_width}x{$original_height}</p>";
    echo "<p><strong>Aspect Ratio:</strong> " . ($is_portrait ? 'Portrait' : 'Landscape') . "</p>";
    echo "<p><strong>Should Rotate:</strong> " . ($should_rotate ? $should_rotate . '°' : 'No') . "</p>";
    if (isset($exif['Orientation'])) {
        echo "<p><strong>EXIF Orientation:</strong> {$exif['Orientation']}</p>";
    }
    echo "<img src='smart-image.php?img=$requested_image' style='max-width: 400px; border: 1px solid #red;'>";
    exit;
}

// Hedef boyutları belirle
$target_width = 800;
$target_height = 600;

// Portrait resimler için farklı boyutlar
if ($is_portrait) {
    $target_width = 600;
    $target_height = 800;
}

// Resim yükleme
$source_image = null;
switch ($image_type) {
    case IMAGETYPE_JPEG:
        $source_image = imagecreatefromjpeg($image_path);
        break;
    case IMAGETYPE_PNG:
        $source_image = imagecreatefrompng($image_path);
        break;
    case IMAGETYPE_GIF:
        $source_image = imagecreatefromgif($image_path);
        break;
    case IMAGETYPE_WEBP:
        if (function_exists('imagecreatefromwebp')) {
            $source_image = imagecreatefromwebp($image_path);
        }
        break;
}

if (!$source_image) {
    // GD hatası durumunda orijinal dosyayı serve et
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . filesize($image_path));
    readfile($image_path);
    exit;
}

// Rotation uygula
if ($should_rotate) {
    $rotated_image = imagerotate($source_image, -$should_rotate, 0);
    if ($rotated_image) {
        imagedestroy($source_image);
        $source_image = $rotated_image;
        
        // Rotation sonrası boyutları güncelle
        $original_width = imagesx($source_image);
        $original_height = imagesy($source_image);
    }
}

// Resize hesaplaması
$ratio = min($target_width / $original_width, $target_height / $original_height);
$new_width = round($original_width * $ratio);
$new_height = round($original_height * $ratio);

// Yeni resim oluştur
$new_image = imagecreatetruecolor($new_width, $new_height);

// Transparency koru
if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
    imagealphablending($new_image, false);
    imagesavealpha($new_image, true);
    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
    imagefill($new_image, 0, 0, $transparent);
}

// Resample
imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

// Headers
$mime_type = 'image/jpeg';
if ($image_type == IMAGETYPE_PNG) $mime_type = 'image/png';
if ($image_type == IMAGETYPE_GIF) $mime_type = 'image/gif';
if ($image_type == IMAGETYPE_WEBP) $mime_type = 'image/webp';

header('Content-Type: ' . $mime_type);
header('Cache-Control: public, max-age=86400');
header('ETag: "' . md5_file($image_path) . '"');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($image_path)) . ' GMT');

// Resmi output et
ob_start();
switch ($image_type) {
    case IMAGETYPE_JPEG:
        imagejpeg($new_image, null, 85);
        break;
    case IMAGETYPE_PNG:
        imagepng($new_image, null, 6);
        break;
    case IMAGETYPE_GIF:
        imagegif($new_image);
        break;
    case IMAGETYPE_WEBP:
        if (function_exists('imagewebp')) {
            imagewebp($new_image, null, 85);
        } else {
            imagejpeg($new_image, null, 85);
        }
        break;
}
$image_data = ob_get_contents();
ob_end_clean();

header('Content-Length: ' . strlen($image_data));
echo $image_data;

// Belleği temizle
imagedestroy($source_image);
imagedestroy($new_image);
exit;
?>
