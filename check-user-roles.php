<?php
include 'db.php';

echo "🔍 KULLANICI ROL KONTROLÜ\n";
echo "========================\n\n";

try {
    // Tüm kullanıcıları listele
    $result = $conn->query("SELECT id, name, email, role FROM users ORDER BY id");
    
    if ($result && $result->num_rows > 0) {
        echo "📋 Mevcut Kullanıcılar:\n";
        echo "─────────────────────\n";
        while ($row = $result->fetch_assoc()) {
            echo "🆔 ID: {$row['id']} | 👤 {$row['name']} | 📧 {$row['email']} | 🔑 Role: {$row['role']}\n";
        }
        
        echo "\n🔧 Root kullanıcısını admin yapalım...\n";
        
        // Root kullanıcısını admin yap
        $update_sql = "UPDATE users SET role = 'admin' WHERE email LIKE '%root%' OR name LIKE '%root%' OR role = 'root'";
        if ($conn->query($update_sql)) {
            echo "✅ Root kullanıcısı admin rolüne güncellendi!\n";
            
            // Kontrolü tekrarla
            $check_result = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'admin' OR name LIKE '%root%'");
            if ($check_result && $check_result->num_rows > 0) {
                echo "\n📋 Admin Kullanıcılar:\n";
                echo "─────────────────────\n";
                while ($row = $check_result->fetch_assoc()) {
                    echo "🆔 ID: {$row['id']} | 👤 {$row['name']} | 📧 {$row['email']} | 🔑 Role: {$row['role']}\n";
                }
            }
        } else {
            echo "❌ Update hatası: " . $conn->error . "\n";
        }
        
    } else {
        echo "📭 Hiç kullanıcı bulunamadı.\n";
    }
    
} catch (Exception $e) {
    echo "💥 Hata: " . $e->getMessage() . "\n";
}
?>
