<?php
session_start();
include '../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

echo "<h2>✅ Blog Dosyası Başarıyla Oluşturuldu</h2>";

// Blog ID 8'in dosya ismini güncelle
$blog_id = 8;
$filename = "blog8.php";

$update_query = $conn->prepare("UPDATE blogs SET blog_file = ? WHERE id = ?");
$update_query->bind_param("si", $filename, $blog_id);

if ($update_query->execute()) {
    echo "<div style='background: #00b894; color: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 İşlem Tamamlandı!</h3>";
    echo "<p>✅ Blog dosyası oluşturuldu: <strong>{$filename}</strong></p>";
    echo "<p>✅ Veritabanı güncellendi</p>";
    echo "<p>✅ Blog artık erişilebilir durumda</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='../{$filename}' target='_blank' style='display: inline-block; padding: 15px 30px; background: #0984e3; color: white; text-decoration: none; border-radius: 5px; font-size: 18px; margin: 10px;'>📄 Blog Yazısını Aç</a>";
    echo "<a href='../blog.php' target='_blank' style='display: inline-block; padding: 15px 30px; background: #6c5ce7; color: white; text-decoration: none; border-radius: 5px; font-size: 18px; margin: 10px;'>📝 Ana Blog Sayfası</a>";
    echo "</div>";
    
    // Test linkleri
    echo "<h3>🔗 Test Linkleri:</h3>";
    echo "<ul>";
    echo "<li><a href='../{$filename}' target='_blank'>Direct Link: {$filename}</a></li>";
    echo "<li><a href='../blog.php' target='_blank'>Blog Ana Sayfa</a></li>";
    echo "<li><a href='admin-blog.php'>Admin Blog Yönetimi</a></li>";
    echo "</ul>";
    
} else {
    echo "<div style='background: #d63031; color: white; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Veritabanı Güncelleme Hatası</h3>";
    echo "<p>Dosya oluşturuldu ama veritabanı güncellenemedi.</p>";
    echo "</div>";
}

// Blog detaylarını göster
$blog_query = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
$blog_query->bind_param("i", $blog_id);
$blog_query->execute();
$blog = $blog_query->get_result()->fetch_assoc();

if ($blog) {
    echo "<h3>📋 Güncel Blog Bilgileri:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr><th style='padding: 10px; background: #ecf0f1;'>Alan</th><th style='padding: 10px; background: #ecf0f1;'>Değer</th></tr>";
    echo "<tr><td style='padding: 8px;'>ID</td><td style='padding: 8px;'>{$blog['id']}</td></tr>";
    echo "<tr><td style='padding: 8px;'>Başlık</td><td style='padding: 8px;'>{$blog['title']}</td></tr>";
    echo "<tr><td style='padding: 8px;'>Durum</td><td style='padding: 8px;'><strong style='color: green;'>{$blog['status']}</strong></td></tr>";
    echo "<tr><td style='padding: 8px;'>Blog Dosyası</td><td style='padding: 8px;'><strong style='color: blue;'>{$blog['blog_file']}</strong></td></tr>";
    echo "<tr><td style='padding: 8px;'>Yayın Tarihi</td><td style='padding: 8px;'>{$blog['publish_date']}</td></tr>";
    echo "</table>";
}

echo "<hr>";
echo "<div style='text-align: center;'>";
echo "<a href='admin-blog.php' style='padding: 10px 20px; background: #74b9ff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Admin Panel</a>";
echo "<a href='repair-blog-files.php' style='padding: 10px 20px; background: #00b894; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Dosya Onarım</a>";
echo "</div>";
?>
