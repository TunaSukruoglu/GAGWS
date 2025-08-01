<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $_SESSION['resend_error'] = "❌ Email adresi gerekli.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    try {
        // Kullanıcıyı bul
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ? AND is_verified = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Yeni token oluştur
            $verification_token = bin2hex(random_bytes(32));
            
            // Token'ı güncelle
            $update_stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
            $update_stmt->bind_param("si", $verification_token, $row['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Email gönder - gokhanaydinli.com domain
            $verification_link = "https://gokhanaydinli.com/activate.php?token=" . $verification_token;
            
            require 'PHPMailer.php';
            require 'SMTP.php';
            require 'Exception.php';

            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\SMTP;
            use PHPMailer\PHPMailer\Exception;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'localhost';
                $mail->SMTPAuth = false;
                $mail->Port = 25;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('no-reply@sunucu.dev', 'Gökhan Aydınlı Gayrimenkul');
                $mail->addAddress($email, $row['name']);

                $mail->isHTML(true);
                $mail->Subject = '📧 Email Onay Linki (Yeniden Gönderildi) - Gökhan Aydınlı Gayrimenkul';
                
                $mail->Body = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Email Onayı - Yeniden Gönderim</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                        .content { background: white; padding: 30px; border: 1px solid #ddd; }
                        .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                        .footer { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header">
                            <h1>🔄 Onay Linki Yeniden Gönderildi</h1>
                            <p>Gökhan Aydınlı Gayrimenkul</p>
                        </div>
                        <div class="content">
                            <p>Merhaba <strong>' . htmlspecialchars($row['name']) . '</strong>,</p>
                            
                            <p>Email onay linkinizi yeniden gönderdik! 📬</p>
                            
                            <p>Hesabınızı aktifleştirmek için aşağıdaki butona tıklayın:</p>
                            
                            <div style="text-align: center;">
                                <a href="' . $verification_link . '" class="button">
                                    📧 Email Adresimi Onayla
                                </a>
                            </div>
                            
                            <p>Bu link 24 saat geçerlidir.</p>
                            
                            <p>İyi günler dileriz! 🌟</p>
                        </div>
                        <div class="footer">
                            <p>Bu email otomatik olarak gönderilmiştir.</p>
                            <p>© 2025 Gökhan Aydınlı Gayrimenkul</p>
                        </div>
                    </div>
                </body>
                </html>';

                $mail->send();
                $_SESSION['resend_success'] = "✅ Onay linki email adresinize yeniden gönderildi!";
                
            } catch (Exception $e) {
                $_SESSION['resend_error'] = "⚠️ Email gönderilirken hata oluştu.";
            }
            
        } else {
            $_SESSION['resend_error'] = "❌ Bu email adresi bulunamadı veya zaten onaylanmış.";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['resend_error'] = "❌ Sistem hatası oluştu.";
        error_log("Resend verification error: " . $e->getMessage());
    }
    
    $conn->close();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
