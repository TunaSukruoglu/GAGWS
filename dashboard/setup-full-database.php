<?php
session_start();
require_once 'db.php';

echo "<h2>🏗️ Emlak Sistemi - Veritabanı Tam Kurulumu</h2>";

// Eklenecek tüm sütunlar ve tanımları
$new_columns = [
    // Temel Bilgiler
    'listing_type' => "VARCHAR(20) DEFAULT 'Satılık' COMMENT 'Satılık/Kiralık'",
    'subcategory' => "VARCHAR(50) DEFAULT NULL COMMENT 'Alt kategori'",
    
    // Alan Bilgileri
    'area_gross' => "DECIMAL(10,2) DEFAULT NULL COMMENT 'Brüt m²'",
    'area_net' => "DECIMAL(10,2) DEFAULT NULL COMMENT 'Net m²'",
    'area' => "DECIMAL(10,2) DEFAULT NULL COMMENT 'Ana alan bilgisi'",
    
    // Oda ve Mekan Bilgileri
    'room_count' => "VARCHAR(10) DEFAULT NULL COMMENT 'Oda sayısı (3+1 formatında)'",
    'bedrooms' => "INT DEFAULT NULL COMMENT 'Yatak odası sayısı'",
    'bathrooms' => "INT DEFAULT NULL COMMENT 'Banyo sayısı'",
    'bathroom_count' => "VARCHAR(20) DEFAULT NULL COMMENT 'Banyo sayısı detaylı'",
    'kitchen' => "VARCHAR(50) DEFAULT NULL COMMENT 'Mutfak tipi'",
    'balcony' => "VARCHAR(50) DEFAULT NULL COMMENT 'Balkon durumu'",
    
    // Bina Bilgileri
    'building_age' => "VARCHAR(20) DEFAULT NULL COMMENT 'Bina yaşı'",
    'floor_location' => "VARCHAR(20) DEFAULT NULL COMMENT 'Bulunduğu kat'",
    'total_floors' => "VARCHAR(20) DEFAULT NULL COMMENT 'Toplam kat sayısı'",
    'floor' => "INT DEFAULT NULL COMMENT 'Kat numarası (sayısal)'",
    'year_built' => "INT DEFAULT NULL COMMENT 'Yapım yılı'",
    
    // Özellikler
    'heating' => "VARCHAR(50) DEFAULT NULL COMMENT 'Isıtma sistemi'",
    'elevator' => "VARCHAR(10) DEFAULT NULL COMMENT 'Asansör var/yok'",
    'parking' => "VARCHAR(50) DEFAULT NULL COMMENT 'Otopark durumu'",
    'furnished' => "TINYINT(1) DEFAULT 0 COMMENT 'Eşyalı mı'",
    'usage_status' => "VARCHAR(50) DEFAULT NULL COMMENT 'Kullanım durumu'",
    
    // Mali Bilgiler
    'dues' => "DECIMAL(10,2) DEFAULT NULL COMMENT 'Aidat (TL)'",
    'credit_eligible' => "TINYINT(1) DEFAULT 0 COMMENT 'Krediye uygun mu'",
    'deed_status' => "VARCHAR(50) DEFAULT NULL COMMENT 'Tapu durumu'",
    'property_number' => "VARCHAR(100) DEFAULT NULL COMMENT 'Taşınmaz numarası'",
    'exchange' => "VARCHAR(10) DEFAULT NULL COMMENT 'Takaslı mı'",
    
    // Adres Detayları
    'neighborhood' => "VARCHAR(100) DEFAULT NULL COMMENT 'Mahalle'",
    'location_type' => "VARCHAR(20) DEFAULT NULL COMMENT 'site/standalone'",
    'site_name' => "VARCHAR(200) DEFAULT NULL COMMENT 'Site adı'",
    'address_details' => "TEXT DEFAULT NULL COMMENT 'Adres detayları'",
    'in_site' => "TINYINT(1) DEFAULT 0 COMMENT 'Site içinde mi'",
    
    // Özellik Grupları (JSON formatında)
    'cephe' => "JSON DEFAULT NULL COMMENT 'Cephe yönleri'",
    'ic_ozellikler' => "JSON DEFAULT NULL COMMENT 'İç özellikler'",
    'dis_ozellikler' => "JSON DEFAULT NULL COMMENT 'Dış özellikler'",
    'muhit' => "JSON DEFAULT NULL COMMENT 'Çevre özellikleri'",
    'ulasim' => "JSON DEFAULT NULL COMMENT 'Ulaşım imkanları'",
    'manzara' => "JSON DEFAULT NULL COMMENT 'Manzara özellikleri'",
    
    // Resim Yönetimi
    'main_image' => "VARCHAR(255) DEFAULT NULL COMMENT 'Ana resim'",
    'image_order' => "JSON DEFAULT NULL COMMENT 'Resim sıralaması'",
    
    // Meta Bilgiler
    'featured' => "TINYINT(1) DEFAULT 0 COMMENT 'Öne çıkarılmış mı'",
    'views' => "INT DEFAULT 0 COMMENT 'Görüntülenme sayısı'",
    'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncellenme tarihi'"
];

// Mevcut sütunları kontrol et
$existing_columns = [];
$result = $conn->query("SHOW COLUMNS FROM properties");
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

// Eksik sütunları tespit et
$missing_columns = [];
foreach ($new_columns as $column => $definition) {
    if (!in_array($column, $existing_columns)) {
        $missing_columns[$column] = $definition;
    }
}

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>📊 Durum Raporu</h3>";
echo "<p><strong>Toplam sütun:</strong> " . count($new_columns) . "</p>";
echo "<p><strong>Mevcut sütun:</strong> " . (count($new_columns) - count($missing_columns)) . "</p>";
echo "<p><strong>Eksik sütun:</strong> " . count($missing_columns) . "</p>";
echo "</div>";

if (!empty($missing_columns)) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>⚠️ Eksik Sütunlar (" . count($missing_columns) . " adet)</h3>";
    
    if (isset($_GET['add_all'])) {
        echo "<h4>🔧 Sütunlar ekleniyor...</h4>";
        $success_count = 0;
        $error_count = 0;
        
        foreach ($missing_columns as $column => $definition) {
            try {
                $sql = "ALTER TABLE properties ADD COLUMN `$column` $definition";
                if ($conn->query($sql)) {
                    echo "<div style='color: #28a745; margin: 5px 0;'>✅ <strong>$column</strong> eklendi</div>";
                    $success_count++;
                } else {
                    echo "<div style='color: #dc3545; margin: 5px 0;'>❌ <strong>$column</strong> hatası: " . $conn->error . "</div>";
                    $error_count++;
                }
            } catch (Exception $e) {
                echo "<div style='color: #dc3545; margin: 5px 0;'>❌ <strong>$column</strong> hatası: " . $e->getMessage() . "</div>";
                $error_count++;
            }
        }
        
        echo "<div style='background: " . ($error_count > 0 ? '#f8d7da' : '#d4edda') . "; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4>📈 Sonuç</h4>";
        echo "<p>✅ Başarılı: $success_count</p>";
        echo "<p>❌ Hatalı: $error_count</p>";
        if ($error_count == 0) {
            echo "<p><strong>🎉 Tüm sütunlar başarıyla eklendi!</strong></p>";
        }
        echo "</div>";
        
        if ($error_count == 0) {
            echo "<script>setTimeout(() => location.reload(), 3000);</script>";
        }
        
    } else {
        echo "<div style='max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px;'>";
        foreach ($missing_columns as $column => $definition) {
            echo "<div style='margin: 8px 0; padding: 8px; background: white; border-radius: 4px;'>";
            echo "<strong>$column</strong><br>";
            echo "<small style='color: #666;'>$definition</small>";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='?add_all=1' onclick='return confirm(\"" . count($missing_columns) . " sütun eklenecek. Devam etmek istiyor musunuz?\")' ";
        echo "style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>";
        echo "🚀 Tüm Sütunları Ekle (" . count($missing_columns) . " adet)</a>";
        echo "</div>";
    }
    echo "</div>";
    
} else {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 Mükemmel!</h3>";
    echo "<p>Tüm gerekli sütunlar veritabanında mevcut. Emlak sisteminiz tam donanımlı!</p>";
    echo "</div>";
}

// Güncellenen tablo yapısını göster
echo "<h3>📋 Güncel Tablo Yapısı</h3>";
$result = $conn->query("SHOW COLUMNS FROM properties");
echo "<div style='max-height: 400px; overflow-y: auto;'>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8f9fa; position: sticky; top: 0;'>";
echo "<th style='padding: 10px;'>Sütun</th>";
echo "<th style='padding: 10px;'>Tip</th>";
echo "<th style='padding: 10px;'>Null</th>";
echo "<th style='padding: 10px;'>Default</th>";
echo "<th style='padding: 10px;'>Açıklama</th>";
echo "</tr>";

while ($row = $result->fetch_assoc()) {
    $is_new = array_key_exists($row['Field'], $new_columns);
    $bg_color = $is_new ? '#e8f5e8' : '#ffffff';
    
    echo "<tr style='background: $bg_color;'>";
    echo "<td style='padding: 8px;'><strong>" . htmlspecialchars($row['Field']) . "</strong></td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars($row['Comment'] ?? '') . "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<div style='margin: 30px 0; text-align: center;'>";
echo "<a href='add-property.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>🏠 İlan Ekleme Sayfası</a>";
echo "<a href='form-test.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>🧪 Form Test</a>";
echo "<a href='dashboard.php' style='background: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; margin: 10px;'>📊 Dashboard</a>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #333; }
table { width: 100%; margin: 20px 0; }
th, td { border: 1px solid #ddd; text-align: left; }
.btn { display: inline-block; margin: 5px; }
</style>
