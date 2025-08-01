<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetki yok']);
    exit;
}

// JSON verilerini al
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID gerekli']);
    exit;
}

try {
    // Bu kullanıcının favori ilanları
    $query = "SELECT p.id as property_id, p.title, p.price, p.type, f.created_at 
              FROM favorites f 
              JOIN properties p ON f.property_id = p.id 
              WHERE f.user_id = ? 
              ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $favorites = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('d.m.Y H:i', strtotime($row['created_at']));
        $row['price'] = number_format($row['price'], 2);
        $favorites[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'favorites' => $favorites,
        'total' => count($favorites)
    ]);
    
} catch (Exception $e) {
    error_log("Get User Favorites Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
}
?>
