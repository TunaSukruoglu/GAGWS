<?php
session_start();
include 'db.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';

if (empty($token)) {
    $message = "❌ Geçersiz onay linki.";
    $message_type = 'error';
} else {
    try {
        // Token'ı kontrol et
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Kullanıcıyı onayla
            $update_stmt = $conn->prepare("UPDATE users SET is_verified = 1, verified_at = NOW(), verification_token = NULL WHERE id = ?");
            $update_stmt->bind_param("i", $row['id']);
            
            if ($update_stmt->execute()) {
                $message = "✅ Email adresiniz başarıyla onaylandı! Artık hesabınıza giriş yapabilirsiniz.";
                $message_type = 'success';
                $user_name = htmlspecialchars($row['name']);
                $user_email = htmlspecialchars($row['email']);
                
                // Hoşgeldin email'i gönder
                $subject = "Hoşgeldiniz - Gökhan Aydınlı Gayrimenkul";
                $welcome_message = "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .header { text-align: center; background: #28a745; color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .info { background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🎉 Hoşgeldiniz!</h1>
            <p>Gökhan Aydınlı Gayrimenkul</p>
        </div>
        
        <h2>Merhaba " . $user_name . "!</h2>
        
        <p>Email doğrulamanız başarıyla tamamlandı. Gökhan Aydınlı Gayrimenkul ailesine hoşgeldiniz!</p>
        
        <div class='info'>
            <strong>✅ Hesabınız aktif!</strong><br>
            Artık tüm özelliklerimizi kullanabilirsiniz:
            <ul>
                <li>📋 Emlak ilanlarını görüntüleme</li>
                <li>❤️ Favori ilanlar</li>
                <li>📞 Doğrudan iletişim</li>
                <li>🔔 Yeni ilan bildirimleri</li>
            </ul>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='https://sunucu.dev/GokhanAydinli/giris.php' class='button'>🔑 Sisteme Giriş Yap</a>
        </div>
        
        <p><strong>Hesap Bilgileriniz:</strong></p>
        <ul>
            <li><strong>Ad Soyad:</strong> " . $user_name . "</li>
            <li><strong>Email:</strong> " . $user_email . "</li>
            <li><strong>Doğrulama Tarihi:</strong> " . date('d.m.Y H:i') . "</li>
        </ul>
        
        <div style='text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px; font-size: 12px;'>
            <p>Sorularınız için bize ulaşabilirsiniz.</p>
            <p>© 2025 Gökhan Aydınlı Gayrimenkul</p>
        </div>
    </div>
</body>
</html>";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: Gökhan Aydınlı Gayrimenkul <root@gokhanaydinli.com>\r\n";
                $headers .= "Reply-To: root@gokhanaydinli.com\r\n";
                
                // Hoşgeldin email'ini gönder
                try {
                    mail($row['email'], $subject, $welcome_message, $headers);
                } catch (Exception $e) {
                    error_log("Welcome email error: " . $e->getMessage());
                }
            } else {
                $message = "❌ Onay sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                $message_type = 'error';
            }
            $update_stmt->close();
        } else {
            $message = "❌ Geçersiz veya süresi dolmuş onay linki. Bu link daha önce kullanılmış olabilir.";
            $message_type = 'error';
        }
        $stmt->close();
    } catch (Exception $e) {
        $message = "❌ Sistem hatası oluştu.";
        $message_type = 'error';
        error_log("Email verification error: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Onayı - Gökhan Aydınlı Gayrimenkul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .verification-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .card-body {
            padding: 3rem;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .error-icon {
            color: #dc3545;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="verification-card">
                    <div class="card-header">
                        <h2><i class="fas fa-envelope-circle-check"></i> Email Onayı</h2>
                        <p class="mb-0">Gökhan Aydınlı Gayrimenkul</p>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($message_type === 'success'): ?>
                            <i class="fas fa-check-circle success-icon"></i>
                            <h3 class="text-success mb-4">Hoş Geldiniz!</h3>
                            <div class="alert alert-success" role="alert">
                                <?php echo $message; ?>
                            </div>
                            <?php if (isset($user_name)): ?>
                                <div class="mb-4">
                                    <h5>Kayıt Bilgileriniz:</h5>
                                    <p><strong>Ad Soyad:</strong> <?php echo $user_name; ?></p>
                                    <p><strong>Email:</strong> <?php echo $user_email; ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-home">
                                    <i class="fas fa-home"></i> Ana Sayfaya Git
                                </a>
                                <button type="button" class="btn btn-outline-primary" onclick="showLoginModal()">
                                    <i class="fas fa-sign-in-alt"></i> Hemen Giriş Yap
                                </button>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle error-icon"></i>
                            <h3 class="text-danger mb-4">Onay Başarısız</h3>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $message; ?>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-home">
                                    <i class="fas fa-home"></i> Ana Sayfaya Git
                                </a>
                                <button type="button" class="btn btn-outline-primary" onclick="showRegisterModal()">
                                    <i class="fas fa-user-plus"></i> Yeniden Kayıt Ol
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLoginModal() {
            window.location.href = 'index.php#login';
        }
        
        function showRegisterModal() {
            window.location.href = 'index.php#register';
        }
    </script>
</body>
</html>

