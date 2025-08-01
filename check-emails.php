<?php
include 'db.php';

echo "<h2>📧 Email Adresleri Kontrol ve Güncelleme</h2>";
echo "<hr>";

// Güncelleme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'update_emails') {
    echo "<h3>🔄 Email Adresleri Güncelleniyor...</h3>";
    
    try {
        // Root kullanıcısının email'ini güncelle
        $update_root = $conn->prepare("UPDATE users SET email = ? WHERE role = 'root'");
        $root_email = 'root@gokhanaydinli.com';
        $update_root->bind_param("s", $root_email);
        
        if ($update_root->execute()) {
            echo "✅ Root email güncellendi: $root_email<br>";
        } else {
            echo "❌ Root email güncellenemedi: " . $conn->error . "<br>";
        }
        
        // Admin kullanıcısının email'ini güncelle
        $update_admin = $conn->prepare("UPDATE users SET email = ? WHERE role = 'admin'");
        $admin_email = 'info@gokhanaydinli.com';
        $update_admin->bind_param("s", $admin_email);
        
        if ($update_admin->execute()) {
            echo "✅ Admin email güncellendi: $admin_email<br>";
        } else {
            echo "❌ Admin email güncellenemedi: " . $conn->error . "<br>";
        }
        
        echo "<hr>";
        echo "<p style='color: green;'><strong>✅ Email güncellemeleri tamamlandı!</strong></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Hata:</strong> " . $e->getMessage() . "</p>";
    }
}

// Mevcut kullanıcıları göster
echo "<h3>👥 Mevcut Kullanıcılar ve Email Adresleri:</h3>";

$users_result = $conn->query("SELECT id, name, email, role FROM users ORDER BY role DESC, id ASC");

if ($users_result && $users_result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>İsim</th><th>Email</th><th>Rol</th><th>Durum</th>";
    echo "</tr>";
    
    $root_found = false;
    $admin_found = false;
    $correct_root_email = false;
    $correct_admin_email = false;
    
    while ($user = $users_result->fetch_assoc()) {
        $role_color = '';
        $status = '';
        
        switch($user['role']) {
            case 'root': 
                $role_color = 'red'; 
                $root_found = true;
                if ($user['email'] == 'root@gokhanaydinli.com') {
                    $status = '✅ Doğru';
                    $correct_root_email = true;
                } else {
                    $status = '❌ Yanlış (root@gokhanaydinli.com olmalı)';
                }
                break;
            case 'admin': 
                $role_color = 'orange'; 
                $admin_found = true;
                if ($user['email'] == 'info@gokhanaydinli.com') {
                    $status = '✅ Doğru';
                    $correct_admin_email = true;
                } else {
                    $status = '❌ Yanlış (info@gokhanaydinli.com olmalı)';
                }
                break;
            case 'agent': 
                $role_color = 'blue'; 
                $status = '📝 Agent';
                break;
            case 'user': 
                $role_color = 'green'; 
                $status = '👤 User';
                break;
        }
        
        echo "<tr>";
        echo "<td><strong>" . $user['id'] . "</strong></td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($user['email']) . "</strong></td>";
        echo "<td><strong style='color: $role_color;'>" . strtoupper($user['role']) . "</strong></td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br>";
    
    // Durum analizi
    echo "<h3>📊 Email Durum Analizi:</h3>";
    echo "<ul>";
    
    if ($root_found) {
        if ($correct_root_email) {
            echo "<li style='color: green;'>✅ <strong>Root email doğru:</strong> root@gokhanaydinli.com</li>";
        } else {
            echo "<li style='color: red;'>❌ <strong>Root email yanlış!</strong> Düzeltilmesi gerekiyor.</li>";
        }
    } else {
        echo "<li style='color: red;'>❌ <strong>Root kullanıcısı bulunamadı!</strong></li>";
    }
    
    if ($admin_found) {
        if ($correct_admin_email) {
            echo "<li style='color: green;'>✅ <strong>Admin email doğru:</strong> info@gokhanaydinli.com</li>";
        } else {
            echo "<li style='color: red;'>❌ <strong>Admin email yanlış!</strong> Düzeltilmesi gerekiyor.</li>";
        }
    } else {
        echo "<li style='color: red;'>❌ <strong>Admin kullanıcısı bulunamadı!</strong></li>";
    }
    
    echo "</ul>";
    
    // Düzeltme butonu
    if (!$correct_root_email || !$correct_admin_email) {
        echo "<hr>";
        echo "<h3>🔧 Email Düzeltme:</h3>";
        echo "<p>Yanlış email adresleri var. Düzeltmek istiyor musun?</p>";
        
        echo "<p><strong>Yapılacak değişiklikler:</strong></p>";
        echo "<ul>";
        if (!$correct_root_email) {
            echo "<li>Root email → <strong>root@gokhanaydinli.com</strong></li>";
        }
        if (!$correct_admin_email) {
            echo "<li>Admin email → <strong>info@gokhanaydinli.com</strong></li>";
        }
        echo "</ul>";
        
        echo "<p><a href='?action=update_emails' onclick='return confirm(\"Email adresleri güncellensin mi?\")' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 EMAIL ADRESLERİNİ DÜZELT</a></p>";
    } else {
        echo "<hr>";
        echo "<p style='color: green; font-size: 18px;'><strong>🎯 Tüm email adresleri doğru!</strong></p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Hiç kullanıcı bulunamadı!</p>";
}

echo "<hr>";
echo "<h3>🔑 Doğru Giriş Bilgileri:</h3>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f8f9fa;'><th>Rol</th><th>Email</th><th>Şifre</th></tr>";
echo "<tr><td><strong style='color: red;'>ROOT</strong></td><td>root@gokhanaydinli.com</td><td>113041sS?!_</td></tr>";
echo "<tr><td><strong style='color: orange;'>ADMIN</strong></td><td>info@gokhanaydinli.com</td><td>admin123</td></tr>";
echo "</table>";

echo "<br>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚪 Giriş Sayfası</a></p>";
echo "<p><a href='root-test.php' style='background: #28a745; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔧 Root Test</a></p>";
echo "<p><a href='reset-users.php' style='background: #6c757d; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔥 Users Sıfırla</a></p>";

echo "<hr>";
echo "<small>🕒 Kontrol Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
