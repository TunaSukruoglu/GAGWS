<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Mail Test Başlıyor...<br><br>";

// PHPMailer include
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "✅ Vendor autoload bulundu<br>";
    $phpmailer_available = true;
} else {
    echo "❌ Vendor autoload bulunamadı<br>";
    // Manuel include dene
    if (file_exists('PHPMailer.php')) {
        require_once 'PHPMailer.php';
        echo "✅ Manuel PHPMailer bulundu<br>";
        $phpmailer_available = true;
    } else {
        echo "❌ PHPMailer bulunamadı<br>";
        $phpmailer_available = false;
    }
}

if ($phpmailer_available) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        echo "✅ PHPMailer nesnesi oluşturuldu<br>";
        
        // SMTP ayarları
        $mail->isSMTP();
        $mail->Host       = 'localhost';
        $mail->SMTPAuth   = false;
        $mail->Port       = 25;
        $mail->CharSet    = 'UTF-8';
        
        echo "✅ SMTP ayarları yapıldı<br>";
        
        // Gönderen ve alıcı
        $mail->setFrom('no-reply@sunucu.dev', 'Test Mail');
        $mail->addAddress('sukru.sukruoglu@gmail.com');
        
        echo "✅ Gönderen/Alıcı ayarlandı<br>";
        
        // Mail içeriği
        $mail->isHTML(true);
        $mail->Subject = 'Mail Test - ' . date('Y-m-d H:i:s');
        $mail->Body = '<h2>Test Mail</h2><p>Bu bir test mailidir. Tarih: ' . date('Y-m-d H:i:s') . '</p>';
        
        echo "✅ Mail içeriği hazırlandı<br>";
        
        $result = $mail->send();
        
        if ($result) {
            echo "✅ <strong>MAIL BAŞARIYLA GÖNDERİLDİ!</strong><br>";
        } else {
            echo "❌ Mail gönderilemedi<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ <strong>HATA:</strong> " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    }
} else {
    echo "❌ PHPMailer mevcut değil - Basit mail() fonksiyonunu deneyelim<br>";
    
    $to = 'sukru.sukruoglu@gmail.com';
    $subject = 'Test Mail (mail function) - ' . date('Y-m-d H:i:s');
    $message = 'Bu bir test mailidir. PHP mail() fonksiyonu ile gönderildi. Tarih: ' . date('Y-m-d H:i:s');
    $headers = 'From: no-reply@sunucu.dev' . "\r\n" .
               'Reply-To: no-reply@sunucu.dev' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        echo "✅ <strong>BASIT MAIL BAŞARIYLA GÖNDERİLDİ!</strong><br>";
    } else {
        echo "❌ Basit mail de gönderilemedi<br>";
    }
}

echo "<br>Test tamamlandı.";
?>
