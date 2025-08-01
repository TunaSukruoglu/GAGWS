<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔑 Giriş Yap - Gökhan Aydınlı Gayrimenkul</title>
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
            max-width: 450px; 
            width: 90%; 
        }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin: 0; font-size: 2.2em; }
        .header p { color: #666; margin: 10px 0 0 0; }
        .form-group { margin: 20px 0; }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: #333; 
        }
        input[type="email"], input[type="password"] { 
            width: 100%; 
            padding: 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 10px; 
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        input:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .submit-btn { 
            width: 100%; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 18px; 
            border: none; 
            border-radius: 10px; 
            font-size: 18px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: transform 0.2s;
        }
        .submit-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .error { 
            color: #e74c3c; 
            background: #ffeaea; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            border-left: 4px solid #e74c3c;
        }
        .success { 
            color: #27ae60; 
            background: #eafaf1; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            border-left: 4px solid #27ae60;
        }
        .links { 
            text-align: center; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #eee; 
        }
        .links a { 
            color: #667eea; 
            text-decoration: none; 
            margin: 0 15px; 
            display: inline-block;
            margin-bottom: 10px;
        }
        .links a:hover { text-decoration: underline; }
        .remember { 
            margin: 15px 0; 
        }
        .remember input[type="checkbox"] { 
            transform: scale(1.2); 
            margin-right: 8px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Gökhan Aydınlı Gayrimenkul</h1>
            <p>Hesabınıza giriş yapın</p>
        </div>
        
        <?php 
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error">❌ ' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['login_success'])) {
            echo '<div class="success">✅ ' . $_SESSION['login_success'] . '</div>';
            unset($_SESSION['login_success']);
        }
        ?>
        
        <form action="login-with-verification.php" method="POST">
            <div class="form-group">
                <label for="email">E-posta Adresi</label>
                <input type="email" id="email" name="email" required placeholder="Örn: ahmet@example.com" 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required placeholder="Şifrenizi girin">
            </div>
            
            <div class="remember">
                <label>
                    <input type="checkbox" name="remember" value="1"> 
                    Beni hatırla
                </label>
            </div>
            
            <button type="submit" class="submit-btn">
                🔑 Giriş Yap
            </button>
        </form>
        
        <div class="links">
            <a href="register-form.php">📝 Henüz hesabınız yok mu? Kayıt olun</a><br>
            <a href="forgot-password.php">🔒 Şifremi unuttum</a><br>
            <a href="verify-email-new.php">📧 Email doğrulama</a>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <a href="email-logs-db.php">📧 Email Logları</a> |
            <a href="index.php">🏠 Ana Sayfa</a>
        </div>
    </div>
</body>
</html>
