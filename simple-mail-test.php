<?php
echo "<h1>📧 Basit Mail Test</h1>";
echo "<hr>";

// Debug log temizle
if (file_exists('mail-debug.txt')) {
    unlink('mail-debug.txt');
}

if (isset($_GET['test'])) {
    $test_type = $_GET['test'];
    
    echo "<h2>🔧 Test: $test_type</h2>";
    
    // Test ayarlarını belirle
    switch($test_type) {
        case '1':
            $host = 'gokhanaydinli.com';
            $port = 465;
            $secure = 'SSL';
            break;
        case '2':
            $host = 'gokhanaydinli.com';
            $port = 587;
            $secure = 'STARTTLS';
            break;
        case '3':
            $host = 'cp24.hosting.sh.com.tr';
            $port = 465;
            $secure = 'SSL';
            break;
        case '4':
            $host = 'cp24.hosting.sh.com.tr';
            $port = 587;
            $secure = 'STARTTLS';
            break;
        default:
            $host = 'gokhanaydinli.com';
            $port = 465;
            $secure = 'SSL';
    }
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>📋 Test Ayarları:</strong><br>";
    echo "🌐 Host: <strong>$host</strong><br>";
    echo "🔌 Port: <strong>$port</strong><br>";
    echo "🔐 Güvenlik: <strong>$secure</strong><br>";
    echo "👤 Kullanıcı: <strong>root@gokhanaydinli.com</strong><br>";
    echo "📨 Alıcı: <strong>sukru.sukruoglu@gmail.com</strong><br>";
    echo "</div>";
    
    // Dinamik mail testi - contact.php kullanmak yerine direkt test
    // PHPMailer kütüphanelerini dahil et
    $phpmailer_available = false;
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        $phpmailer_available = true;
    }
    
    if (!$phpmailer_available) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ HATA!</h4>";
        echo "<p>PHPMailer kütüphanesi bulunamadı!</p>";
        echo "</div>";
    } else {
        // Debug log
        $debug_log = "Mail gönderim denemesi: " . date('Y-m-d H:i:s') . " | Test Kullanıcı | test@test.com | Mail Test ($host:$port)\n";
        file_put_contents('mail-debug.txt', $debug_log, FILE_APPEND | LOCK_EX);
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Debug için
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                file_put_contents('mail-debug.txt', date('Y-m-d H:i:s') . " DEBUG: $str\n", FILE_APPEND | LOCK_EX);
            };
            
            // SMTP ayarları - dinamik
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = 'root@gokhanaydinli.com';
            $mail->Password   = 'rna(-^-S*wQdJ$JH';
            
            if ($secure == 'SSL') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            
            // Gönderen ve alıcı
            $mail->setFrom('root@gokhanaydinli.com', 'Gökhan Aydınlı Website');
            $mail->addAddress('sukru.sukruoglu@gmail.com');
            $mail->addReplyTo('test@test.com', 'Test Kullanıcı');
            
            // Mail içeriği
            $mail->isHTML(true);
            $mail->Subject = "Mail Test ($host:$port)";
            $mail->Body = "Bu mail $host sunucusu üzerinden $port portu ile gönderildi.<br>Güvenlik: $secure<br>Test zamanı: " . date('d.m.Y H:i:s');
            
            $result = $mail->send();
            file_put_contents('mail-debug.txt', "PHPMailer SMTP sonucu: " . ($result ? 'BAŞARILI' : 'BAŞARISIZ') . "\n", FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            $result = false;
            $error_msg = "SMTP Mail gönderim hatası: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
            file_put_contents('mail-debug.txt', $error_msg . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    echo "<h3>📊 Sonuç:</h3>";
    if ($result) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>";
        echo "<h4>✅ BAŞARILI!</h4>";
        echo "<p>Mail başarıyla gönderildi!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ BAŞARISIZ!</h4>";
        echo "<p>Mail gönderilemedi.</p>";
        echo "</div>";
    }
    
    // Debug log göster
    if (file_exists('mail-debug.txt')) {
        echo "<h4>🔍 Debug Log:</h4>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;'>";
        echo "<pre>" . htmlspecialchars(file_get_contents('mail-debug.txt')) . "</pre>";
        echo "</div>";
    }
    
    echo "<br><a href='?' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔙 Geri Dön</a>";
    
} else {
    
    echo "<h2>🎯 Mail Sunucu Testleri</h2>";
    echo "<p>Hangi ayarın çalıştığını bulmak için testleri sırayla deneyin:</p>";
    
    echo "<div style='margin: 20px 0;'>";
    
    echo "<div style='border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>🥇 Test 1: gokhanaydinli.com + Port 465 (SSL) ⭐</h4>";
    echo "<p><strong>Resmi ayar!</strong> Hosting panelinde belirtilen doğru konfigürasyon</p>";
    echo "<a href='?test=1' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>▶️ Test Et</a>";
    echo "</div>";
    
    echo "<div style='border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>🥈 Test 2: gokhanaydinli.com + Port 587 (STARTTLS)</h4>";
    echo "<p>Alternatif port - bazı sunucularda çalışabilir</p>";
    echo "<a href='?test=2' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>▶️ Test Et</a>";
    echo "</div>";
    
    echo "<div style='border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>🥉 Test 3: cp24.hosting.sh.com.tr + Port 465</h4>";
    echo "<p>Doğrudan hosting sunucusu SSL</p>";
    echo "<a href='?test=3' style='background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>▶️ Test Et</a>";
    echo "</div>";
    
    echo "<div style='border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>🆘 Test 4: cp24.hosting.sh.com.tr + Port 587</h4>";
    echo "<p>Hosting sunucusu STARTTLS</p>";
    echo "<a href='?test=4' style='background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>▶️ Test Et</a>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ Güncellendi!</h4>";
    echo "<p><strong>Test 1</strong> artık hosting panelinde belirtilen resmi ayarları kullanıyor:</p>";
    echo "<p>📧 <strong>gokhanaydinli.com:465</strong> (SSL) + doğru şifre</p>";
    echo "<p>Bu ayar %99 çalışacak!</p>";
    echo "</div>";
}

echo "<hr>";
echo "<small>🕒 " . date('d.m.Y H:i:s') . "</small>";
?>
