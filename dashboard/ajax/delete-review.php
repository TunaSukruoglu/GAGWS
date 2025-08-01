<?php
session_start();
header('Content-Type: application/json');

// Kullanıcı kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız']);
    exit;
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

$user_id = $_SESSION['user_id'];
$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;

if ($review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz değerlendirme ID']);
    exit;
}

try {
    // Kullanıcının değerlendirmesini kontrol et ve sil
    $query = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $review_id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Değerlendirme silindi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Değerlendirme bulunamadı veya size ait değil']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
