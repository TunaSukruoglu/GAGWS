<?php
echo "<h1>🔧 Kayıt Sistemi Test</h1>";
echo "<hr>";

// Test kayıt formu
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box;
        }
        button { 
            background: #007bff; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px;
        }
        button:hover { background: #0056b3; }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="info">
        <h3>📧 Test Bilgileri</h3>
        <p><strong>SMTP:</strong> gokhanaydinli.com:465 (SSL)</p>
        <p><strong>Onay Mail:</strong> sukru.sukruoglu@gmail.com'e yönlendirilecek</p>
        <p><strong>Güncelleme:</strong> register.php dosyası çalışan ayarlarla güncellendi</p>
    </div>
    
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="fullname">Ad Soyad:</label>
            <input type="text" id="fullname" name="fullname" value="Test Kullanıcı" required>
        </div>
        
        <div class="form-group">
            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" value="test@test.com" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Telefon:</label>
            <input type="tel" id="phone" name="phone" value="05551234567" required>
        </div>
        
        <div class="form-group">
            <label for="password">Şifre:</label>
            <input type="password" id="password" name="password" value="123456" required>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Şifre Tekrar:</label>
            <input type="password" id="password_confirm" name="password_confirm" value="123456" required>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="terms" checked required>
                Şartlar ve koşulları kabul ediyorum
            </label>
        </div>
        
        <button type="submit">🚀 Kayıt Ol</button>
    </form>
    
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <h4>🔍 Test Sonucu:</h4>
        <p>Form gönderildikten sonra:</p>
        <ul>
            <li>✅ Kayıt başarılıysa: Onay maili sukru.sukruoglu@gmail.com'e gidecek</li>
            <li>❌ Hata varsa: Hata mesajı görüntülenecek</li>
        </ul>
    </div>
</body>
</html>
