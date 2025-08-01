<?php
// HATA RAPORU VE ÇÖZMEMELERİ
echo "<h1>🔍 ADD-PROPERTY.PHP HATA TESPİT VE ÇÖZÜM REHBERİ</h1>";

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>📋 Yapılan İyileştirmeler</h2>";
echo "<ul>";
echo "<li>✅ <strong>Gelişmiş hata yakalama sistemi</strong> - Fatal error ve exception handler eklendi</li>";
echo "<li>✅ <strong>Session kontrolü</strong> - Hataları yakalar ve loglar</li>";
echo "<li>✅ <strong>Database bağlantı kontrolleri</strong> - Test sorgusu ile doğrulama</li>";
echo "<li>✅ <strong>Dosya varlık kontrolleri</strong> - CSS ve include dosyaları için</li>";
echo "<li>✅ <strong>User authentication debugging</strong> - Her adımda log ve kontrol</li>";
echo "<li>✅ <strong>CSRF token güvenlik</strong> - Alternatif class eklendi</li>";
echo "<li>✅ <strong>Upload klasörü kontrolleri</strong> - Otomatik oluşturma ve yetki kontrolü</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #fff3e0; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>🛠️ Debug Dosyaları</h2>";
echo "<p>Hatayı tespit etmek için bu dosyaları sırasıyla test edin:</p>";
echo "<ol>";
echo "<li><a href='minimal-test.php' style='color: #1976d2;'>minimal-test.php</a> - En basit PHP/Database testi</li>";
echo "<li><a href='debug-test.php' style='color: #1976d2;'>debug-test.php</a> - Kapsamlı sistem analizi</li>";
echo "<li><a href='test-steps.php' style='color: #1976d2;'>test-steps.php</a> - Adım adım kontrol</li>";
echo "<li><a href='add-property.php' style='color: #1976d2;'>add-property.php</a> - Ana dosya testi</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>🔧 Olası Hata Sebepleri ve Çözümleri</h2>";

echo "<h3>1. Database Bağlantı Sorunu</h3>";
echo "<p><strong>Belirti:</strong> 'Database hatası' mesajı<br>";
echo "<strong>Çözüm:</strong> ../db.php dosyasını kontrol edin, veritabanı bilgilerini doğrulayın</p>";

echo "<h3>2. Session Sorunu</h3>";
echo "<p><strong>Belirti:</strong> 'Session başlatılamadı' mesajı<br>";
echo "<strong>Çözüm:</strong> tmp klasörü yazma yetkilerini kontrol edin</p>";

echo "<h3>3. Dosya Yetki Sorunu</h3>";
echo "<p><strong>Belirti:</strong> 'Permission denied' hatası<br>";
echo "<strong>Çözüm:</strong> uploads/ klasörü için 755 veya 777 yetkisi verin</p>";

echo "<h3>4. Memory/Timeout Sorunu</h3>";
echo "<p><strong>Belirti:</strong> Beyaz sayfa veya timeout<br>";
echo "<strong>Çözüm:</strong> PHP memory_limit ve max_execution_time artırın</p>";

echo "<h3>5. Include Dosya Sorunu</h3>";
echo "<p><strong>Belirti:</strong> 'File not found' hatası<br>";
echo "<strong>Çözüm:</strong> includes/ klasörü ve dosyaları kontrol edin</p>";

echo "</div>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>📊 Sistem Bilgileri</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "</p>";
echo "<p><strong>Upload Max Size:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>Error Reporting:</strong> " . (error_reporting() ? 'Açık' : 'Kapalı') . "</p>";
echo "</div>";

echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>🚨 Acil Çözüm</h2>";
echo "<p>Eğer hala 500 hatası alıyorsanız:</p>";
echo "<ol>";
echo "<li><strong>Error log kontrol:</strong> debug.log dosyasını kontrol edin</li>";
echo "<li><strong>Server error log:</strong> Apache/Nginx error log'una bakın</li>";
echo "<li><strong>PHP syntax check:</strong> php -l add-property.php komutunu çalıştırın</li>";
echo "<li><strong>Permission check:</strong> Dosya ve klasör yetkilerini kontrol edin</li>";
echo "</ol>";
echo "</div>";

// Debug log varsa göster
if (file_exists(__DIR__ . '/debug.log')) {
    echo "<div style='background: #263238; color: #fff; padding: 20px; border-radius: 8px; margin: 10px 0;'>";
    echo "<h2>📝 Son Debug Logları</h2>";
    $logs = file(__DIR__ . '/debug.log');
    $recent_logs = array_slice($logs, -10);
    echo "<pre style='color: #4caf50; font-size: 12px;'>";
    foreach ($recent_logs as $log) {
        echo htmlspecialchars($log);
    }
    echo "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center;'>";
echo "<strong>Hata tespit edildikten sonra bu dosyaları silebilirsiniz:</strong><br>";
echo "debug-test.php, test-steps.php, minimal-test.php, error-report.php";
echo "</p>";
?>
