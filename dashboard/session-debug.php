<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
echo "<!DOCTYPE html><html><head><title>Session Debug</title></head><body>";
echo "<h1>Session ve Güvenlik Debug</h1>";

echo "<h2>1. Session Bilgileri</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session durumu: " . (session_status() == PHP_SESSION_ACTIVE ? 'Aktif' : 'İnaktif') . "<br>";

if (isset($_SESSION)) {
    echo "<h3>Session İçeriği:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "Session mevcut değil<br>";
}

echo "<h2>2. Kullanıcı Bilgileri</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
    echo "✅ User Name: " . ($_SESSION['user_name'] ?? 'Belirtilmemiş') . "<br>";
    echo "✅ User Role: " . ($_SESSION['role'] ?? 'Belirtilmemiş') . "<br>";
    
    if ($_SESSION['role'] === 'admin') {
        echo "✅ Admin yetkileri doğrulandı<br>";
    } else {
        echo "❌ Admin yetkileri eksik<br>";
    }
} else {
    echo "❌ Kullanıcı giriş yapmamış<br>";
    echo "<strong>Çözüm:</strong> <a href='../login.php'>Lütfen giriş yapın</a><br>";
}

echo "<h2>3. Server Bilgileri</h2>";
echo "Request Method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Belirtilmemiş') . "<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Belirtilmemiş') . "<br>";
echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Belirtilmemiş') . "<br>";

echo "<h2>4. Cookie Bilgileri</h2>";
if (!empty($_COOKIE)) {
    echo "<pre>";
    print_r($_COOKIE);
    echo "</pre>";
} else {
    echo "Cookie bulunamadı<br>";
}

echo "<h2>5. Test Linkler</h2>";
echo "<a href='../login.php' style='padding:10px;background:blue;color:white;text-decoration:none;margin:5px;'>Login Sayfası</a><br><br>";
echo "<a href='dashboard-admin.php' style='padding:10px;background:green;color:white;text-decoration:none;margin:5px;'>Dashboard</a><br><br>";
echo "<a href='admin-blog-add-new.php' style='padding:10px;background:orange;color:white;text-decoration:none;margin:5px;'>Blog Sayfası</a><br><br>";

echo "<h2>6. Çözüm Önerileri</h2>";
echo "<div style='background:#f0f0f0;padding:15px;'>";
echo "<h3>Eğer 'Geçersiz istek' hatası alıyorsanız:</h3>";
echo "1. <strong>Logout yapıp yeniden login olun</strong><br>";
echo "2. <strong>Tarayıcı cache'ini temizleyin</strong><br>";
echo "3. <strong>Farklı bir tarayıcı deneyin</strong><br>";
echo "4. <strong>CSRF token sorunu olabilir</strong><br>";
echo "</div>";

echo "</body></html>";
?>
