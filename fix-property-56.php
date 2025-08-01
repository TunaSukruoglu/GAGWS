<?php
include 'db.php';

header('Content-Type: text/plain; charset=utf-8');

// 56 numaralı ilanın description alanını kontrol et ve düzelt
$property_id = 56;

echo "=== İLAN 56 DESCRİPTİON TEMİZLİĞİ ===\n\n";

// Mevcut description'ı al
$stmt = $conn->prepare("SELECT description FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if ($property) {
    $current_desc = $property['description'];
    
    echo "Mevcut Description:\n";
    echo "-------------------\n";
    echo $current_desc . "\n\n";
    
    echo "Character sayısı: " . strlen($current_desc) . "\n";
    echo "Tekrar kontrolü:\n";
    
    // Tekrarlanan kısmı tespit et
    $repeated_text = "İlan Database Debug - ID: 55";
    $count = substr_count($current_desc, $repeated_text);
    
    echo "'{$repeated_text}' metni {$count} kez tekrarlanmış\n\n";
    
    if ($count > 1) {
        // Temizle
        $cleaned_desc = str_replace($repeated_text, "", $current_desc);
        $cleaned_desc = trim($cleaned_desc);
        
        // Eğer tamamen boş kaldıysa varsayılan açıklama koy
        if (empty($cleaned_desc)) {
            $cleaned_desc = "Bu güzel emlak için detaylı bilgi almak üzere bizimle iletişime geçebilirsiniz.";
        }
        
        echo "Temizlenen Description:\n";
        echo "----------------------\n";
        echo $cleaned_desc . "\n\n";
        
        // Güncelle
        $update_stmt = $conn->prepare("UPDATE properties SET description = ? WHERE id = ?");
        $update_stmt->bind_param("si", $cleaned_desc, $property_id);
        
        if ($update_stmt->execute()) {
            echo "✅ Description başarıyla temizlendi ve güncellendi!\n";
        } else {
            echo "❌ Güncelleme hatası: " . $conn->error . "\n";
        }
    } else {
        echo "✅ Description zaten temiz!\n";
    }
} else {
    echo "❌ İlan bulunamadı!\n";
}

echo "\n=== KONUM VERİLERİNİ DÜZELT ===\n";

// 56 numaralı ilanın konum verilerini kontrol et
$stmt = $conn->prepare("SELECT city, district, neighborhood, il, ilce, mahalle FROM properties WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();
$location_data = $result->fetch_assoc();

if ($location_data) {
    echo "Mevcut konum verileri:\n";
    foreach ($location_data as $field => $value) {
        printf("  %-15s: %s\n", $field, $value ?? 'NULL');
    }
    
    // Eski verilerden yenilere kopyala
    $updates = [];
    $params = [];
    $types = "";
    
    if (empty($location_data['il']) && !empty($location_data['city'])) {
        $updates[] = "il = ?";
        $params[] = $location_data['city'];
        $types .= "s";
        echo "\n🔄 İl alanı city'den kopyalanacak: " . $location_data['city'];
    }
    
    if (empty($location_data['ilce']) && !empty($location_data['district'])) {
        $updates[] = "ilce = ?";
        $params[] = $location_data['district'];
        $types .= "s";
        echo "\n🔄 İlçe alanı district'ten kopyalanacak: " . $location_data['district'];
    }
    
    if (empty($location_data['mahalle']) && !empty($location_data['neighborhood'])) {
        $updates[] = "mahalle = ?";
        $params[] = $location_data['neighborhood'];
        $types .= "s";
        echo "\n🔄 Mahalle alanı neighborhood'dan kopyalanacak: " . $location_data['neighborhood'];
    }
    
    // Örnek veri ekle (şu an hepsi boş)
    if (empty($updates)) {
        echo "\n📝 Örnek konum verileri ekleniyor...\n";
        $updates = ["il = ?", "ilce = ?", "mahalle = ?"];
        $params = ["İstanbul", "Güngören", "Merter"];
        $types = "sss";
    }
    
    if (!empty($updates)) {
        $params[] = $property_id;
        $types .= "i";
        
        $sql = "UPDATE properties SET " . implode(", ", $updates) . " WHERE id = ?";
        $update_stmt = $conn->prepare($sql);
        $update_stmt->bind_param($types, ...$params);
        
        if ($update_stmt->execute()) {
            echo "\n✅ Konum verileri güncellendi!\n";
        } else {
            echo "\n❌ Konum güncelleme hatası: " . $conn->error . "\n";
        }
    }
}
?>
