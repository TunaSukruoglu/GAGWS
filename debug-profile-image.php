<?php
session_start();
include 'db.php';

echo "🖼️ PROFİL RESMİ DEBUG\n";
echo "=====================\n\n";

// Kullanıcı session kontrolü
if (!isset($_SESSION['user_id'])) {
    echo "❌ Session yok! Giriş yapmalısınız.\n";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "👤 User ID: $user_id\n";

// Kullanıcı bilgilerini çek
$stmt = $conn->prepare("SELECT id, name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "❌ Kullanıcı bulunamadı!\n";
    exit;
}

echo "📋 Kullanıcı Bilgileri:\n";
echo "─────────────────────\n";
echo "ID: {$user['id']}\n";
echo "İsim: {$user['name']}\n";
echo "Email: {$user['email']}\n";
echo "Profil Resmi DB: " . ($user['profile_image'] ?: '[BOŞ]') . "\n";

// Dosya kontrolü
if (!empty($user['profile_image'])) {
    $file_path = $user['profile_image'];
    $full_path = __DIR__ . '/' . $file_path;
    
    echo "\n📁 Dosya Kontrolü:\n";
    echo "─────────────────\n";
    echo "DB'deki yol: $file_path\n";
    echo "Tam yol: $full_path\n";
    echo "Dosya var mı: " . (file_exists($full_path) ? "✅ EVET" : "❌ HAYIR") . "\n";
    
    if (file_exists($full_path)) {
        echo "Dosya boyutu: " . formatBytes(filesize($full_path)) . "\n";
        echo "Dosya tipi: " . mime_content_type($full_path) . "\n";
        echo "İzinler: " . substr(sprintf('%o', fileperms($full_path)), -4) . "\n";
    }
} else {
    echo "\n📁 Database'de profil resmi yolu yok.\n";
}

// Upload klasörü kontrolü
echo "\n📂 Upload Klasörü Kontrolü:\n";
echo "───────────────────────────\n";
$upload_dir = __DIR__ . '/uploads/profiles/';
echo "Upload klasörü: $upload_dir\n";
echo "Klasör var mı: " . (is_dir($upload_dir) ? "✅ EVET" : "❌ HAYIR") . "\n";
echo "Yazılabilir mi: " . (is_writable($upload_dir) ? "✅ EVET" : "❌ HAYIR") . "\n";

if (is_dir($upload_dir)) {
    $files = scandir($upload_dir);
    $files = array_diff($files, ['.', '..']);
    echo "Klasördeki dosyalar: " . (count($files) > 0 ? implode(', ', $files) : '[BOŞ]') . "\n";
    echo "İzinler: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "\n";
}

// PHP upload ayarları
echo "\n⚙️ PHP Upload Ayarları:\n";
echo "─────────────────────\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

echo "\n🔧 Çözüm Önerileri:\n";
echo "─────────────────\n";
echo "1. Profil resmi yüklerken console'da hata var mı kontrol edin\n";
echo "2. Network sekmesinden POST isteğinin başarılı olup olmadığını kontrol edin\n";
echo "3. Uploads klasörü izinlerini kontrol edin (777 olmalı)\n";
echo "4. Resim yükledikten sonra bu script'i tekrar çalıştırın\n";
?>
