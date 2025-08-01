<?php
require_once 'db.php';

echo "=== PARKING VERİLERİNİ 3 STANDART DEĞERE DÖNÜŞTÜRLEİM ===\n\n";

// Yeni standart: Sadece 3 seçenek
// 1. "Otopark Yok"
// 2. "Açık Otopark" 
// 3. "Kapalı Otopark"

echo "Mevcut parking değerleri analiz ediliyor...\n";
echo "==========================================\n";

$current_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$current_result = $conn->query($current_query);

if ($current_result && $current_result->num_rows > 0) {
    while ($row = $current_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        if ($parking_val === '') $parking_val = 'BOŞ STRING';
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
    }
}

echo "\n=== DÖNÜŞTÜRMELERİ GERÇEKLEŞTİRİLİYOR ===\n\n";

// Dönüşüm kuralları
$conversions = [
    // Otopark Yok kategorisi
    'Yok' => 'Otopark Yok',
    'none' => 'Otopark Yok',
    '' => 'Otopark Yok', // Boş değerler
    
    // Açık Otopark kategorisi
    'var' => 'Açık Otopark',
    'Var' => 'Açık Otopark', 
    'Açık Otopark' => 'Açık Otopark', // Zaten doğru
    'open' => 'Açık Otopark',
    'Yarı Açık Otopark' => 'Açık Otopark',
    'Bahçe İçi Park' => 'Açık Otopark',
    'Sokak Parkı' => 'Açık Otopark',
    'Ücretsiz Park' => 'Açık Otopark',
    'Vale Park' => 'Açık Otopark',
    'Otopark Var (Eski Kayıt)' => 'Açık Otopark',
    'Otopark Var (Eski Kayıt 2)' => 'Açık Otopark',
    
    // Kapalı Otopark kategorisi
    'Kapalı Otopark' => 'Kapalı Otopark', // Zaten doğru
    'closed' => 'Kapalı Otopark',
    'Yer Altı Otoparkı' => 'Kapalı Otopark',
    'Ücretli Park' => 'Kapalı Otopark',
    '1 Araç' => 'Kapalı Otopark',
    '2 Araç' => 'Kapalı Otopark',
    '3 Araç' => 'Kapalı Otopark',
    '4 Araç' => 'Kapalı Otopark',
    '5+ Araç' => 'Kapalı Otopark',
    '3+ Araç' => 'Kapalı Otopark',
    
    // Karma seçenekler - en uygun kategoriye
    'Açık + Kapalı Park' => 'Kapalı Otopark',
    'Misafir Parkı Var' => 'Açık Otopark',
    'Engelli Parkı Var' => 'Açık Otopark'
];

$total_updated = 0;
$success = true;

foreach ($conversions as $old_value => $new_value) {
    // Mevcut kayıt sayısını kontrol et
    if ($old_value === '') {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = '' OR parking IS NULL";
    } else {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = ?";
    }
    
    $count_stmt = $conn->prepare($count_query);
    if ($old_value !== '') {
        $count_stmt->bind_param("s", $old_value);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['count'];
    
    if ($count > 0) {
        echo "\"$old_value\" → \"$new_value\" ($count kayıt dönüştürülüyor)\n";
        
        // Güncelleme yap
        if ($old_value === '') {
            $update_query = "UPDATE properties SET parking = ? WHERE parking = '' OR parking IS NULL";
        } else {
            $update_query = "UPDATE properties SET parking = ? WHERE parking = ?";
        }
        
        $update_stmt = $conn->prepare($update_query);
        if ($old_value === '') {
            $update_stmt->bind_param("s", $new_value);
        } else {
            $update_stmt->bind_param("ss", $new_value, $old_value);
        }
        
        if ($update_stmt->execute()) {
            $affected = $update_stmt->affected_rows;
            echo "✅ Başarılı: $affected kayıt güncellendi\n";
            $total_updated += $affected;
        } else {
            echo "❌ Hata: " . $update_stmt->error . "\n";
            $success = false;
        }
        $update_stmt->close();
    }
    $count_stmt->close();
}

echo "\n=== SONUÇ ===\n";
if ($success) {
    echo "✅ Tüm dönüştürmeler başarıyla tamamlandı!\n";
    echo "📊 Toplam güncellenen kayıt: $total_updated\n";
} else {
    echo "⚠️ Bazı güncellemelerde hata oluştu.\n";
}

// Final durum
echo "\n=== YENİ PARKING VERİ DAĞILIMI ===\n";
$final_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY parking";
$final_result = $conn->query($final_query);

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
    }
}

// Standartlaştırma kontrolü
echo "\n=== STANDARTLAŞTIRMA KONTOLÜ ===\n";
$standard_values = ['Otopark Yok', 'Açık Otopark', 'Kapalı Otopark'];
$non_standard_query = "SELECT parking, COUNT(*) as count FROM properties WHERE parking NOT IN ('" . 
                     implode("', '", $standard_values) . "') AND parking IS NOT NULL GROUP BY parking";
$non_standard_result = $conn->query($non_standard_query);

if ($non_standard_result && $non_standard_result->num_rows > 0) {
    echo "⚠️ Standart olmayan değerler tespit edildi:\n";
    while ($row = $non_standard_result->fetch_assoc()) {
        echo "- \"" . $row['parking'] . "\" ({$row['count']} kayıt)\n";
    }
} else {
    echo "✅ Tüm parking verileri standart değerlere dönüştürüldü!\n";
}

$conn->close();
?>
