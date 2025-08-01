<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Test verisi hazırla
$interior_features = json_encode([
    'Klima', 
    'Laminat Parke', 
    'Spot Aydınlatma', 
    'Amerikan Mutfak',
    'Ankastre Fırın',
    'Buzdolabı'
]);

$exterior_features = json_encode([
    'Bahçe', 
    'Otopark', 
    'Güvenlik Kamerası', 
    'Çocuk Oyun Alanı',
    'Yüzme Havuzu'
]);

$neighborhood_features = json_encode([
    'Okul Yakını', 
    'Hastane Yakını', 
    'AVM Yakını', 
    'Park Yakını',
    'Market Yakını'
]);

$transportation_features = json_encode([
    'Otobüs Durağı Yakını', 
    'Metro Yakını', 
    'Ana Yol Üzeri',
    'Havalimanı Yakını'
]);

$view_features = json_encode([
    'Deniz Manzarası', 
    'Şehir Manzarası', 
    'Dağ Manzarası',
    'Yeşil Alan Manzarası'
]);

$housing_type_features = json_encode([
    'Güvenlikli Site', 
    'Kapalı Otopark', 
    'Asansör',
    'Jeneratör',
    'Hidrofor'
]);

// ID 1'deki property'yi güncelle
$property_id = 1;

try {
    // Önce mevcut property'yi kontrol et
    $stmt = $conn->prepare("SELECT id, title FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
        echo "<h2>✅ Property bulundu: " . htmlspecialchars($property['title']) . "</h2>";
        
        // Özellikler kolonları var mı kontrol et
        $check_columns = $conn->query("SHOW COLUMNS FROM properties LIKE 'interior_features'");
        if ($check_columns->num_rows > 0) {
            echo "<p>✅ Özellik kolonları mevcut.</p>";
            
            // Property'yi güncelle
            $stmt = $conn->prepare("UPDATE properties SET 
                interior_features = ?, 
                exterior_features = ?, 
                neighborhood_features = ?, 
                transportation_features = ?, 
                view_features = ?, 
                housing_type_features = ? 
                WHERE id = ?");
            
            $stmt->bind_param("ssssssi", 
                $interior_features, 
                $exterior_features, 
                $neighborhood_features, 
                $transportation_features, 
                $view_features, 
                $housing_type_features, 
                $property_id
            );
            
            if ($stmt->execute()) {
                echo "<h3>🎉 Property başarıyla güncellendi!</h3>";
                echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<p><strong>Eklenen test özellikleri:</strong></p>";
                echo "<ul>";
                echo "<li><span style='color: #007bff;'>İç Özellikler:</span> " . count(json_decode($interior_features, true)) . " özellik</li>";
                echo "<li><span style='color: #28a745;'>Dış Özellikler:</span> " . count(json_decode($exterior_features, true)) . " özellik</li>";
                echo "<li><span style='color: #17a2b8;'>Muhit Özellikleri:</span> " . count(json_decode($neighborhood_features, true)) . " özellik</li>";
                echo "<li><span style='color: #ffc107;'>Ulaşım Özellikleri:</span> " . count(json_decode($transportation_features, true)) . " özellik</li>";
                echo "<li><span style='color: #dc3545;'>Manzara Özellikleri:</span> " . count(json_decode($view_features, true)) . " özellik</li>";
                echo "<li><span style='color: #6c757d;'>Konut Tipi Özellikleri:</span> " . count(json_decode($housing_type_features, true)) . " özellik</li>";
                echo "</ul>";
                echo "</div>";
                echo "<p><a href='property-details.php?id=$property_id' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📄 İlan detayını görüntüle</a></p>";
            } else {
                echo "<p style='color: red;'>❌ Güncelleme hatası: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Özellik kolonları bulunamadı. Önce update_properties_table.php çalıştırın.</p>";
            echo "<p><a href='update_properties_table.php'>🔧 Veritabanını güncelle</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Property bulunamadı (ID: $property_id)</p>";
        
        // Mevcut property'leri listele
        $stmt = $conn->prepare("SELECT id, title FROM properties LIMIT 5");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<p>Mevcut property'ler:</p>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>ID: " . $row['id'] . " - " . htmlspecialchars($row['title']) . "</li>";
            }
            echo "</ul>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
}

$conn->close();
?>
