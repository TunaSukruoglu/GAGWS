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
        echo "Mevcut property bulundu: " . htmlspecialchars($property['title']) . "<br>";
        
        // Özellikler kolonları var mı kontrol et
        $check_columns = $conn->query("SHOW COLUMNS FROM properties LIKE 'interior_features'");
        if ($check_columns->num_rows > 0) {
            echo "Özellik kolonları mevcut.<br>";
            
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
                echo "<h3>✅ Property başarıyla güncellendi!</h3>";
                echo "<p>Test özellikleri eklendi:</p>";
                echo "<ul>";
                echo "<li>İç Özellikler: " . count(json_decode($interior_features, true)) . " özellik</li>";
                echo "<li>Dış Özellikler: " . count(json_decode($exterior_features, true)) . " özellik</li>";
                echo "<li>Muhit Özellikleri: " . count(json_decode($neighborhood_features, true)) . " özellik</li>";
                echo "<li>Ulaşım Özellikleri: " . count(json_decode($transportation_features, true)) . " özellik</li>";
                echo "<li>Manzara Özellikleri: " . count(json_decode($view_features, true)) . " özellik</li>";
                echo "<li>Konut Tipi Özellikleri: " . count(json_decode($housing_type_features, true)) . " özellik</li>";
                echo "</ul>";
                echo "<p><a href='property-details.php?id=$property_id'>İlan detayını görüntüle</a></p>";
            } else {
                echo "❌ Güncelleme hatası: " . $stmt->error;
            }
        } else {
            echo "❌ Özellik kolonları bulunamadı. Önce update_properties_table.php çalıştırın.";
        }
    } else {
        echo "❌ Property bulunamadı (ID: $property_id)";
    }
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage();
}

$conn->close();
?>
