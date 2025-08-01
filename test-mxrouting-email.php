<?php
// PHPMailer test - MXRouting
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

echo "<h1>📧 MXRouting Email Test</h1>";

$mail = new PHPMailer(true);

try {
    // MXRouting ayarları
    $mail->isSMTP();
    $mail->Host       = 'blizzard.mxrouting.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@gokhanaydinli.com';
    $mail->Password   = '113041sS?!';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    // Debug için
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    
    // Charset
    $mail->CharSet = 'UTF-8';
    
    // Gönderen ve alıcı
    $mail->setFrom('no-reply@gokhanaydinli.com', 'Gökhan Aydınlı Test');
    $mail->addAddress('sukru.sukruoglu@gmail.com', 'Test Kullanıcı'); // Gerçek email adresi kullanın
    
    // İçerik
    $mail->isHTML(true);
    $mail->Subject = 'Test Email - MXRouting';
    $mail->Body    = '<h1>Test Başarılı!</h1><p>MXRouting ile email gönderimi çalışıyor.</p>';
    
    $mail->send();
    echo "<div style='color: green;'><h2>✅ Email başarıyla gönderildi!</h2></div>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'><h2>❌ Email gönderilemedi!</h2>";
    echo "<p><strong>Hata:</strong> {$mail->ErrorInfo}</p></div>";
}
?>
