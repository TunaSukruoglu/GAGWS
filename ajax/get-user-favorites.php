<?php
session_start();
include __DIR__ . '/../db.php';

// JSON header
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Kullanıcının favori ilan ID'lerini al
    $query = $conn->prepare("SELECT property_id FROM favorites WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    
    $favorites = [];
    while ($row = $result->fetch_assoc()) {
        $favorites[] = (int)$row['property_id'];
    }
    
    echo json_encode(['success' => true, 'favorites' => $favorites]);
    
} catch (Exception $e) {
    error_log("Get User Favorites Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
