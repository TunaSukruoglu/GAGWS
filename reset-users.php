<?php
session_start();
include 'db.php';

echo "<h2>🔧 Users Tablosu Sıfırlama</h2>";
echo "<hr>";

// Güvenlik kontrolü - sadece GET parametresi ile çalıştır
if (isset($_GET['action']) && $_GET['action'] == 'reset_users') {
    
    echo "<h3>⚠️ Users Tablosu Sıfırlanıyor...</h3>";
    
    try {
        // 1. Users tablosundaki tüm kayıtları sil
        $delete_users = "DELETE FROM users";
        if ($conn->query($delete_users)) {
            echo "✅ Tüm kullanıcılar silindi<br>";
        } else {
            throw new Exception("Kullanıcılar silinemedi: " . $conn->error);
        }
        
        // 2. AUTO_INCREMENT'i 1'e sıfırla
        $reset_increment = "ALTER TABLE users AUTO_INCREMENT = 1";
        if ($conn->query($reset_increment)) {
            echo "✅ AUTO_INCREMENT 1'e sıfırlandı<br>";
        } else {
            throw new Exception("AUTO_INCREMENT sıfırlanamadı: " . $conn->error);
        }
        
        // 3. Root kullanıcısını ID=1 ile oluştur
        $root_password = password_hash('113041sS?!_', PASSWORD_DEFAULT);
        $insert_root = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'root', TRUE, TRUE)");
        $root_name = 'root';
        $root_email = 'root@gokhanaydinli.com';
        $insert_root->bind_param("sss", $root_name, $root_email, $root_password);
        
        if ($insert_root->execute()) {
            $new_root_id = $conn->insert_id;
            echo "✅ Root kullanıcısı oluşturuldu (ID: $new_root_id)<br>";
        } else {
            throw new Exception("Root kullanıcısı oluşturulamadı: " . $conn->error);
        }
        
        // 4. Admin kullanıcısını ID=2 ile oluştur
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'admin', TRUE, TRUE)");
        $admin_name = 'Gökhan Aydınlı';
        $admin_email = 'info@gokhanaydinli.com';
        $insert_admin->bind_param("sss", $admin_name, $admin_email, $admin_password);
        
        if ($insert_admin->execute()) {
            $new_admin_id = $conn->insert_id;
            echo "✅ Admin kullanıcısı oluşturuldu (ID: $new_admin_id)<br>";
        } else {
            throw new Exception("Admin kullanıcısı oluşturulamadı: " . $conn->error);
        }
        
        echo "<hr>";
        echo "<h3>🎯 Sıfırlama Tamamlandı!</h3>";
        echo "<p style='color: green;'><strong>✅ Users tablosu başarıyla sıfırlandı ve yeniden düzenlendi!</strong></p>";
        
        // Yeni kullanıcıları listele
        echo "<h4>👥 Yeni Kullanıcılar:</h4>";
        $users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY id ASC");
        
        if ($users_result->num_rows > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>İsim</th><th>Email</th><th>Rol</th></tr>";
            
            while ($user = $users_result->fetch_assoc()) {
                $role_color = '';
                switch($user['role']) {
                    case 'root': $role_color = 'red'; break;
                    case 'admin': $role_color = 'orange'; break;
                    case 'agent': $role_color = 'blue'; break;
                    case 'user': $role_color = 'green'; break;
                }
                
                echo "<tr>";
                echo "<td><strong>" . $user['id'] . "</strong></td>";
                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td><strong style='color: $role_color;'>" . strtoupper($user['role']) . "</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<br>";
        echo "<h4>🔑 Giriş Bilgileri:</h4>";
        echo "<ul>";
        echo "<li><strong>Root:</strong> root@gokhanaydinli.com / 113041sS?!_</li>";
        echo "<li><strong>Admin:</strong> info@gokhanaydinli.com / admin123</li>";
        echo "</ul>";
        
        echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚪 Giriş Sayfasına Git</a></p>";
        echo "<p><a href='root-test.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 Root Test</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Hata:</strong> " . $e->getMessage() . "</p>";
    }
    
} else {
    // Onay formu göster
    echo "<h3>⚠️ DİKKAT: Users Tablosu Sıfırlanacak!</h3>";
    echo "<p style='color: red;'><strong>Bu işlem tüm kullanıcıları silecek ve ID'leri 1'den başlatacak!</strong></p>";
    
    echo "<h4>📋 Mevcut Kullanıcılar:</h4>";
    $current_users = $conn->query("SELECT id, name, email, role FROM users ORDER BY id ASC");
    
    if ($current_users->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>İsim</th><th>Email</th><th>Rol</th></tr>";
        
        while ($user = $current_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td><strong>" . strtoupper($user['role']) . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Toplam kullanıcı:</strong> " . $current_users->num_rows . "</p>";
    }
    
    echo "<hr>";
    echo "<h4>🎯 Sıfırlama sonrası oluşturulacaklar:</h4>";
    echo "<ul>";
    echo "<li><strong>ID 1:</strong> root (root@gokhanaydinli.com)</li>";
    echo "<li><strong>ID 2:</strong> Gökhan Aydınlı (info@gokhanaydinli.com)</li>";
    echo "</ul>";
    
    echo "<p><a href='?action=reset_users' onclick='return confirm(\"Emin misiniz? Tüm kullanıcılar silinecek!\")' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔥 USERS TABLOSUNU SIFIRLA</a></p>";
    echo "<p><a href='root-test.php' style='background: #6c757d; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>↩️ Geri Dön</a></p>";
}

echo "<hr>";
echo "<small>🕒 İşlem Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
