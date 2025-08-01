<?php
echo "<h1>📧 Root Mail Sistemi - Konfigürasyon Testi</        '🔧 Host: mail.gokhanaydinli.com<br>' .
        '🔐 Port: 465 (SSL/TLS)<br><br>' .>";
echo "<hr>";

// Mail ayarları test et
echo "<h2>📋 Güncel Mail Konfigürasyonu:</h2>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔧 Yeni Mail Ayarları (root@gokhanaydinli.com):</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th>Ayar</th><th>Değer</th>";
echo "</tr>";
echo "<tr><td><strong>Email Kullanıcısı</strong></td><td>root@gokhanaydinli.com</td></tr>";
echo "<tr><td><strong>Şifre</strong></td><td>113041sS?!_</td></tr>";
echo "<tr><td><strong>Gelen Sunucu</strong></td><td>gokhanaydinli.com</td></tr>";
echo "<tr><td><strong>IMAP Port</strong></td><td>993 (SSL)</td></tr>";
echo "<tr><td><strong>POP3 Port</strong></td><td>995 (SSL)</td></tr>";
echo "<tr><td><strong>Giden Sunucu</strong></td><td>gokhanaydinli.com</td></tr>";
echo "<tr><td><strong>SMTP Port</strong></td><td>465 (SSL)</td></tr>";
echo "<tr><td><strong>Kimlik Doğrulama</strong></td><td>✅ Gerekli</td></tr>";
echo "</table>";
echo "</div>";

// Güncellenen dosyalar
echo "<h2>✅ Güncellenen Mail Dosyaları:</h2>";
$updated_files = [
    'contact.php' => 'Ana iletişim formu SMTP ayarları',
    'register.php' => 'Kayıt sistemi email doğrulama ayarları', 
    'verify-email.php' => 'Email doğrulama hoşgeldin mesajı',
    'inc/contact.php' => 'Alternatif iletişim formu headers'
];

echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8f9fa;'>";
echo "<th>Dosya</th><th>Güncellenen Kısım</th><th>Durum</th>";
echo "</tr>";

foreach ($updated_files as $file => $description) {
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$description</td>";
    echo "<td><span style='color: green;'>✅ Güncellendi</span></td>";
    echo "</tr>";
}
echo "</table>";

// Test maili gönder
if (isset($_GET['action']) && $_GET['action'] == 'test_mail') {
    echo "<h3>📤 Test Mail Gönderiliyor...</h3>";
    
    // Contact.php'den fonksiyonu kullan
    include 'contact.php';
    
    $result = sendContactEmail(
        'Mail Test Kullanıcı',
        'test@example.com',
        'İletişim Formu Test - ' . date('Y-m-d H:i:s'),
        'Bu test maili yeni konfigürasyon ile gönderildi:<br><br>' .
        '� Gönderen: root@gokhanaydinli.com (Backend)<br>' .
        '� Alıcı: info@gokhanaydinli.com (İletişim)<br>' .
        '� Host: gokhanaydinli.com<br>' .
        '🔐 Port: 465 (SSL/TLS)<br><br>' .
        'Test zamanı: ' . date('d.m.Y H:i:s')
    );
    
    if ($result) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ Test Mail Başarılı!</h4>";
        echo "<p>Root mail sistemi doğru çalışıyor. Email gönderildi!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Test Mail Başarısız!</h4>";
        echo "<p>Mail gönderiminde sorun var. Ayarları kontrol edin.</p>";
        echo "</div>";
    }
    
    // Debug log kontrol et
    if (file_exists('mail-debug.txt')) {
        echo "<h4>🔍 Mail Debug Log:</h4>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; font-size: 12px;'>";
        echo htmlspecialchars(file_get_contents('mail-debug.txt'));
        echo "</pre>";
    }
}

echo "<h2>🧪 Mail Sistemi Test Butonları:</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='?action=test_mail' onclick='return confirm(\"Test mail gönderilsin mi?\")' style='background: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📤 Root Mail Test Et</a><br><br>";
echo "<a href='test-mail.php' style='background: #17a2b8; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Eski Mail Test</a><br><br>";
echo "<a href='test-mail-simple.php' style='background: #6f42c1; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📨 Basit Mail Test</a><br><br>";
echo "</div>";

echo "<h2>📚 Root Mail Konfigürasyon Özeti:</h2>";
echo "<div style='background: #e7f3ff; border: 1px solid #b6d7ff; padding: 15px; border-radius: 5px;'>";
echo "<h4>🎯 Tamamlanan İşlemler:</h4>";
echo "<ul>";
echo "<li>✅ <strong>contact.php</strong>: SMTP Host → mail.gokhanaydinli.com, Username → root@gokhanaydinli.com</li>";
echo "<li>✅ <strong>register.php</strong>: Kayıt email doğrulama sistemi → root@gokhanaydinli.com</li>";
echo "<li>✅ <strong>verify-email.php</strong>: Hoşgeldin email headers → root@gokhanaydinli.com</li>";
echo "<li>✅ <strong>inc/contact.php</strong>: Form email headers → root@gokhanaydinli.com</li>";
echo "<li>✅ <strong>SMTP Ayarları</strong>: SSL Port 465, Authentication aktif</li>";
echo "</ul>";

echo "<h4>📧 Backend Mail Sistemi (Arka Plan):</h4>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
echo "✅ Backend SMTP: root@gokhanaydinli.com (Gizli - Mail gönderimleri için)\n";
echo "✅ Frontend Links: info@gokhanaydinli.com (Görünür - Kullanıcılar için)\n";
echo "✅ Tur Talepleri: root@gokhanaydinli.com üzerinden gönderilir\n";
echo "✅ Kayıt Mailleri: root@gokhanaydinli.com üzerinden gönderilir\n";
echo "✅ İletişim Formu: root@gokhanaydinli.com üzerinden gönderilir\n";
echo "</pre>";

echo "<h4>📧 Mail Sunucu Ayarları:</h4>";
echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;'>";
echo "Host: mail.gokhanaydinli.com\n";
echo "SMTP Port: 465 (SSL/TLS)\n";
echo "Backend Username: root@gokhanaydinli.com\n";
echo "Password: 113041sS?!_\n";
echo "Authentication: Required\n";
echo "Encoding: UTF-8\n";
echo "</pre>";
echo "</div>";

echo "<hr>";
echo "<p><a href='verify-bypass.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔙 Email Doğrulama Sayfasına Dön</a></p>";
echo "<small>🕒 Kontrol Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
