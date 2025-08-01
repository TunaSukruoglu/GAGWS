<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Database connection
require_once 'db.php';

// Raspberry Pi Mail Server Config
$mail_config = [
    'smtp_host' => 'smtp.sunucu.dev',  // Raspberry Pi SMTP
    'smtp_port' => 25,
    'smtp_user' => '',       // Kimlik doğrulama yok
    'smtp_pass' => '',       // Kimlik doğrulama yok
    'from_email' => 'noreply@sunucu.dev',
    'from_name' => 'Gökhan Aydınlı Gayrimenkul'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST isteği kabul edilir.']);
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$terms = isset($_POST['terms']);

// Validation
if (empty($fullname) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Ad Soyad, e-posta ve şifre gerekli.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Geçerli bir e-posta adresi girin.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Şifre en az 6 karakter olmalıdır.']);
    exit;
}

if ($password !== $password_confirm) {
    echo json_encode(['success' => false, 'message' => 'Şifreler eşleşmiyor.']);
    exit;
}

if (!$terms) {
    echo json_encode(['success' => false, 'message' => 'Kullanım şartlarını kabul etmelisiniz.']);
    exit;
}

try {
    // Check if connection exists
    if (isset($conn)) {
        // Use MySQLi connection
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı.']);
            exit;
        }

        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, verification_token, email_verified) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $fullname, $email, $hashed_password, $verification_token);
        
        if ($stmt->execute()) {
            // Send verification email using Raspberry Pi mail server
            if (sendVerificationEmail($email, $fullname, $verification_token, $mail_config)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Kayıt başarılı! E-posta adresinize doğrulama bağlantısı gönderildi.',
                    'requires_verification' => true
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Kayıt başarılı! Ancak doğrulama e-postası gönderilemedi. Lütfen yönetici ile iletişime geçin.',
                    'requires_verification' => true
                ]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Kayıt işlemi başarısız oldu.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantısı bulunamadı.']);
    }

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.']);
}

function sendVerificationEmail($email, $fullname, $token, $config) {
    // Check if PHPMailer is available
    if (!file_exists('PHPMailer.php')) {
        error_log("PHPMailer.php not found");
        return false;
    }

    require_once 'PHPMailer.php';

    try {
        $mail = new PHPMailer(true);
        
        // SMTP configuration for Raspberry Pi
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = false;  // Kimlik doğrulama yok
        $mail->Port = $config['smtp_port'];
        
        // Email settings
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email, $fullname);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'E-posta Doğrulama - Gökhan Aydınlı Gayrimenkul';
        
        $verification_url = "http://" . $_SERVER['HTTP_HOST'] . "/verify-email.php?token=" . $token;
        
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px;'>
                    🚀 Raspberry Pi Mail Server Test
                </h2>
                
                <p>Merhaba <strong>$fullname</strong>,</p>
                
                <p>Gökhan Aydınlı Gayrimenkul platformuna kayıt olduğunuz için teşekkür ederiz!</p>
                
                <p>🎉 <strong>Bu e-posta Raspberry Pi mail server'ınız üzerinden gönderilmektedir!</strong></p>
                
                <p>Hesabınızı aktif hale getirmek için aşağıdaki butona tıklayarak e-posta adresinizi doğrulayın:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$verification_url' style='background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                        E-postamı Doğrula
                    </a>
                </div>
                
                <p>Eğer buton çalışmıyorsa, aşağıdaki bağlantıyı kopyalayıp tarayıcınıza yapıştırabilirsiniz:</p>
                <p style='word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 3px;'>
                    $verification_url
                </p>
                
                <div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #28a745; margin: 0 0 10px 0;'>🍓 Raspberry Pi Mail Server</h4>
                    <p style='margin: 0; font-size: 14px;'>
                        Bu e-posta kendi Raspberry Pi mail server'ınızdan gönderildi. 
                        Artık bağımsız bir mail sisteminiz var!
                    </p>
                </div>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                
                <p style='font-size: 12px; color: #666;'>
                    Bu e-posta Raspberry Pi üzerinde çalışan özel mail server'ınızdan gönderilmiştir.<br>
                    © 2025 Gökhan Aydınlı Gayrimenkul - Tüm hakları saklıdır.
                </p>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>
