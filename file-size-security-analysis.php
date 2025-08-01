<?php
echo "<h2>📊 Dosya Boyutu Risk Analizi</h2>";

echo "<h3>🎯 Önerilen Limitler:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th style='padding: 10px;'>Dosya Tipi</th><th>Önerilen Limit</th><th>Neden</th></tr>";

$limits = [
    'Profil Fotoğrafı' => ['500KB', 'Hızlı yükleme, az yer kaplama'],
    'Emlak Fotoğrafı' => ['2-5MB', 'Kaliteli görüntü, makul boyut'],
    'Dokuman/PDF' => ['10MB', 'Detaylı belgeler için yeterli'],
    'Video' => ['50-100MB', 'Kısa tanıtım videoları için']
];

foreach ($limits as $type => $info) {
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$type</strong></td>";
    echo "<td style='padding: 10px; color: green;'><strong>{$info[0]}</strong></td>";
    echo "<td style='padding: 10px;'>{$info[1]}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>⚠️ Risk Senaryoları:</h3>";
echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>❌ Sınırsız Upload Durumunda:</h4>";
echo "<ul>";
echo "<li><strong>100MB resim yüklenirse:</strong> Sunucu 30+ saniye donabilir</li>";
echo "<li><strong>1GB dosya yüklenirse:</strong> Disk alanı ve RAM tükenir</li>";
echo "<li><strong>10 kişi aynı anda büyük dosya:</strong> Site çökebilir</li>";
echo "<li><strong>Saldırgan spam upload:</strong> Hosting askıya alınabilir</li>";
echo "</ul>";
echo "</div>";

echo "<h3>✅ Güvenli Ayarlar:</h3>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📱 Resim Optimizasyonu:</h4>";
echo "<ul>";
echo "<li><strong>Modern format:</strong> WebP, AVIF (50% daha küçük)</li>";
echo "<li><strong>Sıkıştırma:</strong> Kalite 80-85% yeterli</li>";
echo "<li><strong>Boyut kontrolü:</strong> Max 1920x1080 çözünürlük</li>";
echo "<li><strong>Progressive JPEG:</strong> Hızlı yükleme</li>";
echo "</ul>";
echo "</div>";

// Mevcut ayarları göster
echo "<h3>🔧 Şu Anki Ayarlarınız:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th style='padding: 10px;'>Ayar</th><th>Değer</th><th>Durum</th></tr>";

$settings = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time') . ' saniye',
    'memory_limit' => ini_get('memory_limit')
];

foreach ($settings as $key => $value) {
    $status = "✅ Güvenli";
    $color = "green";
    
    if ($key === 'upload_max_filesize') {
        $bytes = convertToBytes($value);
        if ($bytes > 10*1024*1024) { // 10MB'dan büyükse
            $status = "⚠️ Yüksek";
            $color = "orange";
        }
        if ($bytes > 50*1024*1024) { // 50MB'dan büyükse
            $status = "❌ Riskli";
            $color = "red";
        }
    }
    
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$key</strong></td>";
    echo "<td style='padding: 10px;'>$value</td>";
    echo "<td style='padding: 10px; color: $color;'>$status</td>";
    echo "</tr>";
}
echo "</table>";

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

echo "<h3>💰 Maliyet Hesabı:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📈 Cloudflare Images Maliyeti:</h4>";
echo "<ul>";
echo "<li><strong>2MB resim x 1000 adet:</strong> ~$5-7/ay</li>";
echo "<li><strong>10MB resim x 1000 adet:</strong> ~$25-35/ay</li>";
echo "<li><strong>50MB resim x 1000 adet:</strong> ~$125-175/ay</li>";
echo "</ul>";
echo "<p><strong>💡 Sonuç:</strong> Küçük dosyalar = düşük maliyet + hızlı site!</p>";
echo "</div>";

echo "<h3>🛡️ Önerilen Güvenlik Ayarları:</h3>";
echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<pre style='background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px;'>";
echo "# .htaccess - Güvenli Upload Ayarları
php_value upload_max_filesize 5M     # Resimler için yeterli
php_value post_max_size 8M           # Birden fazla dosya için
php_value max_file_uploads 10        # Spam koruması
php_value max_execution_time 60      # Timeout koruması
php_value memory_limit 128M          # RAM koruması";
echo "</pre>";
echo "</div>";
?>
