<?php
// 🚀 MINIMAL ACTIVITY LOADER
session_start();
include __DIR__ . '/../../db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $html = '';
    
    // Son 3 kullanıcı
    $users = $conn->query("SELECT name, created_at FROM users ORDER BY created_at DESC LIMIT 3");
    if ($users && $users->num_rows > 0) {
        $html .= '<div style="margin-bottom:15px"><strong>Son Kullanıcılar:</strong><ul style="margin:5px 0;padding-left:20px">';
        while ($user = $users->fetch_assoc()) {
            $html .= '<li>' . htmlspecialchars($user['name']) . ' - ' . date('d.m H:i', strtotime($user['created_at'])) . '</li>';
        }
        $html .= '</ul></div>';
    }
    
    // Son 3 emlak
    $properties = $conn->query("SELECT title, created_at FROM properties ORDER BY created_at DESC LIMIT 3");
    if ($properties && $properties->num_rows > 0) {
        $html .= '<div><strong>Son İlanlar:</strong><ul style="margin:5px 0;padding-left:20px">';
        while ($prop = $properties->fetch_assoc()) {
            $html .= '<li>' . htmlspecialchars(substr($prop['title'], 0, 40)) . '... - ' . date('d.m H:i', strtotime($prop['created_at'])) . '</li>';
        }
        $html .= '</ul></div>';
    }
    
    if (empty($html)) {
        $html = '<p style="color:#666;text-align:center">Henüz aktivite yok</p>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Veri yüklenemedi']);
}
?>
