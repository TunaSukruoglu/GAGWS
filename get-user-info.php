<?php
// Kullanıcı Bilgileri API - Ultra Hızlı
session_start();
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300'); // 5 dakika cache

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

try {
    include 'db.php';
    
    $stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'timestamp' => time()
        ]);
    } else {
        echo json_encode(['name' => 'Kullanıcı', 'role' => 'user']);
    }
    
} catch (Exception $e) {
    echo json_encode(['name' => 'Kullanıcı', 'error' => 'db_error']);
}
?>
