<?php
// PHP Ayarları Doğrulama
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

echo "<h3>PHP Ayarları Doğrulama</h3>";
echo "<p>PHP.ini dosyası güncellendi. Yeni ayarları kontrol ediyoruz...</p>";

$expected_settings = [
    'post_max_size' => '1G',
    'upload_max_filesize' => '500M', 
    'max_file_uploads' => '50',
    'max_input_vars' => '10000',
    'max_execution_time' => '1800',
    'max_input_time' => '1800',
    'memory_limit' => '1G'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Ayar</th><th>Beklenen</th><th>Mevcut</th><th>Durum</th></tr>";

foreach ($expected_settings as $setting => $expected) {
    $current = ini_get($setting);
    $status = ($current === $expected) ? '✅ DOĞRU' : '❌ YANLIŞ';
    $color = ($current === $expected) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><strong>$setting</strong></td>";
    echo "<td>$expected</td>";
    echo "<td>$current</td>";
    echo "<td style='color: $color;'><strong>$status</strong></td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><p><strong>Test sonucu:</strong></p>";
if (ini_get('post_max_size') === '1G' && ini_get('upload_max_filesize') === '500M') {
    echo "<div style='color: green; font-weight: bold; font-size: 18px;'>🎉 BAŞARILI! PHP ayarları doğru güncellendi.</div>";
    echo "<p>Artık büyük dosyalar ve formlar desteklenmektedir.</p>";
} else {
    echo "<div style='color: red; font-weight: bold; font-size: 18px;'>⚠️ DİKKAT! Bazı ayarlar henüz güncellenmedi.</div>";
    echo "<p>Web sunucusunu yeniden başlatmayı deneyin.</p>";
}

echo "<br><a href='add-property.php'>İlan Ekleme Formunu Test Et</a>";
echo " | <a href='csrf-panel.php'>Ana Panel</a>";
?>
