<?php
// get-tokens.php - Aktif token'ları göster

header('Content-Type: text/html; charset=UTF-8');

echo "<h1>📋 Aktif Token'lar</h1>";
echo "<hr>";

try {
    include 'db.php';
    
    // Tüm doğrulanmamış kullanıcıları göster
    $stmt = $conn->prepare("
        SELECT id, name, email, verification_token, is_verified, created_at 
        FROM users 
        WHERE verification_token IS NOT NULL 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<h3>🔄 Bekleyen Aktivasyonlar:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Ad</th><th>E-posta</th><th>Token</th><th>Durum</th><th>Tarih</th><th>Test</th>";
        echo "</tr>";
        
        while ($row = $result->fetch_assoc()) {
            $token = $row['verification_token'];
            $status = $row['is_verified'] ? '✅ Aktif' : '⏳ Bekliyor';
            $test_link = "activate-test.php?token=" . urlencode($token);
            
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td style='font-family: monospace; font-size: 10px;'>" . substr($token, 0, 32) . "...</td>";
            echo "<td>$status</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "<td><a href='$test_link' target='_blank'>🧪 Test</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // İlk token'ı otomatik test et
        $first_result = $conn->query("
            SELECT verification_token 
            FROM users 
            WHERE verification_token IS NOT NULL AND is_verified = 0 
            LIMIT 1
        ");
        
        if ($first_result && $first_row = $first_result->fetch_assoc()) {
            $first_token = $first_row['verification_token'];
            echo "<br><div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
            echo "<h4>🚀 Hızlı Test:</h4>";
            echo "<p><strong>İlk aktif token ile test:</strong></p>";
            echo "<p><a href='activate-test.php?token=" . urlencode($first_token) . "' style='background: #15B97C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Et</a></p>";
            echo "<p><small>Token: " . htmlspecialchars($first_token) . "</small></p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
        echo "❌ Aktif token bulunamadı!<br>";
        echo "Yeni bir kayıt yapmanız gerekiyor.";
        echo "</div>";
    }
    
    // Tüm kullanıcıları göster
    echo "<br><h3>👥 Tüm Kullanıcılar:</h3>";
    $all_users = $conn->query("SELECT id, name, email, is_verified, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    if ($all_users && $all_users->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Ad</th><th>E-posta</th><th>Durum</th><th>Tarih</th>";
        echo "</tr>";
        
        while ($row = $all_users->fetch_assoc()) {
            $status = $row['is_verified'] ? '✅ Doğrulanmış' : '⏳ Bekliyor';
            $row_color = $row['is_verified'] ? '#d4edda' : '#fff3cd';
            
            echo "<tr style='background: $row_color;'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>$status</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "❌ Hata: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><hr>";
echo "<h3>🔄 Yeni Test İçin:</h3>";
echo "<ol>";
echo "<li><a href='index.php'>Ana sayfadan yeni kayıt yapın</a></li>";
echo "<li>E-posta kutunuzu kontrol edin</li>";
echo "<li>Gelen aktivasyon linkine tıklayın</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Ana Sayfaya Dön</a></p>";
?>