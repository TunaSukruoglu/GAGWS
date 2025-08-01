<?php
session_start();
include __DIR__ . '/../db.php';

// JSON header
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Favorilerden çıkarmak için giriş yapmalısınız']);
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
    // Favorilerden kaldır
    $query = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
    $query->bind_param("ii", $user_id, $property_id);
    
    if ($query->execute()) {
        if ($query->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'İlan favorilerden kaldırıldı']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bu ilan zaten favorilerde değil']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Favorilerden kaldırılırken hata oluştu']);
    }
    
} catch (Exception $e) {
    error_log("Remove Favorite Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
