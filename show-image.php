<?php
// Güvenlik ve hata ayıklama
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DEBUG MODE
$debug = isset($_GET['debug']) ? true : false;

if ($debug) {
    echo "<h1>Show-Image.php Debug</h1>";
}

// Güvenlik kontrolü
if (!isset($_GET['img'])) {
    if ($debug) {
        echo "<p>❌ Error: No image parameter</p>";
    }
    http_response_code(404);
    exit('Image not found');
}

$requested_image = $_GET['img'];

if ($debug) {
    echo "<p><strong>Requested Image:</strong> $requested_image</p>";
}

// Güvenlik: path traversal saldırılarına karşı koruma
if (strpos($requested_image, '..') !== false || 
    strpos($requested_image, '/') !== false || 
    strpos($requested_image, '\\') !== false) {
    if ($debug) {
        echo "<p>❌ Error: Path traversal detected</p>";
    }
    http_response_code(403);
    exit('Access denied');
}

// Desteklenen dosya uzantıları
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$file_extension = strtolower(pathinfo($requested_image, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    if ($debug) {
        echo "<p>❌ Error: Invalid file extension: $file_extension</p>";
    }
    http_response_code(400);
    exit('Invalid file type');
}

if ($debug) {
    echo "<p><strong>File Extension:</strong> $file_extension</p>";
}

// Olası resim yolları (öncelik sırasına göre)
$possible_paths = [
    __DIR__ . '/dashboard/uploads/properties/' . $requested_image,
    __DIR__ . '/uploads/properties/' . $requested_image,
    __DIR__ . '/images/' . $requested_image,
    __DIR__ . '/dashboard/images/' . $requested_image
];

if ($debug) {
    echo "<h2>Checking Paths:</h2>";
    echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
}

$image_path = null;
foreach ($possible_paths as $i => $path) {
    if ($debug) {
        echo "<p><strong>Path " . ($i + 1) . ":</strong> $path</p>";
        echo "<p>- Exists: " . (file_exists($path) ? "✅ Yes" : "❌ No") . "</p>";
        echo "<p>- Is File: " . (is_file($path) ? "✅ Yes" : "❌ No") . "</p>";
        if (file_exists($path)) {
            echo "<p>- Size: " . filesize($path) . " bytes</p>";
        }
        echo "<hr>";
    }
    
    if (file_exists($path) && is_file($path)) {
        $image_path = $path;
        if ($debug) {
            echo "<p>✅ <strong>Selected Path:</strong> $path</p>";
        }
        break;
    }
}

// Resim bulunamadıysa varsayılan resim göster
if (!$image_path) {
    if ($debug) {
        echo "<p>❌ <strong>No image found!</strong> Using default image.</p>";
    }
    // Varsayılan placeholder resim yolu
    $default_image = __DIR__ . '/images/listing/img_20.jpg';
    if (file_exists($default_image)) {
        $image_path = $default_image;
        if ($debug) {
            echo "<p>✅ Using default image: $default_image</p>";
        }
    } else {
        if ($debug) {
            echo "<p>❌ Default image not found! Creating 1x1 pixel.</p>";
        }
        // Son çare: 1x1 pixel transparan PNG oluştur
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=3600');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        exit;
    }
}

if ($debug) {
    echo "<p>✅ <strong>Final Image Path:</strong> $image_path</p>";
    
    // Resim boyutlarını kontrol et
    if (file_exists($image_path)) {
        list($width, $height, $type) = getimagesize($image_path);
        echo "<p><strong>Image Dimensions:</strong> {$width}x{$height}</p>";
        echo "<p><strong>Aspect Ratio:</strong> " . ($width > $height ? 'Landscape' : 'Portrait') . "</p>";
        echo "<p><strong>Image Type:</strong> $type</p>";
    }
    
    echo "<p><strong>Ready to serve image...</strong></p>";
    exit; // Debug mode'da resim gösterme
}

// MIME type belirleme
$mime_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

$mime_type = $mime_types[$file_extension] ?? 'image/jpeg';

// Cache headers
$etag = md5_file($image_path);
$last_modified = filemtime($image_path);

header('Content-Type: ' . $mime_type);
header('Cache-Control: public, max-age=86400'); // 24 saat cache
header('ETag: "' . $etag . '"');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');

// Client-side cache kontrolü
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
    http_response_code(304);
    exit;
}

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_modified) {
    http_response_code(304);
    exit;
}

// Resim boyutlarını kontrol et ve gerekirse resize et
$max_width = 800;
$max_height = 600;

// Resim bilgilerini al
list($width, $height, $type) = getimagesize($image_path);

// Resize gerekli mi?
if ($width > $max_width || $height > $max_height) {
    // Yeni boyutları hesapla (orantıyı koru)
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // GD extension kontrolü
    if (extension_loaded('gd')) {
        // Kaynak resmi yükle
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($image_path);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($image_path);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $source = imagecreatefromwebp($image_path);
                } else {
                    $source = false;
                }
                break;
            default:
                $source = false;
        }
        
        if ($source) {
            // Yeni resim oluştur
            $resized = imagecreatetruecolor($new_width, $new_height);
            
            // PNG ve GIF için transparency koruma
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefill($resized, 0, 0, $transparent);
            }
            
            // Resize işlemi
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Content-Length header
            ob_start();
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($resized, null, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($resized, null, 6);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($resized);
                    break;
                case IMAGETYPE_WEBP:
                    if (function_exists('imagewebp')) {
                        imagewebp($resized, null, 85);
                    } else {
                        imagejpeg($resized, null, 85);
                    }
                    break;
            }
            $image_data = ob_get_contents();
            ob_end_clean();
            
            header('Content-Length: ' . strlen($image_data));
            echo $image_data;
            
            // Belleği temizle
            imagedestroy($source);
            imagedestroy($resized);
            exit;
        }
    }
}

// Resize yapılamadıysa veya gerekmiyorsa orijinal resmi gönder
header('Content-Length: ' . filesize($image_path));
readfile($image_path);
exit;
?>
