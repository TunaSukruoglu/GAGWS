<?php
session_start();
include __DIR__ . '/../db.php';

// JSON header
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Favorilere eklemek için giriş yapmalısınız']);
    exit;
}

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);
$property_id = $input['property_id'] ?? ($_POST['property_id'] ?? null);
$user_id = $_SESSION['user_id'];

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ilan ID']);
    exit;
}

try {
    // İlan var mı kontrol et
    $property_check = $conn->prepare("SELECT id FROM properties WHERE id = ?");
    $property_check->bind_param("i", $property_id);
    $property_check->execute();
    $property_exists = $property_check->get_result()->num_rows > 0;
    
    if (!$property_exists) {
        echo json_encode(['success' => false, 'message' => 'İlan bulunamadı']);
        exit;
    }
    
    // Zaten favoride mi kontrol et
    $check_query = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
    $check_query->bind_param("ii", $user_id, $property_id);
    $check_query->execute();
    $already_favorite = $check_query->get_result()->num_rows > 0;
    
    if ($already_favorite) {
        echo json_encode(['success' => false, 'message' => 'İlan zaten favorilerinizde']);
        exit;
    }
    
    // Favoriye ekle
    $query = $conn->prepare("INSERT INTO favorites (user_id, property_id, created_at) VALUES (?, ?, NOW())");
    $query->bind_param("ii", $user_id, $property_id);
    
    if ($query->execute()) {
        echo json_encode(['success' => true, 'message' => 'İlan favorilere eklendi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Favoriye eklenirken hata oluştu']);
    }
    
} catch (Exception $e) {
    error_log("Add Favorite Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
