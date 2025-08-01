<?php
// Database bağlantısını test et
echo "<h2>🔧 Database Bağlantı ve Root Test</h2>";
echo "<hr>";

try {
    // db.php dosyasını dahil et
    include 'db.php';
    
    echo "✅ <strong>Database bağlantısı başarılı!</strong><br>";
    echo "📊 Bağlantı: " . ($conn ? "Aktif" : "Pasif") . "<br>";
    echo "🔗 Sunucu: localhost<br>";
    echo "🗄️ Veritabanı: gokhanay_db<br><br>";
    
    // Users tablosundaki role enum'unu kontrol et
    $role_check = $conn->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
    if ($role_check && $role_check->num_rows > 0) {
        $role_info = $role_check->fetch_assoc();
        echo "👥 <strong>Users Role Enum:</strong><br>";
        echo "📋 " . $role_info['Type'] . "<br><br>";
        
        // Root değeri var mı kontrol et
        if (strpos($role_info['Type'], 'root') !== false) {
            echo "✅ 'root' rolü tanımlı<br><br>";
        } else {
            echo "❌ 'root' rolü eksik - güncelleniyor...<br>";
            $update_role = "ALTER TABLE users MODIFY COLUMN role ENUM('root', 'admin', 'agent', 'user') DEFAULT 'user'";
            if ($conn->query($update_role)) {
                echo "✅ Role enum başarıyla güncellendi<br><br>";
            } else {
                echo "❌ Role enum güncellenemedi: " . $conn->error . "<br><br>";
            }
        }
    }
    
    // Mevcut kullanıcıları listele
    $users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY role DESC, id ASC");
    if ($users_result && $users_result->num_rows > 0) {
        echo "👤 <strong>Mevcut Kullanıcılar:</strong><br>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>İsim</th><th>Email</th><th>Rol</th></tr>";
        
        while ($user = $users_result->fetch_assoc()) {
            $role_badge = '';
            switch($user['role']) {
                case 'root': $role_badge = '🔴 ROOT'; break;
                case 'admin': $role_badge = '🟠 ADMIN'; break;
                case 'agent': $role_badge = '🟡 AGENT'; break;
                case 'user': $role_badge = '🟢 USER'; break;
            }
            
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td><strong>$role_badge</strong></td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ Henüz kullanıcı bulunamadı<br><br>";
    }
    
    // Root kullanıcısı kontrolü
    $root_email = 'root@gokhanaydinli.com';
    $check_root = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $check_root->bind_param("s", $root_email);
    $check_root->execute();
    $root_result = $check_root->get_result();
    
    if ($root_result->num_rows > 0) {
        $root_user = $root_result->fetch_assoc();
        echo "🔑 <strong>Root kullanıcısı mevcut:</strong><br>";
        echo "📧 Email: $root_email<br>";
        echo "👤 İsim: " . htmlspecialchars($root_user['name']) . "<br>";
        echo "🔐 Şifre: 113041sS?!_<br><br>";
    } else {
        echo "⚠️ Root kullanıcısı bulunamadı, oluşturuluyor...<br>";
        
        try {
            $root_password = password_hash('113041sS?!_', PASSWORD_DEFAULT);
            $insert_root = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'root', TRUE, TRUE)");
            $root_name = 'root';
            $insert_root->bind_param("sss", $root_name, $root_email, $root_password);
            
            if ($insert_root->execute()) {
                echo "✅ Root kullanıcısı başarıyla oluşturuldu!<br>";
                echo "📧 Email: $root_email<br>";
                echo "👤 İsim: root<br>";
                echo "🔐 Şifre: 113041sS?!_<br><br>";
            } else {
                echo "❌ Root kullanıcısı oluşturulamadı: " . $conn->error . "<br><br>";
            }
        } catch (Exception $e) {
            echo "❌ Hata: " . $e->getMessage() . "<br><br>";
        }
    }
    
    echo "<hr>";
    echo "🎯 <strong>Test Sonucu:</strong> Database bağlantısı ve root sistemi hazır!<br><br>";
    echo "🔗 <a href='root-admin-panel.php' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔑 Root Admin Panel</a><br><br>";
    echo "🔗 <a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚪 Giriş Sayfası</a><br>";
    
} catch (Exception $e) {
    echo "❌ <strong>Database Hatası:</strong><br>";
    echo "🚨 " . $e->getMessage() . "<br>";
    echo "<hr>";
    echo "💡 <strong>Çözüm Önerileri:</strong><br>";
    echo "1. MySQL sunucusunun çalıştığından emin olun<br>";
    echo "2. Database bağlantı bilgilerini kontrol edin<br>";
    echo "3. users tablosunun role ENUM değerlerini güncelleyin<br>";
}

echo "<hr>";
echo "<small>🕒 Test Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
