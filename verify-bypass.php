<?php
include 'db.php';

echo "<h2>🔧 Root & Admin Email Doğrulama Bypass</h2>";
echo "<hr>";

if (isset($_GET['action']) && $_GET['action'] == 'verify_admins') {
    echo "<h3>✅ Root ve Admin kullanıcıları doğrulanıyor...</h3>";
    
    try {
        // Root ve Admin kullanıcılarını doğrulanmış yap
        $verify_admins = $conn->prepare("UPDATE users SET is_verified = 1 WHERE role IN ('root', 'admin')");
        
        if ($verify_admins->execute()) {
            $affected_rows = $conn->affected_rows;
            echo "<p style='color: green;'>✅ $affected_rows root/admin kullanıcısı doğrulandı!</p>";
        } else {
            throw new Exception("Doğrulama güncellemesi başarısız: " . $conn->error);
        }
        
        echo "<hr>";
        echo "<h3>🎯 İşlem Tamamlandı!</h3>";
        echo "<p><strong>Artık root ve admin kullanıcıları email doğrulaması olmadan giriş yapabilir!</strong></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Hata:</strong> " . $e->getMessage() . "</p>";
    }
}

// Mevcut kullanıcıların doğrulama durumunu göster
echo "<h3>👥 Kullanıcı Doğrulama Durumu:</h3>";

$users_result = $conn->query("SELECT id, name, email, role, is_verified FROM users ORDER BY role DESC, id ASC");

if ($users_result && $users_result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>İsim</th><th>Email</th><th>Rol</th><th>Doğrulanma</th><th>Giriş</th>";
    echo "</tr>";
    
    while ($user = $users_result->fetch_assoc()) {
        $role_color = '';
        $verify_status = '';
        $login_status = '';
        
        switch($user['role']) {
            case 'root': 
                $role_color = 'red'; 
                break;
            case 'admin': 
                $role_color = 'orange'; 
                break;
            case 'agent': 
                $role_color = 'blue'; 
                break;
            case 'user': 
                $role_color = 'green'; 
                break;
        }
        
        if ($user['is_verified'] == 1) {
            $verify_status = '<span style="color: green;">✅ Doğrulanmış</span>';
        } else {
            $verify_status = '<span style="color: red;">❌ Doğrulanmamış</span>';
        }
        
        // Root ve Admin için giriş durumu
        if ($user['role'] == 'root' || $user['role'] == 'admin') {
            if ($user['is_verified'] == 1) {
                $login_status = '<span style="color: green;">✅ Giriş Yapabilir</span>';
            } else {
                $login_status = '<span style="color: orange;">⚠️ Doğrulama Gerekli</span>';
            }
        } else {
            if ($user['is_verified'] == 1) {
                $login_status = '<span style="color: green;">✅ Giriş Yapabilir</span>';
            } else {
                $login_status = '<span style="color: red;">❌ Email Doğrulama Gerekli</span>';
            }
        }
        
        echo "<tr>";
        echo "<td><strong>" . $user['id'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td><strong style='color: $role_color;'>" . strtoupper($user['role']) . "</strong></td>";
        echo "<td>$verify_status</td>";
        echo "<td>$login_status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br>";
    
    // Root ve Admin doğrulama durumu kontrol et
    $unverified_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('root', 'admin') AND is_verified = 0")->fetch_assoc()['count'];
    
    if ($unverified_admins > 0) {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #856404;'>⚠️ Dikkat!</h4>";
        echo "<p>$unverified_admins root/admin kullanıcısı henüz doğrulanmamış.</p>";
        echo "<p><strong>Bu kullanıcılar artık email doğrulaması olmadan giriş yapabilir!</strong></p>";
        echo "<p>Emin olmak için doğrulama durumlarını da güncellemek ister misin?</p>";
        echo "<a href='?action=verify_admins' onclick='return confirm(\"Root ve Admin kullanıcıları doğrulansın mı?\")' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>✅ ROOT & ADMIN DOĞRULA</a>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #155724;'>🎯 Mükemmel!</h4>";
        echo "<p><strong>Tüm root ve admin kullanıcıları doğrulanmış durumda!</strong></p>";
        echo "<p>Email doğrulaması olmadan giriş yapabilirler.</p>";
        echo "</div>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Hiç kullanıcı bulunamadı!</p>";
}

echo "<hr>";
echo "<h3>🔑 Giriş Bilgileri:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th>Rol</th><th>Email</th><th>Şifre</th><th>Durum</th></tr>";
echo "<tr><td><strong style='color: red;'>ROOT</strong></td><td>root@gokhanaydinli.com</td><td>113041sS?!_</td><td>✅ Email doğrulama YOK</td></tr>";
echo "<tr><td><strong style='color: orange;'>ADMIN</strong></td><td>info@gokhanaydinli.com</td><td>admin123</td><td>✅ Email doğrulama YOK</td></tr>";
echo "</table>";

echo "<br>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚪 Giriş Test Et</a></p>";
echo "<p><a href='mail-config-test.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>� Mail Konfigürasyon Test</a></p>";
echo "<p><a href='final-root-test.php' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>�🔧 Root Yetki Test</a></p>";

echo "<hr>";
echo "<small>🕒 Kontrol Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
