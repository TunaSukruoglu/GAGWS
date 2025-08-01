<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

// Email log fonksiyonu - Veritabanına kaydet
function logEmail($to, $subject, $status, $method = '', $error_message = null) {
    global $conn;
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    
    try {
        $stmt = $conn->prepare("INSERT INTO email_logs (to_email, subject, method, status, error_message, user_agent, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $to, $subject, $method, $status, $error_message, $user_agent, $ip_address);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        // Fallback - dosyaya yaz
        $log_entry = date('Y-m-d H:i:s') . " | TO: $to | SUBJECT: $subject | METHOD: $method | STATUS: $status | ERROR: " . $e->getMessage() . "\n";
        file_put_contents('email_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    }
}

// Token oluşturma fonksiyonu
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Honeypot kontrolü (gizli alan boş olmalı)
    $honeypot = trim($_POST['website'] ?? '');
    if (!empty($honeypot)) {
        error_log("Bot detected in register-with-verification - Honeypot filled: " . $_SERVER['REMOTE_ADDR']);
        $_SESSION['register_error'] = "Güvenlik kontrolü başarısız. Lütfen tekrar deneyin.";
        header("Location: register-form.php");
        exit;
    }
    
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    // Boş alan kontrolü
    if (empty($fullname) || empty($phone) || empty($email) || empty($password) || empty($password_confirm)) {
        $_SESSION['register_error'] = "Lütfen tüm zorunlu alanları doldurun.";
        header("Location: register-form.php");
        exit;
    }

    // Şartlar kontrolü
    if (empty($terms)) {
        $_SESSION['register_error'] = "Şartlar ve koşulları kabul etmelisiniz.";
        header("Location: register-form.php");
        exit;
    }

    // Şifre eşleşme kontrolü
    if ($password !== $password_confirm) {
        $_SESSION['register_error'] = "Şifreler eşleşmiyor.";
        header("Location: register-form.php");
        exit;
    }

    // Şifre güçlülük kontrolü
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Şifre en az 6 karakter olmalıdır.";
        header("Location: register-form.php");
        exit;
    }

    // Email format kontrolü
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Geçerli bir e-posta adresi girin.";
        header("Location: register-form.php");
        exit;
    }

    // Telefon format kontrolü (basit)
    $phone_clean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone_clean) < 10) {
        $_SESSION['register_error'] = "Geçerli bir telefon numarası girin.";
        header("Location: register-form.php");
        exit;
    }

    try {
        // E-posta adresinin kayıtlı olup olmadığını kontrol et
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['register_error'] = "Bu e-posta adresi zaten kayıtlı.";
            $check_stmt->close();
            header("Location: register-form.php");
            exit;
        }
        $check_stmt->close();

        // Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Doğrulama token'ı oluştur
        $verification_token = generateVerificationToken();

        // Kullanıcıyı veritabanına kaydet (doğrulanmamış olarak)
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password, role, is_approved, verification_token, is_verified) VALUES (?, ?, ?, ?, 'user', 0, ?, 0)");
        $stmt->bind_param("sssss", $fullname, $phone_clean, $email, $hashed_password, $verification_token);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $stmt->close();
            
            // Email doğrulama linkini oluştur
            $verification_link = "https://gokhanaydinli.com/activate.php?token=" . $verification_token;
            
            // Email göndermeyi dene
            $email_sent = false;
            $email_method = '';
            
            try {
                $subject = "Email Adresinizi Doğrulayın - Gökhan Aydınlı Gayrimenkul";
                $message = "
                <!DOCTYPE html>
                <html lang='tr'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Email Doğrulama</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background: #f4f4f4; }
                        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                        .header { text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
                        .content { padding: 20px 0; }
                        .button { display: inline-block; background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>🏠 Gökhan Aydınlı Gayrimenkul</h1>
                            <p>Email Adresinizi Doğrulayın</p>
                        </div>
                        
                        <div class='content'>
                            <h2>Merhaba " . htmlspecialchars($fullname) . "!</h2>
                            
                            <p>Gökhan Aydınlı Gayrimenkul'e kayıt olduğunuz için teşekkür ederiz. Hesabınızı aktifleştirmek için email adresinizi doğrulamanız gerekmektedir.</p>
                            
                            <div class='info'>
                                <strong>📋 Kayıt Bilgileriniz:</strong><br>
                                <strong>Ad Soyad:</strong> " . htmlspecialchars($fullname) . "<br>
                                <strong>Email:</strong> " . htmlspecialchars($email) . "<br>
                                <strong>Telefon:</strong> " . htmlspecialchars($phone) . "<br>
                                <strong>Kayıt Tarihi:</strong> " . date('d.m.Y H:i') . "
                            </div>
                            
                            <p>Email adresinizi doğrulamak için aşağıdaki butona tıklayın:</p>
                            
                            <div style='text-align: center;'>
                                <a href='" . $verification_link . "' class='button'>
                                    ✅ Email Adresimi Doğrula
                                </a>
                            </div>
                            
                            <p><strong>Önemli:</strong> Bu link 24 saat boyunca geçerlidir. Email doğrulaması yapmadan sisteme giriş yapamazsınız.</p>
                            
                            <p>Eğer butona tıklayamıyorsanız, aşağıdaki linki tarayıcınıza kopyalayın:</p>
                            <p style='background: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px;'>
                                " . $verification_link . "
                            </p>
                        </div>
                        
                        <div class='footer'>
                            <p>Bu email otomatik olarak gönderilmiştir. Lütfen yanıtlamayın.</p>
                            <p>© 2025 Gökhan Aydınlı Gayrimenkul - Tüm hakları saklıdır.</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: Gökhan Aydınlı Gayrimenkul <no-reply@sunucu.dev>\r\n";
                $headers .= "Reply-To: no-reply@sunucu.dev\r\n";
                
                if (mail($email, $subject, $message, $headers)) {
                    $email_sent = true;
                    $email_method = 'PHP mail()';
                    logEmail($email, $subject, 'SENT', $email_method);
                } else {
                    logEmail($email, $subject, 'FAILED', 'PHP mail()', 'PHP mail() fonksiyonu false döndü');
                }
            } catch (Exception $e) {
                logEmail($email, $subject, 'ERROR', 'PHP mail()', $e->getMessage());
            }
            
            if ($email_sent) {
                $_SESSION['register_success'] = "
                    <strong>Kayıt başarılı!</strong><br>
                    <strong>" . htmlspecialchars($email) . "</strong> adresine bir doğrulama email'i gönderildi.<br>
                    <strong>Lütfen email kutunuzu kontrol edin ve doğrulama linkine tıklayın.</strong><br>
                    <small>Email gelmemişse spam klasörünü kontrol edin.</small>
                ";
            } else {
                $_SESSION['register_success'] = "
                    Kayıt başarılı ancak doğrulama email'i gönderilemedi.<br>
                    Lütfen manuel olarak email doğrulaması yapın.<br>
                    Token: <code>" . $verification_token . "</code>
                ";
            }
            
        } else {
            $_SESSION['register_error'] = "Kayıt sırasında bir hata oluştu: " . $conn->error;
        }
        
    } catch (Exception $e) {
        $_SESSION['register_error'] = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
        error_log("Register error: " . $e->getMessage());
    }

    $conn->close();
    header("Location: register-form.php");
    exit;
}

// GET isteği gelirse forma yönlendir
header("Location: register-form.php");
exit;
?>
