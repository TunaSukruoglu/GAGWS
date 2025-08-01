<?php
// BASIT TEST DOSYASI - add-property.php'deki hataları bulmak için
// OUTPUT BUFFER BAŞLAT - Header sorununu önlemek için
ob_start();

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

echo "<h1>DEBUG TEST SAYFASI</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Şu anki zaman: " . date('Y-m-d H:i:s') . "</p>";

// 1. Database bağlantısını test et
echo "<h2>1. Database Bağlantı Testi</h2>";
try {
    include '../db.php';
    if (isset($conn) && $conn) {
        echo "<span style='color: green'>✓ Database bağlantısı başarılı</span><br>";
        
        // Test sorgusu
        $test = $conn->query("SELECT 1 as test");
        if ($test) {
            echo "<span style='color: green'>✓ Database sorgusu çalışıyor</span><br>";
        } else {
            echo "<span style='color: red'>✗ Database sorgusu başarısız: " . $conn->error . "</span><br>";
        }
    } else {
        echo "<span style='color: red'>✗ Database bağlantısı yok</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red'>✗ Database hatası: " . $e->getMessage() . "</span><br>";
}

// 2. Dosya ve klasör kontrolü
echo "<h2>2. Dosya/Klasör Kontrolü</h2>";
echo "Çalışma dizini: " . __DIR__ . "<br>";
echo "add-property.php dosyası var mı: " . (file_exists(__DIR__ . '/add-property.php') ? '✓ Evet' : '✗ Hayır') . "<br>";
echo "uploads klasörü var mı: " . (is_dir(__DIR__ . '/uploads') ? '✓ Evet' : '✗ Hayır') . "<br>";

// 3. Session kontrolü
echo "<h2>3. Session Kontrolü</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

// 4. Include dosyalarını kontrol et
echo "<h2>4. Include Dosya Kontrolü</h2>";
$includes = [
    '../db.php',
    '../includes/header-admin.php',
    '../includes/sidebar-admin.php',
    'includes/csrf-manager.php'
];

foreach ($includes as $file) {
    $fullPath = realpath(__DIR__ . '/' . $file);
    if ($fullPath && file_exists($fullPath)) {
        echo "<span style='color: green'>✓ " . $file . " (gerçek yol: " . $fullPath . ")</span><br>";
    } else {
        echo "<span style='color: red'>✗ " . $file . " bulunamadı</span><br>";
    }
}

// 5. PHP Hata logunu kontrol et
echo "<h2>5. PHP Hata Logu</h2>";
$error_log_file = __DIR__ . '/debug.log';
if (file_exists($error_log_file)) {
    echo "<h3>Son 10 hata:</h3>";
    $errors = file($error_log_file);
    $recent_errors = array_slice($errors, -10);
    foreach ($recent_errors as $error) {
        echo "<div style='background: #ffe6e6; padding: 5px; margin: 2px; border-radius: 3px;'>" . htmlspecialchars($error) . "</div>";
    }
} else {
    echo "Henüz hata logu oluşmamış.<br>";
}

// 6. Memory ve performance
echo "<h2>6. Sistem Bilgileri</h2>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Memory kullanımı: " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";
echo "Max execution time: " . ini_get('max_execution_time') . " saniye<br>";

echo "<hr>";
echo "<p><strong>Bu test başarılı olduysa, şimdi add-property.php'yi ziyaret edin:</strong></p>";
echo "<a href='add-property.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>add-property.php'yi Test Et</a>";
?>
