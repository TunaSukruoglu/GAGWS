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

$message = '';
$success = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Token'ı veritabanında ara (verification_token sütunu kullan)
        $stmt = $conn->prepare("
            SELECT id, name, email, created_at 
            FROM users 
            WHERE verification_token = ? AND is_verified = 0
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 24 saat kontrolü
            $created_time = strtotime($user['created_at']);
            $current_time = time();
            $time_diff = $current_time - $created_time;
            
            if ($time_diff > 86400) { // 24 saat = 86400 saniye
                $message = 'Aktivasyon linki süresi dolmuş. Lütfen tekrar kayıt olun.';
                
                // Süresi dolmuş kullanıcıyı sil
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_stmt->bind_param("i", $user['id']);
                $delete_stmt->execute();
                
            } else {
                // Hesabı etkinleştir (verification_token kullan)
                $update_stmt = $conn->prepare("
                    UPDATE users 
                    SET is_verified = 1, verification_token = NULL, verified_at = NOW() 
                    WHERE id = ?
                ");
                $update_stmt->bind_param("i", $user['id']);
                
                if ($update_stmt->execute()) {
                    $success = true;
                    $message = 'Tebrikler! Hesabınız başarıyla etkinleştirildi. Artık giriş yapabilirsiniz.';
                    
                    // Session'a başarı mesajı koy
                    $_SESSION['login_success'] = 'Hesabınız etkinleştirildi! Giriş yapabilirsiniz.';
                } else {
                    $message = 'Aktivasyon sırasında bir hata oluştu. Lütfen tekrar deneyin.';
                }
            }
        } else {
            $message = 'Geçersiz aktivasyon linki veya hesap zaten etkinleştirilmiş.';
        }
        
    } catch (Exception $e) {
        error_log("Activation error: " . $e->getMessage());
        $message = 'Aktivasyon sırasında bir hata oluştu.';
    }
} else {
    $message = 'Geçersiz aktivasyon linki.';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Aktivasyonu - Gökhan Aydınlı Gayrimenkul</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #15B97C, #0d8c5a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }
        .activation-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        .icon-success {
            width: 80px;
            height: 80px;
            background: #15B97C;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }
        .icon-error {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 36px;
        }
        .btn-custom {
            background: #15B97C;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-custom:hover {
            background: #0d8c5a;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="activation-card">
            <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" class="logo">
            
            <?php if ($success): ?>
                <div class="icon-success">
                    <i class="fas fa-check"></i>
                </div>
                <h2 class="text-success mb-3">Aktivasyon Başarılı!</h2>
            <?php else: ?>
                <div class="icon-error">
                    <i class="fas fa-times"></i>
                </div>
                <h2 class="text-danger mb-3">Aktivasyon Hatası</h2>
            <?php endif; ?>
            
            <p class="fs-18 mb-4 text-muted"><?= htmlspecialchars($message) ?></p>
            
            <?php if ($success): ?>
                <a href="index.php" class="btn-custom">
                    <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                </a>
            <?php else: ?>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="index.php" class="btn-custom">
                        <i class="fas fa-home me-2"></i>Ana Sayfa
                    </a>
                    <a href="contact.php" class="btn btn-outline-secondary">
                        <i class="fas fa-envelope me-2"></i>Destek
                    </a>
                </div>
            <?php endif; ?>
            
            <hr class="my-4">
            <small class="text-muted">
                Sorularınız için: <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a>
            </small>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>