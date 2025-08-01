<?php
echo "<h2>🔧 PHP Upload Ayarları Kontrolü</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th style='padding: 10px; background: #f0f0f0;'>Ayar</th><th style='padding: 10px; background: #f0f0f0;'>Değer</th><th style='padding: 10px; background: #f0f0f0;'>Durum</th></tr>";

$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads') ? 'ON' : 'OFF'
];

foreach ($settings as $key => $value) {
    $status = "✅ OK";
    $color = "green";
    
    // Boyut kontrolü
    if ($key === 'upload_max_filesize') {
        $bytes = $this->convertToBytes($value);
        if ($bytes < 5*1024*1024) { // 5MB'dan küçükse
            $status = "⚠️ KÜÇÜK";
            $color = "orange";
        }
    }
    
    if ($key === 'post_max_size') {
        $bytes = $this->convertToBytes($value);
        if ($bytes < 10*1024*1024) { // 10MB'dan küçükse
            $status = "⚠️ KÜÇÜK";
            $color = "orange";
        }
    }
    
    if ($key === 'file_uploads' && $value === 'OFF') {
        $status = "❌ KAPALI";
        $color = "red";
    }
    
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$key</strong></td>";
    echo "<td style='padding: 10px;'>$value</td>";
    echo "<td style='padding: 10px; color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Upload hata kodları
echo "<h3>📋 PHP Upload Hata Kodları:</h3>";
echo "<ul>";
echo "<li><strong>0:</strong> ✅ Başarılı</li>";
echo "<li><strong>1:</strong> ❌ Dosya upload_max_filesize'den büyük</li>";
echo "<li><strong>2:</strong> ❌ Dosya MAX_FILE_SIZE'den büyük</li>";
echo "<li><strong>3:</strong> ⚠️ Dosya kısmen yüklendi</li>";
echo "<li><strong>4:</strong> ❌ Hiç dosya yüklenmedi</li>";
echo "<li><strong>6:</strong> ❌ Temporary folder bulunamadı</li>";
echo "<li><strong>7:</strong> ❌ Disk yazma hatası</li>";
echo "<li><strong>8:</strong> ❌ Extension upload'ı durdurdu</li>";
echo "</ul>";

echo "<h3>🔧 Çözüm Önerileri:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 5px solid #ffc107;'>";
echo "<h4>Hata 1 için çözümler:</h4>";
echo "<ol>";
echo "<li><strong>.htaccess ekle:</strong> php_value upload_max_filesize 10M</li>";
echo "<li><strong>Küçük resim test et:</strong> 500KB altı resim dene</li>";
echo "<li><strong>Resim sıkıştır:</strong> Online araçlarla boyutu küçült</li>";
echo "</ol>";
echo "</div>";

// Helper function
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int)$value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

echo "<p><a href='test-cloudflare-images.php'>Test sayfasına dön</a></p>";
?>
