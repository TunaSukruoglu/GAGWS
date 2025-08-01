<?php
// Güvenli veritabanı güncelleme scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Güvenli Veritabanı Güncelleme</h2>";

try {
    session_start();
    require_once 'db.php';
    
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Veritabanı bağlantısı kurulamadı");
    }
    
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "✅ Veritabanı bağlantısı başarılı";
    echo "</div>";
    
    // Önce mevcut sütunları kontrol et
    $existing_columns = [];
    $result = $conn->query("SHOW COLUMNS FROM properties");
    
    if (!$result) {
        throw new Exception("Properties tablosu bulunamadı: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<p><strong>Mevcut sütun sayısı:</strong> " . count($existing_columns) . "</p>";
    
    // Temel eksik sütunları tanımla (en önemlileri)
    $basic_columns = [
        'listing_type' => "VARCHAR(20) DEFAULT 'Satılık'",
        'area_gross' => "DECIMAL(10,2) DEFAULT NULL",
        'area_net' => "DECIMAL(10,2) DEFAULT NULL",
        'room_count' => "VARCHAR(10) DEFAULT NULL",
        'bedrooms' => "INT DEFAULT NULL",
        'bathrooms' => "INT DEFAULT NULL",
        'heating' => "VARCHAR(50) DEFAULT NULL",
        'elevator' => "VARCHAR(10) DEFAULT NULL",
        'parking' => "VARCHAR(50) DEFAULT NULL",
        'furnished' => "TINYINT(1) DEFAULT 0",
        'main_image' => "VARCHAR(255) DEFAULT NULL",
        'featured' => "TINYINT(1) DEFAULT 0"
    ];
    
    // Eksik sütunları bul
    $missing_basic = [];
    foreach ($basic_columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            $missing_basic[$column] = $definition;
        }
    }
    
    if (empty($missing_basic)) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h3>🎉 Mükemmel!</h3>";
        echo "<p>Temel sütunlar zaten mevcut. Sisteminiz hazır!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h3>⚠️ Eksik Temel Sütunlar (" . count($missing_basic) . " adet)</h3>";
        
        if (isset($_GET['add_basic'])) {
            echo "<h4>Sütunlar ekleniyor...</h4>";
            $success = 0;
            $errors = 0;
            
            foreach ($missing_basic as $column => $definition) {
                try {
                    $sql = "ALTER TABLE properties ADD COLUMN `$column` $definition";
                    if ($conn->query($sql)) {
                        echo "<div style='color: green;'>✅ $column eklendi</div>";
                        $success++;
                    } else {
                        echo "<div style='color: red;'>❌ $column hatası: " . $conn->error . "</div>";
                        $errors++;
                    }
                } catch (Exception $e) {
                    echo "<div style='color: red;'>❌ $column hatası: " . $e->getMessage() . "</div>";
                    $errors++;
                }
            }
            
            echo "<div style='background: " . ($errors > 0 ? '#f8d7da' : '#d4edda') . "; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
            echo "<h4>Sonuç:</h4>";
            echo "<p>✅ Başarılı: $success | ❌ Hatalı: $errors</p>";
            echo "</div>";
            
            if ($errors == 0) {
                echo "<script>setTimeout(() => window.location.reload(), 2000);</script>";
            }
            
        } else {
            echo "<ul>";
            foreach ($missing_basic as $column => $definition) {
                echo "<li><strong>$column</strong> - $definition</li>";
            }
            echo "</ul>";
            
            echo "<a href='?add_basic=1' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin: 10px 0;'>";
            echo "🚀 Temel Sütunları Ekle (" . count($missing_basic) . " adet)</a>";
        }
        echo "</div>";
    }
    
    // Mevcut tablo yapısını göster
    echo "<h3>📋 Mevcut Sütunlar</h3>";
    echo "<div style='max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'><th style='padding: 8px;'>Sütun</th><th style='padding: 8px;'>Tip</th></tr>";
    
    $result->data_seek(0); // Cursor'u başa al
    while ($row = $result->fetch_assoc()) {
        $is_basic = array_key_exists($row['Field'], $basic_columns);
        $bg = $is_basic ? '#e8f5e8' : '#ffffff';
        echo "<tr style='background: $bg;'>";
        echo "<td style='padding: 6px;'>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td style='padding: 6px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<h3>❌ Hata Oluştu</h3>";
    echo "<p><strong>Hata:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Satır:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Dosya:</strong> " . $e->getFile() . "</p>";
    echo "</div>";
} catch (Error $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<h3>❌ Fatal Hata</h3>";
    echo "<p><strong>Hata:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Satır:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<div style='margin: 20px 0; text-align: center;'>";
echo "<a href='add-property.php' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>🏠 İlan Ekle</a>";
echo "<a href='dashboard.php' style='background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>📊 Dashboard</a>";
echo "<a href='db-test.php' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;'>🧪 DB Test</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #333; }
table { width: 100%; }
th, td { border: 1px solid #ddd; text-align: left; }
</style>
