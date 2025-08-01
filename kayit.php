<?php session_start(); ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 Üye Kayıt - Email Doğrulama Sistemi</title>
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
            max-width: 500px; 
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
        .required { color: #e74c3c; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="password"] { 
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
        .checkbox-group { 
            display: flex; 
            align-items: flex-start; 
            gap: 10px;
            margin: 20px 0;
        }
        .checkbox-group input[type="checkbox"] { 
            margin-top: 5px;
            width: auto;
        }
        .checkbox-group label { 
            margin: 0;
            font-weight: normal;
            flex: 1;
        }
        .btn { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 18px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: transform 0.2s;
        }
        .btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert { 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            font-weight: 500;
        }
        .alert-error { 
            background: #ffebee; 
            color: #c62828; 
            border-left: 4px solid #f44336;
        }
        .alert-success { 
            background: #e8f5e8; 
            color: #2e7d32; 
            border-left: 4px solid #4caf50;
        }
        .links { 
            text-align: center; 
            margin-top: 20px; 
        }
        .links a { 
            color: #667eea; 
            text-decoration: none; 
            margin: 0 10px;
        }
        .links a:hover { 
            text-decoration: underline; 
        }
        .system-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
        .system-info h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 1.1em;
        }
        .system-info ul {
            margin: 0;
            padding-left: 20px;
            color: #6c757d;
        }
        .system-info li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Üye Kayıt</h1>
            <p>Email Doğrulama Sistemi ile Güvenli Kayıt</p>
        </div>

        <div class="system-info">
            <h3>📧 Email Doğrulama Sistemi</h3>
            <ul>
                <li>✅ Kayıt sonrası email adresinize doğrulama linki gönderilir</li>
                <li>⏰ Doğrulama linki 24 saat geçerlidir</li>
                <li>🔒 Email doğrulaması yapmadan sisteme giriş yapamazsınız</li>
                <li>📱 Spam klasörünüzü kontrol etmeyi unutmayın</li>
            </ul>
        </div>

        <?php if (isset($_SESSION['register_error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['register_error'] ?>
            </div>
            <?php unset($_SESSION['register_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['register_success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['register_success'] ?>
            </div>
            <?php unset($_SESSION['register_success']); ?>
        <?php endif; ?>

        <form method="POST" action="register-mxrouting.php" id="registerForm">
            <div class="form-group">
                <label for="fullname">Ad Soyad <span class="required">*</span></label>
                <input type="text" id="fullname" name="fullname" required 
                       value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>"
                       placeholder="Adınızı ve soyadınızı girin">
            </div>

            <div class="form-group">
                <label for="phone">Telefon <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                       placeholder="0555 123 45 67">
            </div>

            <div class="form-group">
                <label for="email">E-posta <span class="required">*</span></label>
                <input type="email" id="email" name="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="ornek@email.com">
            </div>

            <div class="form-group">
                <label for="password">Şifre <span class="required">*</span></label>
                <input type="password" id="password" name="password" required 
                       placeholder="En az 6 karakter">
            </div>

            <div class="form-group">
                <label for="password_confirm">Şifre Tekrar <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required 
                       placeholder="Şifrenizi tekrar girin">
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required value="1">
                <label for="terms">
                    <strong>Kullanım şartlarını</strong> ve <strong>gizlilik politikasını</strong> okudum ve kabul ediyorum. <span class="required">*</span>
                </label>
            </div>

            <button type="submit" class="btn">
                🔐 Kayıt Ol
            </button>
        </form>

        <div class="links">
            <a href="index.php">🏠 Ana Sayfa</a>
            <a href="giris.php">🔑 Giriş Yap</a>
            <a href="mxrouting-email-test.php">📧 MXRouting Email Test</a>
            <a href="email-logs-db.php">📊 Email Logları</a>
        </div>
    </div>

    <script>
        // Form doğrulama
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır!');
                return false;
            }
            
            // Form gönderilirken butonu devre dışı bırak
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Kayıt yapılıyor...';
        });

        // Telefon numarası formatı
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + ' ' + value.slice(3);
                } else if (value.length <= 8) {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
                } else {
                    value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 8) + ' ' + value.slice(8, 10);
                }
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
