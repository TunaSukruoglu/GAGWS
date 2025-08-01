<?php
include 'db.php';

echo "<h1>🔧 Aktivasyon Debug Sistemi</h1>";
echo "<hr>";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    echo "<h3>🔍 Token Analizi: <code>" . htmlspecialchars($token) . "</code></h3>";
    
    try {
        // Detaylı token kontrolü
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>📊 Token Bilgileri:</h4>";
        echo "<p><strong>Token Uzunluğu:</strong> " . strlen($token) . " karakter</p>";
        echo "<p><strong>Token Formatı:</strong> " . (ctype_xdigit($token) ? "✅ Geçerli hex" : "❌ Geçersiz format") . "</p>";
        echo "</div>";
        
        // Veritabanında token arama
        $stmt = $conn->prepare("SELECT id, name, email, verification_token, is_verified, created_at FROM users WHERE verification_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Token Veritabanında Bulundu!</h4>";
            echo "<p><strong>Kullanıcı ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>İsim:</strong> " . htmlspecialchars($user['name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
            echo "<p><strong>Doğrulama Durumu:</strong> " . ($user['is_verified'] ? "✅ Doğrulanmış" : "❌ Doğrulanmamış") . "</p>";
            echo "<p><strong>Kayıt Tarihi:</strong> " . $user['created_at'] . "</p>";
            
            // Süre kontrolü
            $created_time = strtotime($user['created_at']);
            $current_time = time();
            $time_diff = $current_time - $created_time;
            $hours_passed = round($time_diff / 3600, 2);
            
            echo "<p><strong>Geçen Süre:</strong> " . $hours_passed . " saat</p>";
            
            if ($time_diff > 86400) {
                echo "<p style='color: red;'><strong>⚠️ Süre Durumu:</strong> Token süresi dolmuş! (24 saat geçti)</p>";
            } else {
                echo "<p style='color: green;'><strong>✅ Süre Durumu:</strong> Token hala geçerli</p>";
            }
            
            echo "</div>";
            
            // Aktivasyon butonu
            if (!$user['is_verified'] && $time_diff <= 86400) {
                echo "<div style='text-align: center; margin: 20px 0;'>";
                echo "<a href='activate.php?token=$token' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 18px;'>🚀 Hesabı Aktifleştir</a>";
                echo "</div>";
            } else if ($user['is_verified']) {
                echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>⚠️ Bu hesap zaten aktifleştirilmiş!</h4>";
                echo "<p>Bu kullanıcı daha önce doğrulanmış. Tekrar aktivasyon yapılamaz.</p>";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ Token Veritabanında Bulunamadı!</h4>";
            echo "<p>Bu token:</p>";
            echo "<ul>";
            echo "<li>Hiç oluşturulmamış olabilir</li>";
            echo "<li>Daha önce kullanılmış ve silinmiş olabilir</li>";
            echo "<li>Süre dolduğu için temizlenmiş olabilir</li>";
            echo "</ul>";
            echo "</div>";
            
            // Benzer tokenlar ara
            $similar_check = $conn->prepare("SELECT verification_token FROM users WHERE verification_token IS NOT NULL LIMIT 5");
            $similar_check->execute();
            $similar_result = $similar_check->get_result();
            
            if ($similar_result->num_rows > 0) {
                echo "<div style='background: #e2e3e5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>🔍 Mevcut Tokenlar (İlk 5):</h4>";
                while ($row = $similar_result->fetch_assoc()) {
                    $existing_token = $row['verification_token'];
                    echo "<p><code>$existing_token</code> ";
                    echo "<a href='?token=$existing_token' style='background: #6c757d; color: white; padding: 2px 8px; text-decoration: none; border-radius: 3px; font-size: 11px;'>Test Et</a></p>";
                }
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
        echo "<h4>❌ Veritabanı Hatası!</h4>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Token Parametresi Bulunamadı!</h4>";
    echo "<p>Bu sayfayı kullanmak için URL'de ?token=XXXXXXX parametresi olmalı.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔧 Test Araçları:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='debug-users.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📋 Kullanıcı Listesi</a>";
echo "<a href='user-manager.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>👤 Kullanıcı Yönetimi</a>";
echo "<a href='test-register.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>📝 Yeni Kayıt</a>";
echo "</div>";

echo "<small>🕒 " . date('d.m.Y H:i:s') . "</small>";
?>
