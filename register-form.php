<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎯 Kayıt Ol - Email Doğrulama Sistemi</title>
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
            margin-top: 4px; 
            transform: scale(1.2);
        }
        .checkbox-group label { 
            margin: 0; 
            font-weight: normal; 
            line-height: 1.4;
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
        }
        .links a:hover { text-decoration: underline; }
        .password-strength { 
            font-size: 12px; 
            margin-top: 5px; 
            color: #666; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Gökhan Aydınlı Gayrimenkul</h1>
            <p>Hesap oluşturun ve email doğrulaması yapın</p>
        </div>
        
        <?php 
        session_start();
        if (isset($_SESSION['register_error'])) {
            echo '<div class="error">❌ ' . $_SESSION['register_error'] . '</div>';
            unset($_SESSION['register_error']);
        }
        if (isset($_SESSION['register_success'])) {
            echo '<div class="success">✅ ' . $_SESSION['register_success'] . '</div>';
            unset($_SESSION['register_success']);
        }
        ?>
        
        <form action="register-with-verification.php" method="POST" id="registerForm">
            <div class="form-group">
                <label for="fullname">Ad Soyad <span class="required">*</span></label>
                <input type="text" id="fullname" name="fullname" required placeholder="Örn: Ahmet Yılmaz">
            </div>
            
            <div class="form-group">
                <label for="phone">Telefon Numarası <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" required placeholder="Örn: 0532 123 45 67">
            </div>
            
            <div class="form-group">
                <label for="email">E-posta Adresi <span class="required">*</span></label>
                <input type="email" id="email" name="email" required placeholder="Örn: ahmet@example.com">
            </div>
            
            <div class="form-group">
                <label for="password">Şifre <span class="required">*</span></label>
                <input type="password" id="password" name="password" required placeholder="En az 6 karakter">
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Şifre Tekrar <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" required placeholder="Şifrenizi tekrar girin">
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" value="1" required>
                <label for="terms">
                    <strong>Şartlar ve Koşulları</strong> kabul ediyorum ve <strong>Kişisel Verilerimin İşlenmesi</strong>ne onay veriyorum. <span class="required">*</span>
                </label>
            </div>
            
            <button type="submit" class="submit-btn">
                📧 Kayıt Ol ve Email Doğrula
            </button>
        </form>
        
        <div class="links">
            <a href="login.php">🔑 Zaten hesabınız var mı? Giriş yapın</a>
            <br><br>
            <a href="email-logs-db.php">📧 Email Logları</a> |
            <a href="verify-email.php">✅ Email Doğrula</a>
        </div>
    </div>

    <script>
        // Honeypot Bot Koruması
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            
            // Honeypot alanı ekle (gizli)
            const honeypot = document.createElement('input');
            honeypot.type = 'text';
            honeypot.name = 'website';
            honeypot.style.display = 'none';
            honeypot.style.position = 'absolute';
            honeypot.style.left = '-9999px';
            honeypot.setAttribute('tabindex', '-1');
            honeypot.setAttribute('autocomplete', 'off');
            registerForm.appendChild(honeypot);
            
            console.log('Kayıt formu honeypot koruması aktif');
        });
        
        // Şifre güçlülük kontrolü
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 6) strength++;
            else feedback.push('En az 6 karakter');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Büyük harf');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('Rakam');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('Özel karakter');
            
            const levels = ['Çok Zayıf', 'Zayıf', 'Orta', 'Güçlü', 'Çok Güçlü'];
            const colors = ['#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#27ae60'];
            
            strengthDiv.style.color = colors[strength];
            strengthDiv.textContent = `Güçlülük: ${levels[strength]}${feedback.length ? ' (Eksik: ' + feedback.join(', ') + ')' : ''}`;
        });

        // Form validasyonu
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            // Honeypot kontrolü
            const honeypotField = document.querySelector('input[name="website"]');
            if (honeypotField && honeypotField.value !== '') {
                e.preventDefault();
                alert('Bot aktivitesi tespit edildi.');
                return false;
            }
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('❌ Şifreler eşleşmiyor!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('❌ Şifre en az 6 karakter olmalıdır!');
                return false;
            }
            
            // Loading durumu ekle
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span style="color:#ffffff;">⏳ Kaydediliyor...</span>';
                
                // 1 saniye bekle (bot tespiti için)
                setTimeout(function() {
                    console.log('Kayıt formu güvenlik kontrolü başarılı');
                }, 1000);
            }
        });
    </script>
</body>
</html>
