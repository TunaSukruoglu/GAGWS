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

error_log("🔄 SET MAIN IMAGE - Input: " . $input);

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
    
    // Index kontrolü
    if (!isset($images[$image_index])) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz resim indexi']);
        exit;
    }
    
    // Seçilen resmi bul ve ilk sıraya taşı
    $selected_image = $images[$image_index];
    
    // Seçilen resmi arrayden çıkar
    unset($images[$image_index]);
    
    // Array indexlerini yeniden düzenle
    $images = array_values($images);
    
    // Seçilen resmi en başa ekle
    array_unshift($images, $selected_image);
    
    // Database'i güncelle
    $new_images_json = json_encode($images, JSON_UNESCAPED_UNICODE);
    $update_stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("ssii", $new_images_json, $selected_image, $property_id, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        error_log("✅ SET MAIN IMAGE SUCCESS - Property: $property_id, New main: $selected_image");
        echo json_encode([
            'success' => true, 
            'message' => 'Ana resim başarıyla değiştirildi',
            'new_main_image' => $selected_image,
            'new_order' => $images
        ]);
    } else {
        error_log("❌ SET MAIN IMAGE DB ERROR: " . $update_stmt->error);
        echo json_encode(['success' => false, 'message' => 'Database güncelleme hatası']);
    }
    
} catch (Exception $e) {
    error_log("❌ SET MAIN IMAGE EXCEPTION: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sistem hatası: ' . $e->getMessage()]);
}
?>
    $image_found = false;
    $new_images = [];
    
    // Önce seçilen resmi ekle
    foreach ($images as $img) {
        if (trim($img) === trim($image_path)) {
            $new_images[] = trim($img);
            $image_found = true;
            break;
        }
    }
    
    if (!$image_found) {
        echo json_encode(['success' => false, 'message' => 'Belirtilen resim bulunamadı']);
        exit;
    }
    
    // Diğer resimleri ekle
    foreach ($images as $img) {
        if (trim($img) !== trim($image_path)) {
            $new_images[] = trim($img);
        }
    }
    
    // Veritabanını güncelle
    $new_images_string = implode(',', $new_images);
    $update_stmt = $conn->prepare("UPDATE properties SET images = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $new_images_string, $property_id, $_SESSION['user_id']);
    
    if ($update_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ana resim başarıyla ayarlandı']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı güncellemesi başarısız']);
    }
    
} catch (Exception $e) {
    error_log("Ana resim ayarlama hatası: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Sunucu hatası']);
}
?>
