<?php
session_start();
header('Content-Type: application/json');

// Kullanıcı kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız']);
    exit;
}

require_once '../db.php';

$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz değerlendirme ID']);
    exit;
}

try {
    // Kullanıcının değerlendirmesini getir
    $query = "SELECT r.*, p.title as property_title 
              FROM reviews r 
              LEFT JOIN properties p ON r.property_id = p.id 
              WHERE r.id = ? AND r.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $review = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $review]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Değerlendirme bulunamadı']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
