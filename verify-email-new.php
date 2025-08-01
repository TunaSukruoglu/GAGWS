<?php
session_start();
include 'db.php';

$message = '';
$status = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Token'ı veritabanında ara
        $stmt = $conn->prepare("SELECT id, name, email, is_verified, created_at FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Token'ın 24 saatlik süresini kontrol et
            $created_time = strtotime($user['created_at']);
            $current_time = time();
            $time_diff = $current_time - $created_time;
            
            if ($time_diff > 86400) { // 24 saat = 86400 saniye
                $status = 'expired';
                $message = "Doğrulama linki süresi dolmuş. Lütfen yeni bir kayıt yapın.";
            } else {
                // Kullanıcıyı doğrula
                $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verified_at = NOW(), is_approved = 1, verification_token = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                
                if ($update_stmt->execute()) {
                    $status = 'success';
                    $message = "Tebrikler " . htmlspecialchars($user['name']) . "! Email adresiniz başarıyla doğrulandı. Artık sisteme giriş yapabilirsiniz.";
                    
                    // Session'a başarı mesajı ekle
                    $_SESSION['login_success'] = "Email doğrulaması tamamlandı! Giriş yapabilirsiniz.";
                } else {
                    $status = 'error';
                    $message = "Doğrulama sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                }
                $update_stmt->close();
            }
        } else {
            $status = 'invalid';
            $message = "Geçersiz doğrulama linki veya email adresi zaten doğrulanmış.";
        }
        $stmt->close();
        
    } catch (Exception $e) {
        $status = 'error';
        $message = "Doğrulama sırasında bir hata oluştu.";
        error_log("Email verification error: " . $e->getMessage());
    }
} else {
    $status = 'missing';
    $message = "Doğrulama token'ı bulunamadı.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📧 Email Doğrulama - Gökhan Aydınlı Gayrimenkul</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; 
            margin: 0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .container { 
            background: white; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            padding: 40px; 
            max-width: 600px; 
            width: 90%; 
            text-align: center;
        }
        .icon { 
            font-size: 4em; 
            margin-bottom: 20px; 
        }
        .success { color: #27ae60; }
        .error { color: #e74c3c; }
        .expired { color: #f39c12; }
        .invalid { color: #e67e22; }
        .missing { color: #9b59b6; }
        
        h1 { 
            color: #333; 
            margin: 0 0 20px 0; 
            font-size: 2em; 
        }
        .message { 
            font-size: 1.1em; 
            line-height: 1.6; 
            margin: 30px 0; 
            color: #555;
        }
        .buttons { 
            margin: 30px 0; 
        }
        .btn { 
            display: inline-block; 
            padding: 15px 30px; 
            margin: 10px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-weight: 600; 
            transition: transform 0.2s;
        }
        .btn:hover { 
            transform: translateY(-2px); 
        }
        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
        }
        .btn-secondary { 
            background: #6c757d; 
            color: white; 
        }
        .btn-success { 
            background: #28a745; 
            color: white; 
        }
        .info-box { 
            background: #f8f9fa; 
            border-left: 4px solid #007bff; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 5px; 
            text-align: left;
        }
        .links { 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #eee; 
        }
        .links a { 
            color: #667eea; 
            text-decoration: none; 
            margin: 0 15px; 
        }
        .links a:hover { 
            text-decoration: underline; 
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($status === 'success'): ?>
            <div class="icon success">✅</div>
            <h1>Email Doğrulandı!</h1>
            <div class="message success"><?= $message ?></div>
            <div class="buttons">
                <a href="login.php" class="btn btn-success">🔑 Giriş Yap</a>
                <a href="index.php" class="btn btn-primary">🏠 Ana Sayfa</a>
            </div>
            
        <?php elseif ($status === 'expired'): ?>
            <div class="icon expired">⏰</div>
            <h1>Link Süresi Doldu</h1>
            <div class="message expired"><?= $message ?></div>
            <div class="info-box">
                <strong>ℹ️ Bilgi:</strong> Doğrulama linkleri güvenlik nedeniyle 24 saat boyunca geçerlidir.
            </div>
            <div class="buttons">
                <a href="register-form.php" class="btn btn-primary">🔄 Yeni Kayıt</a>
                <a href="index.php" class="btn btn-secondary">🏠 Ana Sayfa</a>
            </div>
            
        <?php elseif ($status === 'invalid'): ?>
            <div class="icon invalid">❌</div>
            <h1>Geçersiz Link</h1>
            <div class="message invalid"><?= $message ?></div>
            <div class="info-box">
                <strong>💡 Olası Nedenler:</strong><br>
                • Link daha önce kullanılmış<br>
                • Link hatalı kopyalanmış<br>
                • Email adresi zaten doğrulanmış
            </div>
            <div class="buttons">
                <a href="login.php" class="btn btn-success">🔑 Giriş Dene</a>
                <a href="register-form.php" class="btn btn-primary">🔄 Yeni Kayıt</a>
            </div>
            
        <?php elseif ($status === 'error'): ?>
            <div class="icon error">⚠️</div>
            <h1>Doğrulama Hatası</h1>
            <div class="message error"><?= $message ?></div>
            <div class="info-box">
                <strong>🔧 Çözüm Önerileri:</strong><br>
                • Sayfayı yenileyin<br>
                • Linke tekrar tıklayın<br>
                • Farklı bir tarayıcı deneyin
            </div>
            <div class="buttons">
                <a href="javascript:location.reload()" class="btn btn-primary">🔄 Tekrar Dene</a>
                <a href="register-form.php" class="btn btn-secondary">📝 Yeni Kayıt</a>
            </div>
            
        <?php else: ?>
            <div class="icon missing">❓</div>
            <h1>Token Bulunamadı</h1>
            <div class="message missing"><?= $message ?></div>
            <div class="info-box">
                <strong>📧 Email Doğrulama Nasıl Yapılır:</strong><br>
                1. Email kutunuzu kontrol edin<br>
                2. "Email Adresinizi Doğrulayın" başlıklı emaili bulun<br>
                3. Email içindeki doğrulama linkine tıklayın<br>
                4. Bu sayfaya yönlendirileceksiniz
            </div>
            <div class="buttons">
                <a href="register-form.php" class="btn btn-primary">📝 Kayıt Ol</a>
                <a href="index.php" class="btn btn-secondary">🏠 Ana Sayfa</a>
            </div>
        <?php endif; ?>
        
        <div class="links">
            <a href="email-logs-db.php">📧 Email Logları</a> |
            <a href="register-form.php">📝 Kayıt Ol</a> |
            <a href="login.php">🔑 Giriş Yap</a>
        </div>
    </div>
</body>
</html>
