<?php
session_start();
include '../../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

// Upload dizinini kontrol et ve oluştur
$upload_dir = '../../uploads/blog-images/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Dosya kontrolü
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Dosya yükleme hatası: ' . $file['error']]);
        exit;
    }
    
    // Dosya türü kontrolü
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $file['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Sadece resim dosyaları yükleyebilirsiniz (JPG, PNG, GIF, WebP)']);
        exit;
    }
    
    // Dosya boyutu kontrolü (5MB max)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['error' => 'Dosya boyutu 5MB\'dan küçük olmalıdır']);
        exit;
    }
    
    // Güvenli dosya adı oluştur
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_filename = 'blog_image_' . time() . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $safe_filename;
    
    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Resmi optimize et (opsiyonel)
        optimizeImage($file_path, $file_type);
        
        // Başarılı response
        $file_url = '../uploads/blog-images/' . $safe_filename;
        echo json_encode([
            'location' => $file_url,
            'filename' => $safe_filename,
            'size' => filesize($file_path),
            'type' => $file_type
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Dosya kaydedilemedi']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz istek']);
}

// Resim optimizasyon fonksiyonu
function optimizeImage($file_path, $file_type) {
    // GD extension kontrolü
    if (!extension_loaded('gd')) {
        return false;
    }
    
    try {
        $image = null;
        
        // Resim türüne göre yükle
        switch ($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = imagecreatefromwebp($file_path);
                }
                break;
        }
        
        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Maksimum boyut kontrolü
            $max_width = 1200;
            $max_height = 800;
            
            if ($width > $max_width || $height > $max_height) {
                // Orantılı resize
                $ratio = min($max_width / $width, $max_height / $height);
                $new_width = intval($width * $ratio);
                $new_height = intval($height * $ratio);
                
                // Yeni resim oluştur
                $new_image = imagecreatetruecolor($new_width, $new_height);
                
                // PNG transparanlığını koru
                if ($file_type === 'image/png') {
                    imagealphablending($new_image, false);
                    imagesavealpha($new_image, true);
                    $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
                    imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
                }
                
                // Resize
                imagecopyresampled(
                    $new_image, $image, 
                    0, 0, 0, 0, 
                    $new_width, $new_height, 
                    $width, $height
                );
                
                // Kaydet
                switch ($file_type) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        imagejpeg($new_image, $file_path, 85);
                        break;
                    case 'image/png':
                        imagepng($new_image, $file_path, 6);
                        break;
                    case 'image/gif':
                        imagegif($new_image, $file_path);
                        break;
                    case 'image/webp':
                        if (function_exists('imagewebp')) {
                            imagewebp($new_image, $file_path, 85);
                        }
                        break;
                }
                
                imagedestroy($new_image);
            }
            
            imagedestroy($image);
        }
    } catch (Exception $e) {
        // Hata durumunda orijinal dosyayı koru
        error_log('Resim optimizasyon hatası: ' . $e->getMessage());
    }
}
?>
