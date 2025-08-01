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
$search_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($search_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz arama ID']);
    exit;
}

try {
    // Kullanıcının aramasını getir
    $query = "SELECT * FROM saved_searches WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $search_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $search = $result->fetch_assoc();
        $search['search_criteria'] = json_decode($search['search_criteria'], true);
        
        echo json_encode(['success' => true, 'data' => $search]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Arama bulunamadı']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu']);
}
?>
