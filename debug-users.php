<?php
// Veritabanı bağlantısı
include 'db.php';

echo "<h1>🔍 Kullanıcı Veritabanı Durumu</h1>";
echo "<hr>";

try {
    // Tüm kullanıcıları listele
    $stmt = $conn->prepare("SELECT id, name, email, verification_token, is_verified, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h3>📋 Son 10 Kullanıcı:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 10px;'>ID</th>";
    echo "<th style='padding: 10px;'>İsim</th>";
    echo "<th style='padding: 10px;'>Email</th>";
    echo "<th style='padding: 10px;'>Token Var mı?</th>";
    echo "<th style='padding: 10px;'>Doğrulandı mı?</th>";
    echo "<th style='padding: 10px;'>Kayıt Tarihi</th>";
    echo "<th style='padding: 10px;'>İşlem</th>";
    echo "</tr>";
    
    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . (!empty($user['verification_token']) ? "✅ Var" : "❌ Yok") . "</td>";
            echo "<td style='padding: 8px;'>" . ($user['is_verified'] ? "✅ Evet" : "❌ Hayır") . "</td>";
            echo "<td style='padding: 8px;'>" . $user['created_at'] . "</td>";
            
            // Manuel aktivasyon linki
            if (!empty($user['verification_token']) && !$user['is_verified']) {
                echo "<td style='padding: 8px;'>";
                echo "<a href='activate.php?token=" . $user['verification_token'] . "' style='background: #28a745; color: white; padding: 4px 8px; text-decoration: none; border-radius: 3px; font-size: 12px;'>Manuel Aktifleştir</a>";
                echo "</td>";
            } else if ($user['is_verified']) {
                echo "<td style='padding: 8px; color: green;'>✅ Aktif</td>";
            } else {
                echo "<td style='padding: 8px; color: red;'>❌ Token Yok</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7' style='padding: 20px; text-align: center;'>Kullanıcı bulunamadı.</td></tr>";
    }
    echo "</table>";
    
    // Token ile kullanıcı arama
    if (isset($_GET['check_token'])) {
        $token = $_GET['check_token'];
        echo "<h3>🔍 Token Kontrolü: " . htmlspecialchars($token) . "</h3>";
        
        $check_stmt = $conn->prepare("SELECT id, name, email, is_verified, created_at FROM users WHERE verification_token = ?");
        $check_stmt->bind_param("s", $token);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $user = $check_result->fetch_assoc();
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Token Bulundu!</h4>";
            echo "<p><strong>Kullanıcı:</strong> " . htmlspecialchars($user['name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
            echo "<p><strong>Doğrulandı mı:</strong> " . ($user['is_verified'] ? "Evet" : "Hayır") . "</p>";
            echo "<p><strong>Kayıt Tarihi:</strong> " . $user['created_at'] . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ Token Bulunamadı!</h4>";
            echo "<p>Bu token veritabanında mevcut değil veya hesap zaten doğrulanmış.</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h4>❌ Veritabanı Hatası!</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔧 Test Araçları:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='?' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔄 Sayfayı Yenile</a>";
echo "<a href='test-register.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📝 Yeni Kayıt Test</a>";
echo "</div>";

// Token kontrolü formu
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔍 Token Kontrol Et:</h4>";
echo "<form method='GET' style='display: flex; gap: 10px; align-items: center;'>";
echo "<input type='text' name='check_token' placeholder='Token değerini buraya yapıştır' style='flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "<button type='submit' style='background: #17a2b8; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;'>Kontrol Et</button>";
echo "</form>";
echo "</div>";

echo "<small>🕒 " . date('d.m.Y H:i:s') . "</small>";
?>
