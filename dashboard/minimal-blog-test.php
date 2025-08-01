<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Basit admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Admin girişi gerekli. <a href='../login.php'>Giriş yap</a>";
    exit();
}

echo "<!DOCTYPE html>";
echo "<html lang='tr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>Minimal Blog Test</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body class='bg-light'>";

echo "<div class='container mt-4'>";
echo "<h1>✅ Minimal Blog Sayfası Çalışıyor!</h1>";

echo "<div class='alert alert-success'>";
echo "Bu sayfa çalışıyorsa, sorun admin-blog-add-new.php dosyasında.";
echo "</div>";

echo "<h2>Form Testi</h2>";
echo "<form method='POST'>";
echo "<div class='mb-3'>";
echo "<label class='form-label'>Test Başlık:</label>";
echo "<input type='text' class='form-control' name='test_title' required>";
echo "</div>";
echo "<button type='submit' name='test_submit' class='btn btn-primary'>Test Gönder</button>";
echo "</form>";

if (isset($_POST['test_submit'])) {
    echo "<div class='alert alert-info mt-3'>";
    echo "Form çalışıyor! Girilen başlık: " . htmlspecialchars($_POST['test_title']);
    echo "</div>";
}

echo "<hr>";
echo "<h2>Linkler</h2>";
echo "<a href='admin-blog-add-new.php' class='btn btn-warning me-2'>Orijinal Blog Sayfası</a>";
echo "<a href='dashboard-admin.php' class='btn btn-secondary me-2'>Dashboard</a>";
echo "<a href='test-blog-admin.php' class='btn btn-info'>Detaylı Test</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
