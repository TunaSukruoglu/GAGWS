<?php
include '../db.php';

// Kullanıcı bilgilerini al
session_start();
$user_id = $_SESSION['user_id'] ?? 13; // Test için root kullanıcısı

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo "<!DOCTYPE html>";
echo "<html><head><title>Profile Image Debug</title></head><body>";
echo "<h1>🔧 Profile Image Debug</h1>";

if ($user) {
    $db_image_path = $user['profile_image'];
    $relative_path = '../' . $db_image_path;
    $absolute_path = realpath(__DIR__ . '/../' . $db_image_path);
    
    echo "<h2>👤 Kullanıcı: " . htmlspecialchars($user['name']) . "</h2>";
    echo "<h3>📊 Debug Bilgileri:</h3>";
    echo "<ul>";
    echo "<li><strong>DB'deki yol:</strong> " . htmlspecialchars($db_image_path ?: '[BOŞ]') . "</li>";
    echo "<li><strong>Relative yol:</strong> " . htmlspecialchars($relative_path) . "</li>";
    echo "<li><strong>Absolute yol:</strong> " . htmlspecialchars($absolute_path ?: '[YOK]') . "</li>";
    echo "<li><strong>Current dir:</strong> " . htmlspecialchars(__DIR__) . "</li>";
    echo "<li><strong>File exists (relative):</strong> " . (file_exists($relative_path) ? '✅ EVET' : '❌ HAYIR') . "</li>";
    echo "<li><strong>File exists (absolute):</strong> " . (file_exists($absolute_path) ? '✅ EVET' : '❌ HAYIR') . "</li>";
    echo "</ul>";
    
    if (!empty($db_image_path)) {
        echo "<h3>🖼️ Resim Test:</h3>";
        echo "<p>Web yolu test: <img src='../" . htmlspecialchars($db_image_path) . "' alt='Test' style='width:100px;height:100px;border:1px solid red;'></p>";
        
        // Doğrudan yol testi
        $direct_path = str_replace('../', '', $relative_path);
        echo "<p>Doğrudan yol test: <img src='" . htmlspecialchars($direct_path) . "' alt='Test' style='width:100px;height:100px;border:1px solid blue;'></p>";
        
        // Root'tan yol testi
        $root_path = '/' . $db_image_path;
        echo "<p>Root yol test: <img src='" . htmlspecialchars($root_path) . "' alt='Test' style='width:100px;height:100px;border:1px solid green;'></p>";
    }
} else {
    echo "<p>❌ Kullanıcı bulunamadı!</p>";
}

echo "</body></html>";
?>
