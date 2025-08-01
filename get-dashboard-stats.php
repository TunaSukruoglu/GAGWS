<?php
// Dashboard İstatistikleri API - Ultra Hızlı
session_start();
header('Content-Type: application/json');
header('Cache-Control: public, max-age=60'); // 1 dakika cache

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

// Cache kontrolü
$cache_file = 'cache/dashboard_stats.json';
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 60) {
    echo file_get_contents($cache_file);
    exit;
}

try {
    include 'db.php';
    
    // Tek sorgu ile tüm stats
    $sql = "SELECT 
        (SELECT COUNT(*) FROM properties) as properties,
        (SELECT COUNT(*) FROM users) as users,
        (SELECT COUNT(*) FROM agencies) as agencies,
        (SELECT COUNT(*) FROM contact_messages) as messages";
    
    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stats) {
        $stats = ['properties' => 0, 'users' => 0, 'agencies' => 0, 'messages' => 0];
    }
    
    $result = json_encode($stats);
    
    // Cache kaydet (güvenli)
    if (is_writable(dirname($cache_file))) {
        file_put_contents($cache_file, $result, LOCK_EX);
    }
    
    echo $result;
    
} catch (Exception $e) {
    echo json_encode([
        'properties' => '?',
        'users' => '?', 
        'agencies' => '?',
        'messages' => '?',
        'error' => 'db_error'
    ]);
}
?>
