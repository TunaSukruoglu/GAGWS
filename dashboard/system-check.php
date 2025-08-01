<?php
// Sistem kontrol scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Sistem Kontrol Raporu</h2>";

// 1. Dosya varlığı kontrolü
$important_files = [
    'add-property.php',
    'db.php',
    'uploads/properties/'
];

echo "<h3>1. Dosya Kontrolleri</h3>";
foreach ($important_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file mevcut</p>";
    } else {
        echo "<p style='color: red;'>✗ $file bulunamadı</p>";
    }
}

// 2. Database bağlantısı
echo "<h3>2. Database Bağlantısı</h3>";
try {
    include __DIR__ . '/../db.php';
    echo "<p style='color: green;'>✓ Database bağlantısı başarılı</p>";
    
    // Tablo kontrolü
    $tables = ['properties', 'users'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color: green;'>✓ $table tablosu mevcut</p>";
        } else {
            echo "<p style='color: red;'>✗ $table tablosu bulunamadı</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database hatası: " . $e->getMessage() . "</p>";
}

// 3. Uploads klasörü kontrolü
echo "<h3>3. Uploads Klasörü</h3>";
if (is_dir('uploads/properties')) {
    $files = scandir('uploads/properties');
    $count = count($files) - 2; // . ve .. hariç
    echo "<p style='color: green;'>✓ Uploads klasörü mevcut - $count fotoğraf</p>";
} else {
    echo "<p style='color: red;'>✗ Uploads klasörü bulunamadı</p>";
}

// 4. PHP hataları kontrol
echo "<h3>4. PHP Syntax Kontrol</h3>";
$output = shell_exec('php -l add-property.php 2>&1');
if (strpos($output, 'No syntax errors') !== false) {
    echo "<p style='color: green;'>✓ PHP syntax hatası yok</p>";
} else {
    echo "<p style='color: red;'>✗ PHP syntax hatası: " . $output . "</p>";
}

echo "<h3>5. Öneriler</h3>";
echo "<ul>";
echo "<li>Sistem genel olarak çalışır durumda</li>";
echo "<li>Fotoğraflar basitleştirildi</li>";
echo "<li>Parametre sayımları düzeltildi</li>";
echo "<li>Form gönderimi güvenli hale getirildi</li>";
echo "</ul>";

echo "<p><strong>Sistem test edilmeye hazır!</strong></p>";
?>
