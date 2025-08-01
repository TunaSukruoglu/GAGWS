<?php
require_once 'db.php';

echo "=== VERİTABANINDAN ESKİ PARKING DEĞERLERİNİ TEMİZLEYELİM ===\n\n";

// Önce mevcut durumu görelim
echo "MEVCUT DURUM:\n";
echo "=============\n";
$current_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$current_result = $conn->query($current_query);

$all_values = [];
if ($current_result && $current_result->num_rows > 0) {
    while ($row = $current_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        if ($parking_val === '') $parking_val = 'BOŞ STRING';
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
        $all_values[] = $row['parking'];
    }
}

// Standart değerler
$standard_values = ['Otopark Yok', 'Açık Otopark', 'Kapalı Otopark'];

echo "\nSTANDART DEĞERLER:\n";
echo "==================\n";
foreach ($standard_values as $std) {
    echo "- \"$std\"\n";
}

echo "\n=== TEMİZLEME İŞLEMLERİ ===\n\n";

// İlk önce tüm NULL, boş ve standart olmayan değerleri dönüştürelim
$conversion_map = [
    // NULL ve boş değerler
    null => 'Otopark Yok',
    '' => 'Otopark Yok',
    
    // Otopark yok anlamına gelenler
    'Yok' => 'Otopark Yok',
    'none' => 'Otopark Yok',
    'yok' => 'Otopark Yok',
    
    // Açık otopark anlamına gelenler
    'var' => 'Açık Otopark',
    'Var' => 'Açık Otopark',
    'open' => 'Açık Otopark',
    'açık' => 'Açık Otopark',
    'Otopark Var (Eski Kayıt)' => 'Açık Otopark',
    'Otopark Var (Eski Kayıt 2)' => 'Açık Otopark',
    'Yarı Açık Otopark' => 'Açık Otopark',
    'Bahçe İçi Park' => 'Açık Otopark',
    'Sokak Parkı' => 'Açık Otopark',
    'Ücretsiz Park' => 'Açık Otopark',
    'Vale Park' => 'Açık Otopark',
    'Misafir Parkı Var' => 'Açık Otopark',
    'Engelli Parkı Var' => 'Açık Otopark',
    
    // Kapalı otopark anlamına gelenler
    'closed' => 'Kapalı Otopark',
    'kapalı' => 'Kapalı Otopark',
    'Yer Altı Otoparkı' => 'Kapalı Otopark',
    'Ücretli Park' => 'Kapalı Otopark',
    'Açık + Kapalı Park' => 'Kapalı Otopark',
    '1 Araç' => 'Kapalı Otopark',
    '2 Araç' => 'Kapalı Otopark',
    '3 Araç' => 'Kapalı Otopark',
    '4 Araç' => 'Kapalı Otopark',
    '5+ Araç' => 'Kapalı Otopark',
    '3+ Araç' => 'Kapalı Otopark'
];

$total_converted = 0;

foreach ($conversion_map as $old_value => $new_value) {
    // Kayıt sayısını kontrol et
    if ($old_value === null) {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking IS NULL";
        $count_stmt = $conn->prepare($count_query);
    } elseif ($old_value === '') {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = ''";
        $count_stmt = $conn->prepare($count_query);
    } else {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = ?";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->bind_param("s", $old_value);
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['count'];
    $count_stmt->close();
    
    if ($count > 0) {
        $display_old = $old_value === null ? 'NULL' : ($old_value === '' ? 'BOŞ STRING' : "\"$old_value\"");
        echo "$display_old → \"$new_value\" ($count kayıt dönüştürülüyor)\n";
        
        // Güncelleme yap
        if ($old_value === null) {
            $update_query = "UPDATE properties SET parking = ? WHERE parking IS NULL";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } elseif ($old_value === '') {
            $update_query = "UPDATE properties SET parking = ? WHERE parking = ''";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } else {
            $update_query = "UPDATE properties SET parking = ? WHERE parking = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_value, $old_value);
        }
        
        if ($update_stmt->execute()) {
            $affected = $update_stmt->affected_rows;
            echo "✅ Başarılı: $affected kayıt güncellendi\n";
            $total_converted += $affected;
        } else {
            echo "❌ Hata: " . $update_stmt->error . "\n";
        }
        $update_stmt->close();
        echo "\n";
    }
}

// Şimdi standart olmayan kalan değerleri kontrol edelim
echo "=== STANDART OLMAYAN DEĞERLER KONTROL EDİLİYOR ===\n";
$remaining_query = "SELECT DISTINCT parking FROM properties WHERE parking NOT IN ('" . 
                  implode("', '", $standard_values) . "') AND parking IS NOT NULL AND parking != ''";
$remaining_result = $conn->query($remaining_query);

if ($remaining_result && $remaining_result->num_rows > 0) {
    echo "⚠️ Hala standart olmayan değerler var:\n";
    while ($row = $remaining_result->fetch_assoc()) {
        $non_standard = $row['parking'];
        echo "- \"$non_standard\"\n";
        
        // Bu değerleri de dönüştürelim - varsayılan olarak "Açık Otopark" yapalım
        $convert_query = "UPDATE properties SET parking = 'Açık Otopark' WHERE parking = ?";
        $convert_stmt = $conn->prepare($convert_query);
        $convert_stmt->bind_param("s", $non_standard);
        
        if ($convert_stmt->execute()) {
            $affected = $convert_stmt->affected_rows;
            echo "  → \"Açık Otopark\" olarak dönüştürüldü ($affected kayıt)\n";
            $total_converted += $affected;
        }
        $convert_stmt->close();
    }
} else {
    echo "✅ Standart olmayan değer kalmadı!\n";
}

echo "\n=== SONUÇ ===\n";
echo "✅ Toplam dönüştürülen kayıt: $total_converted\n";

// Final durum
echo "\n=== FİNAL DURUM ===\n";
$final_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY parking";
$final_result = $conn->query($final_query);

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
    }
}

// Kontrolle teyit edelim
$total_properties = $conn->query("SELECT COUNT(*) as total FROM properties")->fetch_assoc()['total'];
$standard_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE parking IN ('" . 
                             implode("', '", $standard_values) . "')")->fetch_assoc()['count'];

echo "\nTOTAL KONTROL:\n";
echo "==============\n";
echo "Toplam property: $total_properties\n";
echo "Standart değere sahip: $standard_count\n";
echo "Standart dışı: " . ($total_properties - $standard_count) . "\n";

if ($total_properties == $standard_count) {
    echo "\n🎉 BAŞARILI! Tüm parking verileri sadece 3 standart değere dönüştürüldü!\n";
} else {
    echo "\n⚠️ Hala bazı kayıtlarda standart olmayan değerler var.\n";
}

$conn->close();
?>
