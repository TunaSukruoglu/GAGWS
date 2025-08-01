<?php
// Debug log dosyasını kontrol et
$debug_file = 'debug.log';

echo "<h2>Debug Log İçeriği</h2>";

if (file_exists($debug_file)) {
    $lines = file($debug_file);
    $recent_lines = array_slice($lines, -50); // Son 50 satır
    
    echo "<h3>Son 50 satır:</h3>";
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
    foreach ($recent_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
    
    // Usage status ile ilgili satırları filtrele
    echo "<h3>Usage Status ile ilgili loglar:</h3>";
    echo "<pre style='background: #fff3cd; padding: 15px; border-radius: 5px; font-size: 12px;'>";
    foreach ($lines as $line) {
        if (stripos($line, 'usage_status') !== false || stripos($line, 'Usage Status') !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
    
} else {
    echo "<p>Debug log dosyası bulunamadı: $debug_file</p>";
}
?>
