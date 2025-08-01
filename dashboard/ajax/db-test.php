<?php
header('Content-Type: application/json');
include __DIR__ . '/../../db.php';

try {
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'user_count' => $row['count'],
        'time' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
