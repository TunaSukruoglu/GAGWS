<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']) ? $_POST['terms'] : '';

    // Boş alan kontrolü
    if (empty($fullname) || empty($email) || empty($password) || empty($password_confirm)) {
        $_SESSION['register_error'] = "❌ Lütfen tüm zorunlu alanları doldurun.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Şartlar kontrolü
    if (empty($terms)) {
        $_SESSION['register_error'] = "❌ Şartlar ve koşulları kabul etmelisiniz.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Şifre eşleşme kontrolü
    if ($password !== $password_confirm) {
        $_SESSION['register_error'] = "❌ Şifreler eşleşmiyor.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Email format kontrolü
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "❌ Geçerli bir e-posta adresi girin.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        // E-posta adresinin kayıtlı olup olmadığını kontrol et
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['register_error'] = "❌ Bu e-posta adresi zaten kayıtlı.";
            $check_stmt->close();
            $conn->close();
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        $check_stmt->close();

        // Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Onay token'ı oluştur
        $verification_token = bin2hex(random_bytes(32));

        // Önce tablo yapısını kontrol et
        $columns_result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_verified'");
        if ($columns_result->num_rows == 0) {
            // Eğer is_verified alanı yoksa, basit kayıt yap
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_approved) VALUES (?, ?, ?, 'user', 1)");
            $stmt->bind_param("sss", $fullname, $email, $hashed_password);
        } else {
            // is_verified alanı varsa, email onayı ile kayıt yap
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_approved, is_verified, verification_token) VALUES (?, ?, ?, 'user', 1, 0, ?)");
            $stmt->bind_param("ssss", $fullname, $email, $hashed_password, $verification_token);
        }

        if ($stmt->execute()) {
            $stmt->close();
            
            // Email göndermeyi dene, başarısız olsa bile kayıt tamamlandı
            $email_sent = false;
            
            if (file_exists('PHPMailer.php') && isset($verification_token)) {
                try {
                    require_once 'PHPMailer.php';
                    require_once 'SMTP.php';  
                    require_once 'Exception.php';

                    use PHPMailer\PHPMailer\PHPMailer;
                    use PHPMailer\PHPMailer\SMTP;
                    use PHPMailer\PHPMailer\Exception;

                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'localhost';
                    $mail->SMTPAuth = false;
                    $mail->Port = 25;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('no-reply@sunucu.dev', 'Gökhan Aydınlı Gayrimenkul');
                    $mail->addAddress($email, $fullname);

                    $mail->isHTML(true);
                    $mail->Subject = '📧 Email Adresinizi Onaylayın - Gökhan Aydınlı Gayrimenkul';
                    
                    $verification_link = "https://sunucu.dev/GokhanAydinli/activate.php?token=" . $verification_token;
                    
                    $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h2>🏡 Hoş Geldiniz!</h2>
                        <p>Merhaba <strong>' . htmlspecialchars($fullname) . '</strong>,</p>
                        <p>Gökhan Aydınlı Gayrimenkul\'e kayıt olduğunuz için teşekkür ederiz!</p>
                        <p>Hesabınızı aktifleştirmek için aşağıdaki linke tıklayın:</p>
                        <p><a href="' . $verification_link . '" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">📧 Email Adresimi Onayla</a></p>
                        <p>İyi günler dileriz!</p>
                        <hr>
                        <small>© 2025 Gökhan Aydınlı Gayrimenkul</small>
                    </div>';

                    $mail->send();
                    $email_sent = true;
                    
                } catch (Exception $e) {
                    error_log("Email send error: " . $e->getMessage());
                }
            }
            
            if ($email_sent) {
                $_SESSION['register_success'] = "✅ Kayıt başarılı! Email adresinize gönderilen onay linkine tıklayarak hesabınızı aktifleştirin.";
            } else {
                $_SESSION['register_success'] = "✅ Kayıt başarılı! Hesabınız aktif, giriş yapabilirsiniz.";
            }
            
        } else {
            $_SESSION['register_error'] = "❌ Kayıt sırasında bir hata oluştu: " . $conn->error;
        }
        
    } catch (Exception $e) {
        $_SESSION['register_error'] = "❌ Kayıt sırasında bir hata oluştu.";
        error_log("Register error: " . $e->getMessage());
    }

    $conn->close();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
