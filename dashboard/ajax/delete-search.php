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
$search_id = isset($_POST['search_id']) ? (int)$_POST['search_id'] : 0;

if ($search_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz arama ID']);
    exit;
}

try {
    // Kullanıcının aramasını kontrol et ve sil
    $query = "DELETE FROM saved_searches WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $search_id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Kayıtlı arama silindi']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Arama bulunamadı veya size ait değil']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
