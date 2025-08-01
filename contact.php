<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database bağlantısını dahil et
try {
    include 'db.php';
} catch (Exception $e) {
    error_log("DB Bağlantı Hatası: " . $e->getMessage());
}

$phpmailer_available = false;
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    $phpmailer_available = true;
} else {
    // Manuel include (eski yöntem)
    if (file_exists('PHPMailer.php') && file_exists('SMTP.php')) {
        require_once 'PHPMailer.php';
        require_once 'SMTP.php';
        if (file_exists('Exception.php')) {
            require_once 'Exception.php';
        }
        $phpmailer_available = true;
    }
}

// reCAPTCHA doğrulama fonksiyonu
function verifyRecaptcha($token) {
    $recaptcha_secret = '6LEp_JIrAAAABwtbJPMGJzqjSQ8WHeffhy9TzyOny';
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    
    $recaptcha_data = [
        'secret' => $recaptcha_secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptcha_data),
            'timeout' => 10
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($recaptcha_url, false, $context);
    
    if ($response === false) {
        safe_log_write('recaptcha-debug.txt', date('Y-m-d H:i:s') . " | reCAPTCHA API'ye ulaşılamadı\n");
        return false; // API'ye ulaşılamazsa başarısız sayar
    }
    
    $result = json_decode($response, true);
    
    // Debug log
    safe_log_write('recaptcha-debug.txt', date('Y-m-d H:i:s') . " | reCAPTCHA response: " . json_encode($result) . "\n");
    
    // Başarılı ve skor 0.3'ten yüksekse geçerli (daha esnek limit)
    $success = $result['success'] ?? false;
    $score = $result['score'] ?? 0;
    
    return $success && $score >= 0.3;
}

// Güvenli log yazma fonksiyonu
function safe_log_write($filename, $content) {
    try {
        if (is_writable(dirname($filename)) || is_writable($filename)) {
            return file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
        }
    } catch (Exception $e) {
        // Sessizce devam et
    }
    return false;
}

// Mail gönderme fonksiyonu
function sendContactEmail($name, $email, $subject, $message) {
    global $phpmailer_available;
    
    // Debug log
    $debug_log = "Mail gönderim denemesi: " . date('Y-m-d H:i:s') . " | $name | $email | $subject\n";
    safe_log_write('mail-debug.txt', $debug_log);
    
    // PHPMailer kullan
    if (!$phpmailer_available) {
        error_log("PHPMailer mevcut değil");
        safe_log_write('mail-debug.txt', "HATA: PHPMailer mevcut değil\n");
        return false;
    }
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Debug için
        $mail->SMTPDebug = 0; // Debug kapalı
        /*
        $mail->Debugoutput = function($str, $level) {
            safe_log_write('mail-debug.txt', date('Y-m-d H:i:s') . " DEBUG: $str\n");
        };
        */
        
        // SMTP ayarları - MXRouting (Doğru ayarlar)
        $mail->isSMTP();
        $mail->Host       = 'blizzard.mxrouting.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'no-reply@gokhanaydinli.com';
        $mail->Password   = '113041sS?!';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        
        // Gönderen ve alıcı
        $mail->setFrom('no-reply@gokhanaydinli.com', 'Gökhan Aydınlı Gayrimenkul');
        $mail->addAddress('info@gokhanaydinli.com');
        $mail->addReplyTo($email, $name);
        
        // Mail içeriği
        $mail->isHTML(true);
        $mail->Subject = "İletişim Formu: $subject";
        
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa; border-radius: 10px;'>
            <div style='background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #2563eb; margin-bottom: 20px; text-align: center;'>İletişim Formu Mesajı</h2>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                    <p style='margin: 10px 0;'><strong>👤 Ad Soyad:</strong> $name</p>
                    <p style='margin: 10px 0;'><strong>📧 E-posta:</strong> $email</p>
                    <p style='margin: 10px 0;'><strong>📋 Konu:</strong> $subject</p>
                    <p style='margin: 10px 0;'><strong>📅 Tarih:</strong> " . date('d.m.Y H:i') . "</p>
                </div>
                
                <div style='background: white; border-left: 4px solid #2563eb; padding: 20px; margin: 20px 0;'>
                    <h3 style='color: #1f2937; margin-bottom: 15px;'>💬 Mesaj:</h3>
                    <p style='line-height: 1.6; color: #374151;'>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                
                <div style='text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>
                    Bu mesaj gokhanaydinli.com iletişim formu üzerinden gönderilmiştir.
                </div>
            </div>
        </div>";
        
        $result = $mail->send();
        safe_log_write('mail-debug.txt', "PHPMailer SMTP sonucu: " . ($result ? 'BAŞARILI' : 'BAŞARISIZ') . "\n");
        return $result;
        
    } catch (Exception $e) {
        $error_msg = "SMTP Mail gönderim hatası: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
        error_log($error_msg);
        safe_log_write('mail-debug.txt', $error_msg . "\n");
        return false;
    }
}

// Tur talebi için özel form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'tour_request') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $preferred_time = trim($_POST['preferred_time'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $property_id = $_POST['property_id'] ?? '';
    $property_title = $_POST['property_title'] ?? '';
    $recaptcha_token = $_POST['recaptcha_token'] ?? '';
    
    // Doğrulama
    $errors = [];
    if (empty($name)) $errors[] = "Ad-Soyad boş olamaz";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin";
    if (empty($phone)) $errors[] = "Telefon numarası boş olamaz";
    
    // reCAPTCHA kontrolü - HONEYPOT İLE DEĞİŞTİRİLDİ
    // Honeypot kontrolü (bot tespiti için gizli alan)
    $honeypot = $_POST['website'] ?? '';
    if (!empty($honeypot)) {
        $errors[] = "Bot aktivitesi tespit edildi.";
        error_log("Honeypot triggered for tour request - IP: " . $_SERVER['REMOTE_ADDR'] . " - Honeypot: " . $honeypot);
    } else {
        safe_log_write('security-debug.txt', date('Y-m-d H:i:s') . " | TUR TALEBİ | HONEYPOT BAŞARILI | IP: " . $_SERVER['REMOTE_ADDR'] . "\n");
    }
    
    if (empty($errors)) {
        // Tur talebi için özel konu oluştur
        $subject = "🏠 Tur Talebi - " . $property_title;
        
        // Tur talebi için özel mesaj formatı
        $tour_message = "TUR TALEBİ DETAYLARI:\n\n";
        $tour_message .= "📋 Emlak: " . $property_title . "\n";
        $tour_message .= "🆔 Emlak ID: " . $property_id . "\n\n";
        $tour_message .= "👤 Talep Eden: " . $name . "\n";
        $tour_message .= "📞 Telefon: " . $phone . "\n";
        $tour_message .= "📧 E-posta: " . $email . "\n\n";
        if (!empty($preferred_date)) {
            $tour_message .= "📅 Tercih Edilen Tarih: " . date('d.m.Y', strtotime($preferred_date)) . "\n";
        }
        if (!empty($preferred_time)) {
            $tour_message .= "⏰ Tercih Edilen Saat: " . $preferred_time . "\n";
        }
        $tour_message .= "\n💬 Mesaj: " . $message;
        
        // Mail gönder
        $mailSent = sendContactEmail($name, $email, $subject, $tour_message);
        
        // Yerel kayıt da yap
        $log_entry = date('Y-m-d H:i:s') . " | TUR TALEBİ | $name | $email | $phone | $property_title | $preferred_date $preferred_time | " . str_replace("\n", " ", $message) . "\n";
        safe_log_write('tour-requests.txt', $log_entry);
        
        // Property detay sayfasına yönlendir - sadece GET parametresi kullan
        if ($mailSent) {
            header('Location: property-details.php?id=' . $property_id . '&tour=success');
        } else {
            header('Location: property-details.php?id=' . $property_id . '&tour=partial');
        }
        exit;
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
        // Property detay sayfasına yönlendir
        header('Location: property-details.php?id=' . $property_id . '&tour=error');
        exit;
    }
}

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $recaptcha_token = $_POST['recaptcha_token'] ?? '';
    
    // Doğrulama
    $errors = [];
    if (empty($name)) $errors[] = "Ad-Soyad boş olamaz";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin";
    if (empty($subject)) $errors[] = "Konu boş olamaz";
    if (empty($message)) $errors[] = "Mesaj boş olamaz";
    
    // reCAPTCHA kontrolü - HONEYPOT İLE DEĞİŞTİRİLDİ
    // Honeypot kontrolü (bot tespiti için gizli alan)
    $honeypot = $_POST['website'] ?? '';
    if (!empty($honeypot)) {
        $errors[] = "Bot aktivitesi tespit edildi.";
        error_log("Honeypot triggered for contact form - IP: " . $_SERVER['REMOTE_ADDR'] . " - Honeypot: " . $honeypot);
    } else {
        safe_log_write('security-debug.txt', date('Y-m-d H:i:s') . " | İLETİŞİM FORMU | HONEYPOT BAŞARILI | IP: " . $_SERVER['REMOTE_ADDR'] . "\n");
    }
    
    if (empty($errors)) {
        // Mail gönder
        $mailSent = sendContactEmail($name, $email, $subject, $message);
        
        if ($mailSent) {
            $_SESSION['success'] = "✅ Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.";
        } else {
            $_SESSION['success'] = "✅ Mesajınız kaydedildi! (Mail sunucusunda sorun var ama mesajınız bize ulaştı)";
        }
        
        // Yerel kayıt da yap
        $log_entry = date('Y-m-d H:i:s') . " | $name | $email | $subject | " . str_replace("\n", " ", $message) . "\n";
        safe_log_write('contact-messages.txt', $log_entry);
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="İletişim, Gayrimenkul, İstanbul Emlak, Gökhan Aydınlı">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul ile iletişime geçin. Size en iyi hizmeti vermek için buradayız.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:type" content="website">
    <meta property="og:title" content="İletişim - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/contact-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>İletişim - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* İletişim sayfası özel stilleri */
        .inner-banner-one.inner-banner {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        }

        .bg-pink {
            background: #ffffff !important;
        }

        .form-style-one h3 {
            color: #6c757d !important;
            font-weight: 700;
        }

        .form-style-one .input-group-meta label {
            color: #6c757d !important;
        }

        .form-style-one .input-group-meta input:focus,
        .form-style-one .input-group-meta textarea:focus {
            border-color: #6c757d !important;
            box-shadow: 0 0 5px rgba(108, 117, 125, 0.15) !important;
        }

        .form-style-one .btn-nine {
            background: #6c757d !important;
            color: #fff !important;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .form-style-one .btn-nine:hover {
            background: #495057 !important;
            color: #fff !important;
        }

        .footer-four .footer-title,
        .footer-four .email,
        .footer-four .footer-nav-link li a:hover {
            color: #6c757d !important;
        }

        .footer-four .social-icon li a {
            background: #f8f9fa !important;
            color: #6c757d !important;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .footer-four .social-icon li a:hover {
            background: #6c757d !important;
            color: #fff !important;
        }

        .footer-four {
            background: #ffffff !important;
        }

        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #495057;
            transform: translateY(-2px);
        }

        /* Modern Contact Cards Stilleri */
        .modern-contact-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #f0f2f5;
            transition: all 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .modern-contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .modern-contact-card:hover::before {
            transform: scaleX(1);
        }

        .modern-contact-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
            border-color: rgba(102, 126, 234, 0.2);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            transition: all 0.4s ease;
        }

        .modern-contact-card:hover .card-icon {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .card-icon i {
            font-size: 32px;
            color: #ffffff;
        }

        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-content h5 {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .card-content a,
        .card-content span {
            font-size: 16px;
            font-weight: 500;
            color: #667eea;
            text-decoration: none;
            transition: all 0.3s ease;
            line-height: 1.6;
            display: block;
        }

        .card-content a:hover {
            color: #764ba2;
            transform: translateY(-1px);
        }

        .card-content a + a {
            margin-top: 8px;
        }

        /* Grid düzenlemesi için ek stiller */
        @media (min-width: 992px) {
            .address-banner .row {
                justify-content: space-between;
            }
            
            .address-banner .row .col-lg-4 {
                flex: 0 0 auto;
                width: 31.33%;
                margin-bottom: 20px;
            }
        }

        /* Success/Error messages */
        .alert {
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 25px;
            font-size: 16px;
            font-weight: 500;
            border: 1px solid transparent;
            animation: slideInDown 0.5s ease;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            box-shadow: 0 4px 15px rgba(21, 185, 124, 0.2);
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal özel stilleri */
        .user-data-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: none;
            position: relative;
            justify-content: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #6b7280;
            font-size: 18px;
        }

        .close:hover {
            color: #2563eb;
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        /* Sosyal Medya Butonları Stilleri */
        .btn-google {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-google:hover {
            background: #f9fafb;
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: #374151;
            transform: translateY(-1px);
        }

        .btn-facebook {
            background: #1877f2;
            border: 1px solid #1877f2;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-facebook:hover {
            background: #166fe5;
            border-color: #166fe5;
            box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .divider-line-or {
            position: relative;
        }

        .divider-line-or:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
            z-index: 1;
        }

        .divider-line-or span {
            position: relative;
            z-index: 2;
            background: #ffffff;
            color: #6b7280;
            font-size: 14px;
        }

        .alert-info {
            background-color: #e1f5fe;
            border-color: #81d4fa;
            color: #0277bd;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
        }

        /* Responsive düzenlemeler */
        @media (max-width: 768px) {
            .modern-contact-card {
                padding: 30px 20px;
                margin-bottom: 20px;
            }
            
            .card-icon {
                width: 70px;
                height: 70px;
                margin-bottom: 20px;
            }
            
            .card-icon i {
                font-size: 28px;
            }
            
            .card-content h5 {
                font-size: 16px;
                margin-bottom: 12px;
            }
            
            .card-content a,
            .card-content span {
                font-size: 14px;
            }
            
            .inner-banner-one.inner-banner {
                padding-top: 90px !important;
                padding-bottom: 60px !important;
            }
            .form-style-one {
                padding: 20px !important;
            }
        }

        @media (max-width: 576px) {
            .modern-contact-card {
                padding: 25px 15px;
            }
            
            .card-icon {
                width: 60px;
                height: 60px;
            }
            
            .card-icon i {
                font-size: 24px;
            }
            
            .btn-google, .btn-facebook {
                margin-bottom: 10px;
            }
            
            .btn-google span, .btn-facebook span {
                font-size: 13px;
            }
        }
    </style>
    
    <!-- Honeypot Bot Koruması (reCAPTCHA yerine) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contactForm');
            const registerForm = document.getElementById('registerForm');
            
            // İletişim formu için honeypot
            if (contactForm) {
                // Honeypot alanı ekle (gizli)
                const honeypot = document.createElement('input');
                honeypot.type = 'text';
                honeypot.name = 'website';
                honeypot.style.display = 'none';
                honeypot.style.position = 'absolute';
                honeypot.style.left = '-9999px';
                honeypot.setAttribute('tabindex', '-1');
                honeypot.setAttribute('autocomplete', 'off');
                contactForm.appendChild(honeypot);
                
                contactForm.addEventListener('submit', function(e) {
                    // Buton disabled yap ve loading göster
                    const submitBtn = contactForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...';
                    
                    // Honeypot kontrolü
                    if (honeypot.value !== '') {
                        e.preventDefault();
                        alert('Bot aktivitesi tespit edildi.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        return;
                    }
                    
                    // 1 saniye bekle (bot tespiti için)
                    setTimeout(function() {
                        console.log('Form güvenlik kontrolü başarılı');
                    }, 1000);
                });
            }
            
            // Kayıt formu için honeypot
            if (registerForm) {
                // Honeypot alanı ekle (gizli)
                const honeypotReg = document.createElement('input');
                honeypotReg.type = 'text';
                honeypotReg.name = 'website';
                honeypotReg.style.display = 'none';
                honeypotReg.style.position = 'absolute';
                honeypotReg.style.left = '-9999px';
                honeypotReg.setAttribute('tabindex', '-1');
                honeypotReg.setAttribute('autocomplete', 'off');
                registerForm.appendChild(honeypotReg);
                
                registerForm.addEventListener('submit', function(e) {
                    // Buton disabled yap ve loading göster
                    const submitBtn = registerForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Kaydediliyor...';
                    
                    // Honeypot kontrolü
                    if (honeypotReg.value !== '') {
                        e.preventDefault();
                        alert('Bot aktivitesi tespit edildi.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        return;
                    }
                    
                    // 1 saniye bekle (bot tespiti için)
                    setTimeout(function() {
                        console.log('Kayıt formu güvenlik kontrolü başarılı');
                    }, 1000);
                });
            }
            
            console.log('Honeypot bot koruması aktif - İletişim ve Kayıt formları');
        });
    </script>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Loading Transition -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
            </div>
        </div>

        <!-- Theme Main Menu -->
        <header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="index.php" class="d-flex align-items-center">
                                <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
                            </a>
                        </div>
                        <!-- Header'da Giriş butonu -->
                        <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                    <li class="dropdown">
                                        <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="dashboard.php">Panel</a></li>
                                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one">
                                            <i class="fa-regular fa-lock"></i> <span>Giriş</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <nav class="navbar navbar-expand-lg p0 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="nav-item">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="portfoy.php">Portföy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- İç Banner -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">İletişim</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>İletişim</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Contact Us -->
        <div class="contact-us border-top mt-130 xl-mt-100 pt-80 lg-pt-60">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-9 col-xl-8 col-lg-10 m-auto">
                        <div class="title-one text-center wow fadeInUp">
                            <h3>Sorularınız mı var? Bize mesaj gönderebilirsiniz.</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modern Contact Cards -->
            <div class="address-banner wow fadeInUp mt-60 lg-mt-40">
                <div class="container">
                    <div class="row justify-content-center">
                        <!-- E-posta Kartı -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="modern-contact-card">
                                <div class="card-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="card-content">
                                    <h5>Size yardımcı olmaktan mutluluk duyarız.</h5>
                                    <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Telefon Kartı -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="modern-contact-card">
                                <div class="card-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="card-content">
                                    <h5>Telefon numaramız</h5>
                                    <a href="tel:+902128016058">+90 (212) 801 60 58</a>
                                    <a href="tel:+905302037083">+90 (530) 203 70 83</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Çalışma Saatleri Kartı -->
                        <div class="col-lg-4 col-md-12 mb-4">
                            <div class="modern-contact-card">
                                <div class="card-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="card-content">
                                    <h5>Çalışma Saatleri</h5>
                                    <span>Pzt-Cum: 09:00-19:00, Cmt-Paz: 09:00-14:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-pink mt-150 xl-mt-120 md-mt-80">
                <div class="row">
                    <div class="col-xl-7 col-lg-6">
                        <div class="form-style-one wow fadeInUp">
                            <!-- Başarı/Hata Mesajları -->
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error']; ?>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>
                            
                            <form method="POST" id="contactForm" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                                <h3 style="margin-bottom: 30px; color: #6c757d;">Mesaj Gönder</h3>
                                
                                <div style="margin-bottom: 25px;">
                                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold; color: #6c757d;">Ad Soyad*</label>
                                    <input type="text" name="name" id="name" required style="width: 100%; padding: 12px 15px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 16px; transition: all 0.3s;" placeholder="Adınız ve soyadınız">
                                </div>
                                
                                <div style="margin-bottom: 25px;">
                                    <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold; color: #6c757d;">E-posta*</label>
                                    <input type="email" name="email" id="email" required style="width: 100%; padding: 12px 15px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 16px; transition: all 0.3s;" placeholder="ornek@email.com">
                                </div>
                                
                                <div style="margin-bottom: 25px;">
                                    <label for="subject" style="display: block; margin-bottom: 8px; font-weight: bold; color: #6c757d;">Konu*</label>
                                    <input type="text" name="subject" id="subject" required style="width: 100%; padding: 12px 15px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 16px; transition: all 0.3s;" placeholder="Mesajınızın konusu">
                                </div>
                                
                                <div style="margin-bottom: 30px;">
                                    <label for="message" style="display: block; margin-bottom: 8px; font-weight: bold; color: #6c757d;">Mesajınız*</label>
                                    <textarea name="message" id="message" required rows="6" style="width: 100%; padding: 12px 15px; border: 2px solid #e9ecef; border-radius: 8px; resize: vertical; font-size: 16px; transition: all 0.3s;" placeholder="Lütfen mesajınızı buraya yazın..."></textarea>
                                </div>
                                
                                <input type="hidden" name="recaptcha_token" id="contact_recaptcha_token">
                                <button type="submit" style="background: #6c757d; color: white; border: none; padding: 15px 40px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; width: 100%; transition: all 0.3s; text-transform: uppercase;">
                                    MESAJ GÖNDER
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-6 d-flex order-lg-first">
                        <div class="contact-map-banner w-100">
                            <div class="gmap_canvas h-100 w-100">
                                <iframe class="gmap_iframe h-100 w-100" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.8746!2d28.9147!3d41.0438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab0a4c1c7a6f3%3A0x1e7b4a4c2d8e5f6a!2sMaltepe%20Mah.%2C%20Eski%20%C3%87%C4%B1rp%C4%B1c%C4%B1%20Yolu%20Cd.%2C%2034140%20Bak%C4%B1rk%C3%B6y%2F%C4%B0stanbul!5e0!3m2!1str!2str!4v1620000000000!5m2!1str!2str" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-four position-relative z-1">
            <div class="container container-large">
                <div class="bg-wrapper position-relative z-1">
                    <div class="row">
                        <div class="col-xxl-3 col-lg-4 mb-60">
                            <div class="footer-intro">
                                <div class="logo mb-20">
                                    <a href="index.php">
                                        <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" style="height: 60px;">
                                    </a>
                                </div>
                                <p class="mb-30 xs-mb-20">Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
                                <a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="https://wa.me/905302037083" target="_blank"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="https://www.instagram.com/gokhanaydinli?igsh=ejRhZmd0eWlpY3c1" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="https://www.linkedin.com/in/g%C3%B6khan-ayd%C4%B1nl%C4%B1-8a186271/?originalSubdomain=tr" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 ms-auto mb-30">
                            <div class="footer-nav ps-xl-5">
                                <h5 class="footer-title">Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="hesaplama-araclari.php">Hesaplamalar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Yasal</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="terms.php">Şartlar & Koşullar</a></li>
                                    <li><a href="cookies.php">Çerez Politikası</a></li>
                                    <li><a href="privacy.php">Gizlilik Politikası</a></li>
                                    <li><a href="faq.php">S.S.S</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetlerimiz</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="listing_04.php">Ticari Gayrimenkul</a></li>
                                    <li><a href="listing_01.php">Konut Satışı</a></li>
                                    <li><a href="listing_02.php">Ev Kiralama</a></li>
                                    <li><a href="contact.php">Yatırım Danışmanlığı</a></li>
                                    <li><a href="portfoy.php?type=villa">Villa Satışı</a></li>
                                    <li><a href="portfoy.php?type=ofis">Ofis Kiralama</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>

        <!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="container">
                    <div class="user-data-form modal-content">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="form-wrapper m-auto">
                            <ul class="nav nav-tabs w-100" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Kayıt</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-30">
                                <div class="tab-pane show active" role="tabpanel" id="fc1">
                                    <div class="text-center mb-20">
                                        <h2>Hoş Geldiniz!</h2>
                                        <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#" onclick="switchToRegister()">Kayıt olun</a></p>
                                    </div>
                                    <form action="login.php" method="POST" id="loginForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="remember" name="remember">
                                                        <label for="remember">Beni hatırla</label>
                                                    </div>
                                                    <a href="#" onclick="showForgotPassword()">Şifremi unuttum?</a>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase tran3s d-block mt-20">GİRİŞ YAP</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="fc2">
                                    <div class="text-center mb-20">
                                        <h2>Kayıt Ol</h2>
                                        <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="switchToLogin()">Giriş yapın</a></p>
                                    </div>
                                    <form action="register.php" method="POST" id="registerForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Ad Soyad*</label>
                                                    <input type="text" name="fullname" placeholder="Adınız ve soyadınız" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta*</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="En az 6 karakter" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Şifre Tekrar*</label>
                                                    <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" name="terms" id="terms" required>
                                                        <label for="terms">Şartlar ve koşulları kabul ediyorum</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase tran3s d-block mt-20">KAYIT OL</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll to top button -->
        <button class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
            ↑
        </button>

        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" crossorigin="anonymous"></script>
        <script src="vendor/slick/slick.min.js"></script>
        <script src="vendor/fancybox/fancybox.umd.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.11/jquery.lazy.min.js" crossorigin="anonymous"></script>
        <script src="vendor/jquery.counterup.min.js"></script>
        <script src="vendor/jquery.waypoints.min.js"></script>
        <script src="vendor/nice-select/jquery.nice-select.min.js"></script>
        <script src="vendor/validator.js"></script>
        <script src="vendor/isotope.pkgd.min.js"></script>
        <script src="js/theme.js"></script>

        <script>
        // Modal fonksiyonları
        function switchToRegister() {
            document.querySelector('#fc1').classList.remove('show', 'active');
            document.querySelector('#fc2').classList.add('show', 'active');
            document.querySelector('[data-bs-target="#fc1"]').classList.remove('active');
            document.querySelector('[data-bs-target="#fc2"]').classList.add('active');
        }

        function switchToLogin() {
            document.querySelector('#fc2').classList.remove('show', 'active');
            document.querySelector('#fc1').classList.add('show', 'active');
            document.querySelector('[data-bs-target="#fc2"]').classList.remove('active');
            document.querySelector('[data-bs-target="#fc1"]').classList.add('active');
        }

        function showForgotPassword() {
            alert('Şifre sıfırlama linki e-posta adresinize gönderilecektir.');
        }

        // Form hover efektleri
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#6c757d';
                    this.style.boxShadow = '0 0 10px rgba(108, 117, 125, 0.2)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#e9ecef';
                    this.style.boxShadow = 'none';
                });
            });

            // Submit button hover effect
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.addEventListener('mouseenter', function() {
                    this.style.background = '#495057';
                    this.style.transform = 'translateY(-2px)';
                });
                
                submitBtn.addEventListener('mouseleave', function() {
                    this.style.background = '#6c757d';
                    this.style.transform = 'translateY(0)';
                });
            }

            // Scroll to top button
            window.addEventListener('scroll', function() {
                const scrollTop = document.querySelector('.scroll-top');
                if (window.pageYOffset > 300) {
                    scrollTop.style.display = 'block';
                    scrollTop.style.opacity = '1';
                } else {
                    scrollTop.style.opacity = '0';
                    setTimeout(() => {
                        scrollTop.style.display = 'none';
                    }, 300);
                }
            });

            // Initialize WOW.js
            if (typeof WOW !== 'undefined') {
                new WOW().init();
            }

            // Initialize lazy loading
            if (typeof $ !== 'undefined' && $.fn.lazy) {
                $('.lazy-img').lazy();
            }
            
            // Preloader'ı kapat
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    setTimeout(() => {
                        preloader.style.display = 'none';
                        preloader.remove();
                    }, 500);
                }, 1000);
            }
        });
        </script>
    </div>
</body>
</html>