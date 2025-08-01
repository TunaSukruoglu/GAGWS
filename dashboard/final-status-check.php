<?php
// Apache ve PHP Durum Kontrolü
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

echo "<h2>🔧 Apache ve PHP Durum Kontrolü</h2>";

// PHP ayarları
echo "<h3>📊 PHP Ayarları (Güncellenmiş)</h3>";
$settings = [
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'max_input_vars' => ini_get('max_input_vars'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit')
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
foreach ($settings as $key => $value) {
    $color = 'green';
    if ($key == 'post_max_size' && $value != '1G') $color = 'red';
    if ($key == 'upload_max_filesize' && $value != '500M') $color = 'red';
    if ($key == 'max_file_uploads' && $value < 50) $color = 'red';
    
    echo "<tr>";
    echo "<td><strong>$key</strong></td>";
    echo "<td style='color: $color;'><strong>$value</strong></td>";
    echo "</tr>";
}
echo "</table>";

// Server bilgisi
echo "<h3>🌐 Server Bilgisi</h3>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor') . "</p>";
echo "<p><strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Bilinmiyor') . "</p>";
echo "<p><strong>Server Port:</strong> " . ($_SERVER['SERVER_PORT'] ?? 'Bilinmiyor') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Bilinmiyor') . "</p>";

// .htaccess kontrolü
echo "<h3>📄 .htaccess Kontrolleri</h3>";
$htaccess_files = [
    'Ana dizin' => '../.htaccess',
    'Dashboard' => '.htaccess'
];

foreach ($htaccess_files as $name => $file) {
    if (file_exists($file)) {
        echo "<p>✅ <strong>$name .htaccess:</strong> Mevcut</p>";
        $content = file_get_contents($file);
        if (strpos($content, 'post_max_size') !== false) {
            echo "<p style='color: green;'>✅ POST ayarları .htaccess'te mevcut</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ POST ayarları .htaccess'te bulunamadı</p>";
        }
    } else {
        echo "<p>❌ <strong>$name .htaccess:</strong> Bulunamadı</p>";
    }
}

// Test formu
echo "<h3>🧪 Hızlı Test</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0;'>";
    echo "<h4>✅ POST Test Başarılı!</h4>";
    echo "<p>POST data count: " . count($_POST) . "</p>";
    echo "<p>Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'undefined') . " bytes</p>";
    if (!empty($_FILES)) {
        echo "<p>Files uploaded: " . count($_FILES) . "</p>";
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                echo "<p>$key: " . count(array_filter($file['name'])) . " dosya</p>";
            } else if (!empty($file['name'])) {
                $size_mb = round($file['size'] / 1024 / 1024, 2);
                echo "<p>$key: {$file['name']} ({$size_mb} MB)</p>";
            }
        }
    }
    echo "</div>";
}

echo "<form method='POST' enctype='multipart/form-data'>";
echo "<p><input type='text' name='test_field' value='Form boyut testi' style='width: 300px;'></p>";
echo "<p><input type='file' name='test_files[]' multiple></p>";
echo "<p><button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>🚀 Hızlı Test</button></p>";
echo "</form>";

echo "<hr>";
echo "<h3>🎯 Sonuç</h3>";

if (ini_get('post_max_size') === '1G' && ini_get('upload_max_filesize') === '500M') {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: green;'>🎉 TÜM AYARLAR BAŞARILI!</h4>";
    echo "<p>✅ PHP ayarları doğru güncellendi</p>";
    echo "<p>✅ Apache çalışıyor ve ayarları okuyor</p>";
    echo "<p>✅ Büyük dosya ve form desteği aktif</p>";
    echo "<p><strong>Artık add-property.php formunu sorunsuz kullanabilirsiniz!</strong></p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4 style='color: red;'>⚠️ Bazı ayarlar henüz aktif değil</h4>";
    echo "<p>Apache'yi tamamen yeniden başlatmayı deneyin.</p>";
    echo "</div>";
}

echo "<br><p>";
echo "<a href='add-property.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏠 İlan Ekleme Formu</a> ";
echo "<a href='php-upload-test.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📤 Upload Test</a> ";
echo "<a href='csrf-panel.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🏠 Ana Panel</a>";
echo "</p>";
?>
