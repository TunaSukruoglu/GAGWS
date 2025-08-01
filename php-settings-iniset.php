<?php
// PHP Upload ayarlarını kod içinde ayarla
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '8M');
ini_set('max_file_uploads', '10');
ini_set('max_execution_time', '60');
ini_set('memory_limit', '128M');

echo "<h2>🔧 PHP Ayarları (ini_set ile)</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th style='padding: 10px;'>Ayar</th><th>Eski Değer</th><th>Yeni Değer</th><th>Durum</th></tr>";

$settings = [
    'upload_max_filesize' => ['5M', ini_get('upload_max_filesize')],
    'post_max_size' => ['8M', ini_get('post_max_size')],
    'max_file_uploads' => ['10', ini_get('max_file_uploads')],
    'max_execution_time' => ['60', ini_get('max_execution_time')],
    'memory_limit' => ['128M', ini_get('memory_limit')]
];

foreach ($settings as $key => $values) {
    $target = $values[0];
    $current = $values[1];
    
    $status = "✅ OK";
    $color = "green";
    
    if ($key === 'upload_max_filesize' && convertToBytes($current) < convertToBytes('2M')) {
        $status = "⚠️ Düşük";
        $color = "orange";
    }
    
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$key</strong></td>";
    echo "<td style='padding: 10px;'>$current</td>";
    echo "<td style='padding: 10px;'>$target</td>";
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

echo "<h3>📝 Not:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p>Bazı hosting sağlayıcıları .htaccess ile PHP ayarlarını değiştirmeye izin vermez.</p>";
echo "<p>Bu durumda <code>ini_set()</code> fonksiyonu ile kod içinde ayarları değiştiriyoruz.</p>";
echo "</div>";

echo "<p><a href='test-cloudflare-images.php'>Test sayfasına dön</a></p>";
echo "<p><a href='dashboard/add-property.php'>Dashboard'a git</a></p>";
?>
