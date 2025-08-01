<?php
session_start();

// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Veritabanı bağlantısı
include 'db.php';

// PHPMailer dahil et
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // BOT KORUMA KONTROLU
        session_start();
        
        // 1. Honeypot kontrolü (gizli alan boş olmalı)
        $honeypot = trim($_POST['website'] ?? '');
        if (!empty($honeypot)) {
            error_log("Bot detected - Honeypot filled: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['register_error'] = "Güvenlik kontrolü başarısız.";
            header('Location: index.php');
            exit();
        }
        
        // 2. Rate limiting (son 5 dakikada max 3 kayıt)
        $ip = $_SERVER['REMOTE_ADDR'];
        $time_limit = time() - 300; // 5 dakika
        
        $rate_check = $conn->prepare("SELECT COUNT(*) as attempt_count FROM users WHERE created_at > FROM_UNIXTIME(?) AND ip_address = ?");
        if ($rate_check) {
            $rate_check->bind_param("is", $time_limit, $ip);
            $rate_check->execute();
            $rate_result = $rate_check->get_result()->fetch_assoc();
            
            if ($rate_result['attempt_count'] >= 3) {
                error_log("Rate limit exceeded: " . $ip);
                $_SESSION['register_error'] = "Çok fazla kayıt denemesi. Lütfen 5 dakika bekleyin.";
                header('Location: index.php');
                exit();
            }
        }
        
        // 3. reCAPTCHA v3 doğrulaması - GEÇİCİ OLARAK DEVRE DIŞI
        /*
        $recaptcha_token = $_POST['recaptcha_token'] ?? '';
        if (empty($recaptcha_token)) {
            error_log("reCAPTCHA token missing from: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['register_error'] = "Güvenlik doğrulaması eksik. Lütfen tekrar deneyin.";
            header('Location: index.php');
            exit();
        }
        
        // Google reCAPTCHA doğrulama
        $recaptcha_secret = '6LEp_JIrAAAABwtbJPMGJzqjSQ8WHeffhy9TzyOny';
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $recaptcha_data = [
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($recaptcha_data)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($recaptcha_url, false, $context);
        $result = json_decode($response, true);
        
        // reCAPTCHA başarısız veya skor çok düşükse reddet
        if (!$result['success'] || (isset($result['score']) && $result['score'] < 0.5)) {
            error_log("reCAPTCHA failed - Score: " . ($result['score'] ?? 'N/A') . " IP: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['register_error'] = "Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.";
            header('Location: index.php');
            exit();
        }
        
        // DEBUG: reCAPTCHA başarılı
        error_log("reCAPTCHA success - Score: " . ($result['score'] ?? 'N/A') . " IP: " . $_SERVER['REMOTE_ADDR']);
        */
        
        // DEBUG: reCAPTCHA geçici olarak devre dışı
        error_log("reCAPTCHA bypass - IP: " . $_SERVER['REMOTE_ADDR']);
        
        // 4. Minimum form doldurma süresi (3 saniye)
        $form_start_time = intval($_SESSION['form_start_time'] ?? 0);
        if ($form_start_time > 0 && (time() - $form_start_time) < 3) {
            error_log("Bot detected - Too fast submission: " . $_SERVER['REMOTE_ADDR']);
            $_SESSION['register_error'] = "Form çok hızlı gönderildi. Lütfen tekrar deneyin.";
            header('Location: index.php');
            exit();
        }

        // Form verilerini al ve temizle
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $terms = isset($_POST['terms']);

        // Validasyonlar
        $errors = [];

        if (empty($fullname)) {
            $errors[] = "Ad soyad gereklidir.";
        }

        if (empty($email)) {
            $errors[] = "E-posta adresi gereklidir.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Geçerli bir e-posta adresi girin.";
        }

        if (empty($password)) {
            $errors[] = "Şifre gereklidir.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Şifre en az 6 karakter olmalıdır.";
        }

        if ($password !== $password_confirm) {
            $errors[] = "Şifreler eşleşmiyor.";
        }

        if (!$terms) {
            $errors[] = "Şartlar ve koşulları kabul etmelisiniz.";
        }

        // E-posta kontrolü (zaten kayıtlı mı?)
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result = $check_email->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Bu e-posta adresi zaten kayıtlı.";
        }

        // Hata varsa geri dön
        if (!empty($errors)) {
            $_SESSION['register_error'] = implode(" ", $errors);
            header('Location: index.php');
            exit();
        }

        // Aktivasyon token'ı oluştur
        $activation_token = bin2hex(random_bytes(32));
        
        // Şifreyi hash'le
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // DÜZELTME 1: verification_token sütunu kullan (activation_token değil)
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, phone, password, verification_token, is_verified, ip_address) 
            VALUES (?, ?, ?, ?, ?, 0, ?)
        ");
        
        $stmt->bind_param("ssssss", $fullname, $email, $phone, $hashed_password, $activation_token, $ip_address);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Aktivasyon e-postası gönder
            if (sendActivationEmail($email, $fullname, $activation_token)) {
                $_SESSION['register_success'] = "Kayıt başarılı! E-posta adresinize gönderilen aktivasyon linkine tıklayarak hesabınızı etkinleştirin.";
            } else {
                $_SESSION['register_success'] = "Kayıt başarılı ancak aktivasyon e-postası gönderilemedi. Lütfen destek ile iletişime geçin.";
            }
        } else {
            throw new Exception("Veritabanı hatası: " . $stmt->error);
        }

    } catch (Exception $e) {
        error_log("Register error: " . $e->getMessage());
        $_SESSION['register_error'] = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
    }
    
    header('Location: index.php');
    exit();
}

// Aktivasyon e-postası gönderme fonksiyonu
function sendActivationEmail($email, $fullname, $token) {
    $mail = new PHPMailer(true);

    try {
        // Server settings - MXRouting Configuration
        $mail->isSMTP();
        $mail->Host       = 'blizzard.mxrouting.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@gokhanaydinli.com';
        $mail->Password   = '113041sS?!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Charset ayarı
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        // Recipients - kullanıcının gerçek email adresine gönder
        $mail->setFrom('no-reply@gokhanaydinli.com', 'Gökhan Aydınlı Gayrimenkul');
        $mail->addAddress($email, $fullname);
        $mail->addReplyTo('no-reply@gokhanaydinli.com', 'Gökhan Aydınlı');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Hesap Aktivasyonu - Gökhan Aydınlı Gayrimenkul';
        
        // Doğru aktivasyon linki oluştur - gokhanaydinli.com
        $activation_link = "https://gokhanaydinli.com/activate.php?token=" . $token;
        
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
                    <h2>Merhaba " . htmlspecialchars($fullname) . ",</h2>
                    <p><strong>Gökhan Aydınlı Gayrimenkul</strong>'e kayıt olduğunuz için teşekkür ederiz!</p>
                    
                    <p>Hesabınızı etkinleştirmek için aşağıdaki butona tıklayın:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . htmlspecialchars($activation_link) . "' class='button'>
                            ✅ Hesabımı Etkinleştir
                        </a>
                    </div>
                    
                    <p>Eğer buton çalışmıyorsa, aşağıdaki linki kopyalayıp tarayıcınıza yapıştırabilirsiniz:</p>
                    <div class='link-box'>
                        " . htmlspecialchars($activation_link) . "
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Önemli:</strong> Bu aktivasyon linki <strong>24 saat</strong> geçerlidir.
                    </div>
                    
                    <p>Hesabınız etkinleştirildikten sonra şu özelliklerden yararlanabileceksiniz:</p>
                    <ul>
                        <li>🏢 Özel ticari gayrimenkul fırsatları</li>
                        <li>💬 Doğrudan iletişim imkanı</li>
                        <li>📊 Kişiselleştirilmiş öneriler</li>
                        <li>🔔 Yeni ilan bildirimleri</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p><strong>Gökhan Aydınlı Gayrimenkul</strong></p>
                    <p>Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
                    <p>Bu e-posta " . htmlspecialchars($email) . " adresine gönderilmiştir.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->AltBody = "
Merhaba $fullname,

Gökhan Aydınlı Gayrimenkul'e kayıt olduğunuz için teşekkür ederiz.

Hesabınızı etkinleştirmek için aşağıdaki linke tıklayın:
$activation_link

Bu link 24 saat geçerlidir.

Gökhan Aydınlı Gayrimenkul
Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul
";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
        return false;
    }
}

// Eğer GET isteği gelirse ana sayfaya yönlendir
header('Location: index.php');
exit();
?>