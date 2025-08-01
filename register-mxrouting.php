<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

// Email log fonksiyonu
function logEmail($to, $subject, $status, $method = '', $error_message = null) {
    global $conn;
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, method, status, error_message, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $to, $subject, $method, $status, $error_message, $user_agent, $ip_address);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Email log error: " . $e->getMessage());
    }
}

// Token oluşturma fonksiyonu
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    // Basit validasyon
    if (empty($fullname) || empty($phone) || empty($email) || empty($password) || empty($password_confirm)) {
        $_SESSION['register_error'] = "Lütfen tüm zorunlu alanları doldurun.";
        header("Location: kayit.php");
        exit;
    }

    if (empty($terms)) {
        $_SESSION['register_error'] = "Şartlar ve koşulları kabul etmelisiniz.";
        header("Location: kayit.php");
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['register_error'] = "Şifreler eşleşmiyor.";
        header("Location: kayit.php");
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Şifre en az 6 karakter olmalıdır.";
        header("Location: kayit.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Geçerli bir e-posta adresi girin.";
        header("Location: kayit.php");
        exit;
    }

    // Telefon format kontrolü
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone_clean) < 10) {
        $_SESSION['register_error'] = "Geçerli bir telefon numarası girin.";
        header("Location: kayit.php");
        exit;
    }

    try {
        // Email kontrolü
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$check_stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            $_SESSION['register_error'] = "Bu e-posta adresi zaten kayıtlı.";
            header("Location: kayit.php");
            exit;
        }
        $check_stmt->close();

        // Kullanıcı kayıt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = generateVerificationToken();

        $insert_stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, role, is_approved, verification_token, is_verified) VALUES (?, ?, ?, ?, 'user', 0, ?, 0)");
        if (!$insert_stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }

        $insert_stmt->bind_param("sssss", $fullname, $phone_clean, $email, $hashed_password, $verification_token);

        if ($insert_stmt->execute()) {
            $user_id = $conn->insert_id;
            $insert_stmt->close();
            
            // Email gönderme (MXRouting ile PHP mail())
            $verification_link = "https://sunucu.dev/GokhanAydinli/activate.php?token=" . $verification_token;
            
            $subject = "Email Adresinizi Doğrulayın - Gökhan Aydınlı Gayrimenkul";
            $message = "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .header { text-align: center; background: #667eea; color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🏠 Gökhan Aydınlı Gayrimenkul</h1>
            <p>Email Adresinizi Doğrulayın</p>
        </div>
        
        <h2>Merhaba " . htmlspecialchars($fullname) . "!</h2>
        
        <p>Gökhan Aydınlı Gayrimenkul'e kayıt olduğunuz için teşekkür ederiz.</p>
        
        <div class='info'>
            <strong>📋 Kayıt Bilgileriniz:</strong><br>
            <strong>Ad Soyad:</strong> " . htmlspecialchars($fullname) . "<br>
            <strong>Email:</strong> " . htmlspecialchars($email) . "<br>
            <strong>Telefon:</strong> " . htmlspecialchars($phone) . "<br>
            <strong>Kayıt Tarihi:</strong> " . date('d.m.Y H:i') . "
        </div>
        
        <p>Email adresinizi doğrulamak için aşağıdaki butona tıklayın:</p>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='" . $verification_link . "' class='button'>✅ Email Adresimi Doğrula</a>
        </div>
        
        <p><strong>Önemli:</strong> Bu link 24 saat boyunca geçerlidir.</p>
        
        <p>Link çalışmıyorsa bu adresi kopyalayın:</p>
        <p style='background: #f8f9fa; padding: 10px; word-break: break-all; font-size: 12px;'>" . $verification_link . "</p>
        
        <div style='text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px; font-size: 12px;'>
            <p>Bu email MXRouting (blizzard.mxrouting.net) üzerinden gönderilmiştir.</p>
            <p>© 2025 Gökhan Aydınlı Gayrimenkul</p>
        </div>
    </div>
</body>
</html>";
            
            // MXRouting için optimize edilmiş headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: Gökhan Aydınlı Gayrimenkul <no-reply@sunucu.dev>\r\n";
            $headers .= "Reply-To: no-reply@sunucu.dev\r\n";
            $headers .= "Return-Path: no-reply@sunucu.dev\r\n";
            $headers .= "Message-ID: <" . time() . "." . uniqid() . "@sunucu.dev>\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            
            $email_sent = false;
            try {
                if (mail($email, $subject, $message, $headers)) {
                    $email_sent = true;
                    logEmail($email, $subject, 'SENT', 'MXRouting PHP mail()');
                } else {
                    logEmail($email, $subject, 'FAILED', 'MXRouting PHP mail()', 'mail() fonksiyonu false döndü');
                }
            } catch (Exception $e) {
                logEmail($email, $subject, 'ERROR', 'MXRouting PHP mail()', $e->getMessage());
            }
            
            if ($email_sent) {
                $_SESSION['register_success'] = "✅ Kayıt başarılı! <strong>" . htmlspecialchars($email) . "</strong> adresine doğrulama email'i gönderildi. (MXRouting ile)";
            } else {
                $_SESSION['register_success'] = "⚠️ Kayıt başarılı ancak email gönderilemedi. <a href='mxrouting-email-test.php'>MXRouting Email Test</a> sayfasından kontrol edin.";
            }
            
        } else {
            throw new Exception("Kullanıcı kaydedilemedi: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['register_error'] = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
        error_log("Register error: " . $e->getMessage());
    }

    $conn->close();
    header("Location: kayit.php");
    exit;
}

// GET isteği gelirse kayıt formuna yönlendir
header("Location: kayit.php");
exit;
?>
