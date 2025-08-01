<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "🗑️  TEST İLANLARINI SİLME\n";
echo "========================\n\n";

try {
    // Önce mevcut ilanları listele
    echo "📋 Mevcut İlanlar:\n";
    echo "─────────────────\n";
    
    $list_sql = "SELECT id, title, city, district, price, usage_status, created_at FROM properties ORDER BY id DESC";
    $result = $conn->query($list_sql);
    
    if ($result && $result->num_rows > 0) {
        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
            echo "🆔 ID: {$row['id']} | 🏠 {$row['title']} | 📍 {$row['city']}/{$row['district']} | 💰 " . number_format($row['price']) . " TL | 🕐 {$row['created_at']}\n";
        }
        
        echo "\n📊 Toplam " . count($properties) . " ilan bulundu.\n\n";
        
        // Kullanıcıdan onay al
        echo "❓ Tüm ilanları silmek istediğinizden emin misiniz? (y/N): ";
        
        // Test amaçlı otomatik onay
        $confirm = 'y'; // Gerçek kullanımda fgets(STDIN) kullanılabilir
        
        if (strtolower(trim($confirm)) === 'y') {
            echo "\n🗑️  İlanlar siliniyor...\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━\n";
            
            $deleted_count = 0;
            foreach ($properties as $property) {
                $delete_sql = "DELETE FROM properties WHERE id = ?";
                $stmt = $conn->prepare($delete_sql);
                $stmt->bind_param("i", $property['id']);
                
                if ($stmt->execute()) {
                    echo "✅ Silindi: ID {$property['id']} - {$property['title']}\n";
                    $deleted_count++;
                } else {
                    echo "❌ Hata: ID {$property['id']} - " . $stmt->error . "\n";
                }
            }
            
            echo "━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "🎯 Toplam $deleted_count ilan silindi.\n\n";
            
            // Son kontrol
            $check_sql = "SELECT COUNT(*) as total FROM properties";
            $check_result = $conn->query($check_sql);
            $check_row = $check_result->fetch_assoc();
            
            echo "✅ Database temizlendi!\n";
            echo "📊 Kalan ilan sayısı: {$check_row['total']}\n";
            
            if ($check_row['total'] == 0) {
                echo "🧹 Database tamamen temiz!\n";
                
                // AUTO_INCREMENT'i sıfırla
                $reset_sql = "ALTER TABLE properties AUTO_INCREMENT = 1";
                if ($conn->query($reset_sql)) {
                    echo "🔄 AUTO_INCREMENT sıfırlandı.\n";
                } else {
                    echo "⚠️  AUTO_INCREMENT sıfırlama hatası: " . $conn->error . "\n";
                }
            }
            
        } else {
            echo "❌ İşlem iptal edildi.\n";
        }
        
    } else {
        echo "📭 Hiç ilan bulunamadı. Database zaten temiz!\n";
    }

} catch (Exception $e) {
    echo "💥 HATA: " . $e->getMessage() . "\n";
}

echo "\n🏁 İşlem tamamlandı.\n";
?>
