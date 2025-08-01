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
$property_id = $input['property_id'] ?? 0;

if (!$property_id) {
    echo json_encode(['success' => false, 'message' => 'Property ID gerekli']);
    exit;
}

try {
    // Bu property'yi kim favoriledi?
    $query = "SELECT u.id, u.name, u.email, f.created_at 
              FROM favorites f 
              JOIN users u ON f.user_id = u.id 
              WHERE f.property_id = ? 
              ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = date('d.m.Y H:i', strtotime($row['created_at']));
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'users' => $users,
        'total' => count($users)
    ]);
    
} catch (Exception $e) {
    error_log("Get Property Favorites Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
}
?>
