<?php
session_start();
require_once '../db.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON response header
header('Content-Type: application/json');

// Güvenlik kontrolü
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Oturum gerekli']);
    exit;
}

// JSON verilerini al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("🗑️ DELETE IMAGE - Input: " . $input);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı']);
    exit;
}

$property_id = $data['propertyId'] ?? null;
$image_index = $data['imageIndex'] ?? null;

if (!$property_id || $image_index === null) {
    echo json_encode(['success' => false, 'message' => 'Eksik parametreler: propertyId ve imageIndex gerekli']);
    exit;
}

try {
    // Mevcut resim sırasını al
    $stmt = $conn->prepare("SELECT images FROM properties WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'İlan bulunamadı veya yetkiniz yok']);
        exit;
    }
    
    $property = $result->fetch_assoc();
    $images = json_decode($property['images'], true);
    
    if (!is_array($images) || empty($images)) {
        echo json_encode(['success' => false, 'message' => 'Resim bulunamadı']);
        exit;
    }
    
    // Minimum resim kontrolü
    if (count($images) <= 1) {
        echo json_encode(['success' => false, 'message' => 'En az bir resim kalmalıdır']);
        exit;
    }
    
    // Index kontrolü
    if (!isset($images[$image_index])) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz resim indexi']);
        exit;
    }
    
    $deleted_image = $images[$image_index];
    
    // Resmi arrayden çıkar
    unset($images[$image_index]);
    
    // Array indexlerini yeniden düzenle
    $images = array_values($images);
    
    // Main image güncelle (ilk resim ana resim olacak)
    $new_main_image = !empty($images) ? $images[0] : '';
    
    // Database'i güncelle
    $new_images_json = json_encode($images, JSON_UNESCAPED_UNICODE);
    $update_stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ssii", $new_images_json, $new_main_image, $property_id, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        error_log("✅ DELETE IMAGE SUCCESS - Property: $property_id, Deleted: $deleted_image, New main: $new_main_image");
        echo json_encode([
            'success' => true, 
            'message' => 'Resim başarıyla silindi',
            'deleted_image' => $deleted_image,
            'new_main_image' => $new_main_image,
            'remaining_images' => $images
        ]);
    } else {
        error_log("❌ DELETE IMAGE DB ERROR: " . $update_stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database güncelleme hatası']);
    }
    
} catch (Exception $e) {
    error_log("❌ DELETE IMAGE EXCEPTION: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()]);
}
?>
    if (count($images) <= 1) {
        echo json_encode(['success' => false, 'message' => 'İlanda en az 1 resim bulunmalıdır']);
        exit;
    }
    
    // Seçilen resmi listeden çıkar
    $new_images = [];
    $image_found = false;
    
    foreach ($images as $img) {
        if (trim($img) !== trim($image_path)) {
            $new_images[] = trim($img);
        } else {
            $image_found = true;
        }
    }
    
    if (!$image_found) {
        echo json_encode(['success' => false, 'message' => 'Belirtilen resim bulunamadı']);
        exit;
    }
    
    // Veritabanını güncelle
    $new_images_string = implode(',', $new_images);
    $update_stmt = $conn->prepare("UPDATE properties SET images = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $new_images_string, $property_id, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        // Cloudflare'den resmi silmeye çalış (isteğe bağlı)
        // Extract Cloudflare ID if it's a Cloudflare image
        if (preg_match('/\/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})\//', $image_path, $matches)) {
            $cloudflare_id = $matches[1];
            
            // Cloudflare Images API ile silme işlemi (isteğe bağlı)
            $cloudflare_api_url = "https://api.cloudflare.com/client/v4/accounts/prdw3ANMyocSBJD-Do1EeQ/images/v1/" . $cloudflare_id;
            
            // API token'ın tanımlı olup olmadığını kontrol et
            if (defined('CLOUDFLARE_API_TOKEN')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $cloudflare_api_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Authorization: Bearer " . CLOUDFLARE_API_TOKEN,
                    "Content-Type: application/json"
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                // Cloudflare silme sonucunu log'la ama hata vermeme
                if ($http_code === 200) {
                    error_log("Cloudflare resim silindi: " . $cloudflare_id);
                } else {
                    error_log("Cloudflare resim silinemedi: " . $cloudflare_id . " - HTTP: " . $http_code);
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Resim başarıyla silindi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı güncellemesi başarısız']);
    }
    
} catch (Exception $e) {
    error_log("Resim silme hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası']);
}
?>
