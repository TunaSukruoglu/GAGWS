<?php
include 'db.php';

echo "<h2>Users Tablosunu Email Verification için Güncelleme</h2>";

try {
    // Verification alanları ekle - IF NOT EXISTS yerine direkt kontrol edelim
    
    // Önce mevcut alanları kontrol et
    $result = $conn->query("DESCRIBE users");
    $existing_fields = [];
    while ($row = $result->fetch_assoc()) {
        $existing_fields[] = $row['Field'];
    }
    
    if (!in_array('verification_token', $existing_fields)) {
        $sql1 = "ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL";
        $conn->query($sql1);
        echo "✅ verification_token alanı eklendi<br>";
    } else {
        echo "✅ verification_token alanı zaten var<br>";
    }
    
    if (!in_array('is_verified', $existing_fields)) {
        $sql2 = "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0";
        $conn->query($sql2);
        echo "✅ is_verified alanı eklendi<br>";
    } else {
        echo "✅ is_verified alanı zaten var<br>";
    }
    
    if (!in_array('verified_at', $existing_fields)) {
        $sql3 = "ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL";
        $conn->query($sql3);
        echo "✅ verified_at alanı eklendi<br>";
    } else {
        echo "✅ verified_at alanı zaten var<br>";
    }
    
    // Mevcut kullanıcıları otomatik onaylı yap
    $sql4 = "UPDATE users SET is_verified = 1, verified_at = NOW() WHERE verification_token IS NULL OR verification_token = ''";
    $result = $conn->query($sql4);
    echo "✅ Mevcut " . $conn->affected_rows . " kullanıcı otomatik onaylandı<br>";
    
    echo "<br><h3>Güncel Tablo Yapısı:</h3>";
    $result = $conn->query("DESCRIBE users");
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
