<?php
include 'db.php';

echo "<h1>🧹 Test Verilerini Temizle ve Yeni Token Oluştur</h1>";
echo "<hr>";

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action === 'clean_test_users') {
            // Test kullanıcılarını temizle (test@test.com ve benzeri)
            $stmt = $conn->prepare("DELETE FROM users WHERE email LIKE '%test%' OR name LIKE '%Test%'");
            $stmt->execute();
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Temizlendi!</h4>";
            echo "<p>" . $stmt->affected_rows . " test kullanıcısı silindi.</p>";
            echo "</div>";
            
        } else if ($action === 'create_fresh_user') {
            // Yeni bir test kullanıcısı oluştur
            $fullname = $_POST['fullname'] ?? 'Test Kullanıcı';
            $email = $_POST['email'] ?? 'test@test.com';
            $phone = $_POST['phone'] ?? '05551234567';
            $password = password_hash('123456', PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(32));
            
            // Önce aynı email'i sil
            $delete_stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            
            // Yeni kullanıcı ekle
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssss", $fullname, $email, $phone, $password, $verification_token);
            
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $activation_link = "https://gokhanaydinli.com/activate.php?token=" . $verification_token;
                
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Yeni Kullanıcı Oluşturuldu!</h4>";
                echo "<p><strong>ID:</strong> $user_id</p>";
                echo "<p><strong>İsim:</strong> $fullname</p>";
                echo "<p><strong>Email:</strong> $email</p>";
                echo "<p><strong>Token:</strong> <code>$verification_token</code></p>";
                echo "<h5>🔗 Aktivasyon Linki:</h5>";
                echo "<div style='background: white; padding: 10px; border-radius: 3px; word-break: break-all; font-family: monospace; font-size: 12px;'>";
                echo "<a href='$activation_link' target='_blank'>$activation_link</a>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
                echo "<h4>❌ Hata!</h4>";
                echo "<p>" . $stmt->error . "</p>";
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Hata!</h4>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Yönetimi</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"] { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        button { 
            background: #007bff; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            margin: 5px;
        }
        button.danger { background: #dc3545; }
        button.success { background: #28a745; }
        .section { 
            border: 1px solid #ddd; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 5px; 
        }
    </style>
</head>
<body>

<div class="section">
    <h3>🧹 Test Verilerini Temizle</h3>
    <p>Test sırasında oluşturulmuş kullanıcıları temizler.</p>
    <form method="POST">
        <input type="hidden" name="action" value="clean_test_users">
        <button type="submit" class="danger">🗑️ Test Kullanıcılarını Sil</button>
    </form>
</div>

<div class="section">
    <h3>👤 Yeni Test Kullanıcısı Oluştur</h3>
    <p>Aktivasyon testi için yeni bir kullanıcı oluşturur ve aktivasyon linkini gösterir.</p>
    <form method="POST">
        <input type="hidden" name="action" value="create_fresh_user">
        
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
            <input type="text" id="phone" name="phone" value="05551234567" required>
        </div>
        
        <button type="submit" class="success">➕ Yeni Kullanıcı Oluştur</button>
    </form>
</div>

<div class="section">
    <h3>🔧 Faydalı Linkler:</h3>
    <a href="debug-users.php" style="background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin: 5px;">📋 Kullanıcı Listesi</a>
    <a href="test-register.php" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin: 5px;">📝 Kayıt Testi</a>
</div>

</body>
</html>
