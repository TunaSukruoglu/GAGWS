<?php
// PHP Konfigürasyon Bilgileri
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

echo "<h2>PHP Konfigürasyonu</h2>";

// PHP.ini dosyasının konumu
$php_ini_path = php_ini_loaded_file();
echo "<p><strong>PHP.ini dosyası:</strong> " . ($php_ini_path ?: "Bulunamadı") . "</p>";

// Ek konfigürasyon dosyaları
$additional_ini = php_ini_scanned_files();
if ($additional_ini) {
    echo "<p><strong>Ek .ini dosyaları:</strong><br>" . nl2br($additional_ini) . "</p>";
}

// PHP bilgileri
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor') . "</p>";

// Mevcut limitler
echo "<h3>Mevcut Upload Limitleri:</h3>";
echo "<ul>";
echo "<li><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</li>";
echo "<li><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</li>";
echo "<li><strong>max_input_vars:</strong> " . ini_get('max_input_vars') . "</li>";
echo "<li><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</li>";
echo "<li><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "</ul>";

// Değiştirilebilir mi kontrol et
echo "<h3>Ayar Değiştirme Testi:</h3>";
$old_value = ini_get('memory_limit');
$test_result = ini_set('memory_limit', '256M');
if ($test_result !== false) {
    echo "<p style='color: green;'>✅ PHP ayarları çalışma zamanında değiştirilebilir</p>";
    ini_set('memory_limit', $old_value); // Geri döndür
} else {
    echo "<p style='color: red;'>❌ PHP ayarları çalışma zamanında değiştirilemez</p>";
}

// phpinfo linkini ekle
echo "<p><a href='?info=1' target='_blank'>PHP Info Sayfasını Aç</a></p>";

if (isset($_GET['info'])) {
    phpinfo();
    exit;
}
?>
