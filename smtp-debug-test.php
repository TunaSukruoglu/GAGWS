<?php
echo "<h1>🔧 SMTP Debug Test - mail.gokhanaydinli.com</h1>";
echo "<hr>";

// Debug log dosyasını temizle
if (file_exists('mail-debug.txt')) {
    unlink('mail-debug.txt');
}

if (isset($_GET['action']) && $_GET['action'] == 'test_smtp') {
    echo "<h2>📤 SMTP Bağlantı Testi Başlatılıyor...</h2>";
    
    // Contact.php'den mail fonksiyonunu kullan
    include 'contact.php';
    
    echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🔧 Test Ayarları:</h3>";
    echo "📧 Host: <strong>mail.gokhanaydinli.com</strong><br>";
    echo "🔐 Port: <strong>465 (SSL)</strong><br>";
    echo "👤 Username: <strong>root@gokhanaydinli.com</strong><br>";
    echo "🔑 Password: <strong>113041sS?!_</strong><br>";
    echo "📨 Alıcı: <strong>info@gokhanaydinli.com</strong><br>";
    echo "🐛 Debug: <strong>Level 2 (Detaylı)</strong><br>";
    echo "</div>";
    
    $result = sendContactEmail(
        'SMTP Test User',
        'test@example.com',
        'SMTP Test - mail.gokhanaydinli.com - ' . date('Y-m-d H:i:s'),
        'Bu test maili yeni mail.gokhanaydinli.com host ayarı ile gönderildi.<br><br>' .
        '🔧 Test Detayları:<br>' .
        '• Host: mail.gokhanaydinli.com<br>' .
        '• Port: 465 (SSL/TLS)<br>' .
        '• Authentication: Aktif<br>' .
        '• Debug Mode: Level 2<br><br>' .
        'Test zamanı: ' . date('d.m.Y H:i:s') . '<br>' .
        'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor')
    );
    
    echo "<h3>📊 Test Sonucu:</h3>";
    if ($result) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ SMTP Test Başarılı!</h4>";
        echo "<p>Mail başarıyla gönderildi! mail.gokhanaydinli.com sunucusu çalışıyor.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ SMTP Test Başarısız!</h4>";
        echo "<p>Mail gönderilemedi. Debug logları kontrol edin.</p>";
        echo "</div>";
    }
    
    // Debug log göster
    if (file_exists('mail-debug.txt')) {
        echo "<h3>🔍 SMTP Debug Log:</h3>";
        echo "<div style='background: #212529; color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
        echo "<pre style='color: #f8f9fa; margin: 0;'>";
        echo htmlspecialchars(file_get_contents('mail-debug.txt'));
        echo "</pre>";
        echo "</div>";
        
        // Log analizi
        $log_content = file_get_contents('mail-debug.txt');
        echo "<h3>📈 Log Analizi:</h3>";
        echo "<div style='background: #e7f3ff; border: 1px solid #b6d7ff; padding: 15px; border-radius: 5px;'>";
        
        if (strpos($log_content, 'SMTP ERROR') !== false) {
            echo "❌ <strong>SMTP HATASI BULUNDU:</strong><br>";
            preg_match_all('/SMTP ERROR: (.+)/', $log_content, $errors);
            foreach ($errors[1] as $error) {
                echo "• " . htmlspecialchars($error) . "<br>";
            }
        }
        
        if (strpos($log_content, 'SMTP connect()') !== false) {
            echo "🔗 SMTP Bağlantı girişimi tespit edildi<br>";
        }
        
        if (strpos($log_content, 'SMTP INBOUND') !== false) {
            echo "📨 Sunucu yanıtları alındı<br>";
        }
        
        if (strpos($log_content, '250') !== false) {
            echo "✅ Pozitif SMTP yanıtları (250 kodları) tespit edildi<br>";
        }
        
        if (strpos($log_content, 'Authentication successful') !== false) {
            echo "🔐 Kimlik doğrulama başarılı<br>";
        }
        
        echo "</div>";
    } else {
        echo "<p>❌ Debug log dosyası oluşturulmadı.</p>";
    }
}

echo "<h2>🎯 SMTP Test Seçenekleri:</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='?action=test_smtp' onclick='return confirm(\"SMTP test başlatılsın mı?\")' style='background: #dc3545; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold;'>🔧 SMTP Debug Test</a><br><br>";
echo "</div>";

echo "<h2>📋 Mevcut Ayarlar:</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8f9fa;'><th>Ayar</th><th>Değer</th><th>Durum</th></tr>";
echo "<tr><td><strong>Host</strong></td><td>mail.gokhanaydinli.com</td><td>🆕 YENİ</td></tr>";
echo "<tr><td><strong>Port</strong></td><td>465</td><td>✅ SSL</td></tr>";
echo "<tr><td><strong>Username</strong></td><td>root@gokhanaydinli.com</td><td>✅ Aktif</td></tr>";
echo "<tr><td><strong>Password</strong></td><td>113041sS?!_</td><td>✅ Güçlü</td></tr>";
echo "<tr><td><strong>Encryption</strong></td><td>SMTPS (SSL/TLS)</td><td>✅ Güvenli</td></tr>";
echo "<tr><td><strong>Authentication</strong></td><td>Required</td><td>✅ Aktif</td></tr>";
echo "<tr><td><strong>Debug Level</strong></td><td>2 (Detaylı)</td><td>🐛 Debug</td></tr>";
echo "<tr><td><strong>Alıcı</strong></td><td>info@gokhanaydinli.com</td><td>📧 İletişim</td></tr>";
echo "</table>";

echo "<h2>🔄 Alternatif Sunucu Ayarları:</h2>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
echo "<h4>Test Edilebilecek Diğer Ayarlar:</h4>";
echo "<ul>";
echo "<li><strong>Host:</strong> smtp.gokhanaydinli.com (Port 587 STARTTLS)</li>";
echo "<li><strong>Host:</strong> gokhanaydinli.com (Port 465 SSL)</li>";
echo "<li><strong>Host:</strong> mail.gokhanaydinli.com (Port 587 STARTTLS)</li>";
echo "<li><strong>Host:</strong> mail.gokhanaydinli.com (Port 25 - güvensiz)</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p>";
echo "<a href='mail-config-test.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📧 Ana Mail Test</a> ";
echo "<a href='verify-bypass.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>👥 Kullanıcı Yönetimi</a>";
echo "</p>";
echo "<small>🕒 Test Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
