<?php
session_start();
include 'db.php';

echo "<h2>🔧 Root Admin Panel Test</h2>";
echo "<hr>";

// Session bilgilerini göster
echo "<h3>📋 Session Bilgileri:</h3>";
echo "<pre>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'YOK') . "\n";
echo "role: " . ($_SESSION['role'] ?? 'YOK') . "\n";
echo "user_role: " . ($_SESSION['user_role'] ?? 'YOK') . "\n";
echo "name: " . ($_SESSION['name'] ?? 'YOK') . "\n";
echo "email: " . ($_SESSION['email'] ?? 'YOK') . "\n";
echo "</pre>";

// Root kullanıcısını kontrol et
if (isset($_SESSION['user_id'])) {
    echo "<h3>🔍 Root Kontrol Testi:</h3>";
    $user_id = $_SESSION['user_id'];
    
    // isRoot fonksiyonunu test et
    $is_root = isRoot($user_id, $conn);
    echo "isRoot() sonucu: " . ($is_root ? "✅ TRUE" : "❌ FALSE") . "<br>";
    
    // Manuel sorgu ile kontrol
    $check_user = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $user_result = $check_user->get_result();
    
    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "<h4>Kullanıcı Bilgileri:</h4>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>İsim: " . htmlspecialchars($user['name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
        echo "<li>Rol: <strong>" . strtoupper($user['role']) . "</strong></li>";
        echo "</ul>";
        
        if ($user['role'] == 'root') {
            echo "<p style='color: green;'>✅ Bu kullanıcı ROOT yetkisine sahip!</p>";
            echo "<p><a href='root-admin-panel.php' style='background: #dc3545; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔑 Root Admin Panel'e Git</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Bu kullanıcı ROOT değil!</p>";
        }
    }
} else {
    echo "<h3>⚠️ Session Yok - Giriş Yapmalısın</h3>";
    echo "<p>Root admin panel'e erişmek için önce root kullanıcı olarak giriş yapmalısın.</p>";
    
    echo "<h4>🔑 Root Kullanıcı Bilgileri:</h4>";
    echo "<ul>";
    echo "<li>Email: <strong>root@gokhanaydinli.com</strong></li>";
    echo "<li>Şifre: <strong>113041sS?!_</strong></li>";
    echo "</ul>";
    
    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🚪 Giriş Sayfasına Git</a></p>";
}

// Tüm root kullanıcılarını listele
echo "<hr>";
echo "<h3>👥 Mevcut Root Kullanıcıları:</h3>";
$root_users = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'root'");

if ($root_users->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>İsim</th><th>Email</th><th>Rol</th></tr>";
    
    while ($root_user = $root_users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $root_user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($root_user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($root_user['email']) . "</td>";
        echo "<td><strong style='color: red;'>ROOT</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Hiç root kullanıcısı bulunamadı!</p>";
    echo "<p>Veritabanından root kullanıcısı oluşturuluyor...</p>";
    
    // Root kullanıcısı oluştur
    try {
        $root_password = password_hash('113041sS?!_', PASSWORD_DEFAULT);
        $insert_root = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'root', TRUE, TRUE)");
        $root_name = 'root';
        $root_email = 'root@gokhanaydinli.com';
        $insert_root->bind_param("sss", $root_name, $root_email, $root_password);
        
        if ($insert_root->execute()) {
            echo "<p style='color: green;'>✅ Root kullanıcısı oluşturuldu!</p>";
        } else {
            echo "<p style='color: red;'>❌ Root kullanıcısı oluşturulamadı: " . $conn->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    }
}

echo "<hr>";
echo "<small>🕒 Test Zamanı: " . date('d.m.Y H:i:s') . "</small>";
?>
