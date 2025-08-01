<?php
require 'db.php';

// Mail testi için PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Test için sample mail gönder
function sendTestActivationMail() {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP ayarları
        $mail->isSMTP();
        $mail->Host = 'gokhanaydinli.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'root@gokhanaydinli.com';
        $mail->Password = 'Q!w2e3r4+';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Gönderen ve alıcı
        $mail->setFrom('root@gokhanaydinli.com', 'Gökhan Aydınlı Gayrimenkul');
        $mail->addAddress('sukru.sukruoglu@gmail.com', 'Test User');

        $mail->isHTML(true);
        $mail->Subject = 'LOGO TEST - Hesap Aktivasyonu';
        
        // Test activation link
        $activation_link = "https://gokhanaydinli.com/activate.php?token=test123";
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white;
                }
                .header { 
                    background: linear-gradient(135deg, #15B97C, #0d8c5a); 
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                }
                .content { 
                    padding: 30px; 
                    background: #f9f9f9; 
                }
                .button { 
                    display: inline-block; 
                    background: linear-gradient(135deg, #15B97C, #0d8c5a); 
                    color: white; 
                    padding: 15px 30px; 
                    text-decoration: none; 
                    border-radius: 25px; 
                    margin: 20px 0;
                    font-weight: bold;
                }
                .link-box {
                    background: #fff;
                    padding: 15px;
                    border-left: 4px solid #15B97C;
                    word-break: break-all;
                    margin: 15px 0;
                }
                .footer { 
                    padding: 20px; 
                    text-align: center; 
                    color: #666; 
                    font-size: 14px; 
                    background: #eee;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://gokhanaydinli.com/images/logoSiyah.png' alt='Gökhan Aydınlı Gayrimenkul' style='max-height: 60px; margin-bottom: 15px;'>
                    <h1>🎉 Hoş Geldiniz!</h1>
                    <p>Hesabınızı etkinleştirmek için son adım</p>
                </div>
                <div class='content'>
                    <h2>LOGO TEST MAILI</h2>
                    <p><strong>Bu mail logoSiyah.png test etmek için gönderilmiştir.</strong></p>
                    
                    <p>Logo yukarıda header bölümünde görünüyor olmalı.</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . htmlspecialchars($activation_link) . "' class='button'>
                            ✅ Test Link
                        </a>
                    </div>
                </div>
                <div class='footer'>
                    <p><strong>Gökhan Aydınlı Gayrimenkul</strong></p>
                    <p>Logo Test - " . date('Y-m-d H:i:s') . "</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return "✅ Test aktivasyon maili başarıyla gönderildi!";
    } catch (Exception $e) {
        return "❌ Mail gönderilemedi: " . $mail->ErrorInfo;
    }
}

// Test mailini gönder
$result = sendTestActivationMail();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo Test - Aktivasyon Maili</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">📧 Logo Test - Aktivasyon Maili</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h4>Test Sonucu:</h4>
                            <p><?= $result ?></p>
                        </div>
                        
                        <h5>📋 Test Detayları:</h5>
                        <ul>
                            <li><strong>Gönderen:</strong> root@gokhanaydinli.com</li>
                            <li><strong>Alıcı:</strong> sukru.sukruoglu@gmail.com</li>
                            <li><strong>Logo:</strong> https://gokhanaydinli.com/images/logoSiyah.png</li>
                            <li><strong>Logo Boyutu:</strong> max-height: 60px</li>
                            <li><strong>Test Zamanı:</strong> <?= date('Y-m-d H:i:s') ?></li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <h6>✅ Kontrol Listesi:</h6>
                            <p>E-posta geldiğinde şunları kontrol edin:</p>
                            <ol>
                                <li>Header bölümünde siyah logo görünüyor mu?</li>
                                <li>Logo boyutu uygun mu?</li>
                                <li>Logo net ve okunabilir mi?</li>
                            </ol>
                        </div>
                        
                        <div class="text-center">
                            <a href="index.php" class="btn btn-secondary me-2">Ana Sayfaya Dön</a>
                            <button onclick="location.reload()" class="btn btn-primary">Test Tekrarla</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
