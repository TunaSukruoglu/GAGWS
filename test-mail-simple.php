<?php
session_start();

// Contact.php'den mail fonksiyonunu doğrudan kullan
require_once 'contact.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_mail'])) {
    $result = sendContactEmail(
        'Test Kullanıcı',
        'test@example.com',
        'SMTP Mail Sistemi Test',
        'Bu bir SMTP test mesajıdır. MXRouting üzerinden gönderildi. Tarih: ' . date('Y-m-d H:i:s')
    );
    
    echo "<h3>" . ($result ? "✅ SMTP Mail gönderildi!" : "❌ SMTP Mail gönderilemedi!") . "</h3>";
    
    // Debug dosyasını oku
    if (file_exists('mail-debug.txt')) {
        echo "<h4>Debug Log:</h4>";
        echo "<pre>" . htmlspecialchars(file_get_contents('mail-debug.txt')) . "</pre>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mail Test</title>
</head>
<body>
    <h2>Mail Sistemi Test</h2>
    
    <form method="POST">
        <button type="submit" name="test_mail" style="padding: 15px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Test Mail Gönder
        </button>
    </form>
    
    <hr>
    
    <h3>Debug Bilgileri:</h3>
    <?php
    // PHPMailer durumu
    $phpmailer_available = false;
    if (file_exists('vendor/autoload.php')) {
        echo "✅ Vendor autoload mevcut<br>";
        $phpmailer_available = true;
    } elseif (file_exists('PHPMailer.php')) {
        echo "✅ Manuel PHPMailer mevcut<br>";
        $phpmailer_available = true;
    } else {
        echo "❌ PHPMailer bulunamadı<br>";
    }
    
    echo "PHP mail() fonksiyonu: " . (function_exists('mail') ? '✅ Mevcut' : '❌ Mevcut değil') . "<br>";
    echo "Sendmail yolu: " . (file_exists('/usr/sbin/sendmail') ? '✅ /usr/sbin/sendmail' : '❌ Bulunamadı') . "<br>";
    ?>
</body>
</html>
