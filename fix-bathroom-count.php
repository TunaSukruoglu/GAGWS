<?php
require_once 'db.php';

echo "=== BANYO SAYISI VERİLERİ ANALİZİ ===\n\n";

// Mevcut bathrooms değerlerini kontrol et
$query = "SELECT bathrooms, COUNT(*) as count FROM properties GROUP BY bathrooms ORDER BY bathrooms";
$result = $conn->query($query);

echo "Mevcut banyo sayısı değerleri:\n";
echo "==============================\n";

$all_values = [];
$total_properties = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bathroom_val = $row['bathrooms'] ?? 'NULL';
        if ($bathroom_val === '') $bathroom_val = 'BOŞ STRING';
        $count = $row['count'];
        $total_properties += $count;
        
        // 1, 2, 3 değerleri standart, diğerleri problematik
        $status = in_array($row['bathrooms'], ['1', '2', '3']) ? '✅' : '⚠️';
        echo "$status \"$bathroom_val\" ($count kayıt)\n";
        
        $all_values[] = $row['bathrooms'];
    }
}

echo "\nToplam property: $total_properties\n";
echo "Farklı banyo değeri sayısı: " . count($all_values) . "\n";

// Standart olmayan değerleri tespit et
echo "\n=== STANDART OLMAYAN DEĞERLER ===\n";
$standard_values = ['1', '2', '3'];
$non_standard_query = "SELECT bathrooms, COUNT(*) as count FROM properties 
                      WHERE bathrooms NOT IN ('" . implode("', '", $standard_values) . "') 
                      OR bathrooms IS NULL OR bathrooms = ''
                      GROUP BY bathrooms ORDER BY count DESC";
$non_standard_result = $conn->query($non_standard_query);

if ($non_standard_result && $non_standard_result->num_rows > 0) {
    echo "Düzeltilmesi gereken değerler:\n";
    echo "============================\n";
    while ($row = $non_standard_result->fetch_assoc()) {
        $bathroom_val = $row['bathrooms'] ?? 'NULL';
        if ($bathroom_val === '') $bathroom_val = 'BOŞ STRING';
        echo "- \"$bathroom_val\" ({$row['count']} kayıt)\n";
    }
} else {
    echo "✅ Tüm değerler standart!\n";
}

echo "\n=== DÖNÜŞTÜRMELERİ GERÇEKLEŞTİRİLİYOR ===\n";

// Dönüşüm kuralları
$conversions = [
    // NULL ve boş değerler
    null => '1',
    '' => '1',
    'Yok' => '1',  // Yok -> 1 banyo (minimum)
    '0' => '1',
    
    // 3'ten büyük değerleri 3 yap
    '4' => '3',
    '5' => '3', 
    '6' => '3',
    '7' => '3',
    '8' => '3',
    '9' => '3',
    '10' => '3',
    '10+' => '3'
];

$total_updated = 0;

foreach ($conversions as $old_value => $new_value) {
    // Mevcut kayıt sayısını kontrol et
    if ($old_value === null) {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE bathrooms IS NULL";
        $count_stmt = $conn->prepare($count_query);
    } elseif ($old_value === '') {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE bathrooms = ''";
        $count_stmt = $conn->prepare($count_query);
    } else {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE bathrooms = ?";
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
            $update_query = "UPDATE properties SET bathrooms = ? WHERE bathrooms IS NULL";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } elseif ($old_value === '') {
            $update_query = "UPDATE properties SET bathrooms = ? WHERE bathrooms = ''";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } else {
            $update_query = "UPDATE properties SET bathrooms = ? WHERE bathrooms = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_value, $old_value);
        }
        
        if ($update_stmt->execute()) {
            $affected = $update_stmt->affected_rows;
            echo "✅ Başarılı: $affected kayıt güncellendi\n";
            $total_updated += $affected;
        } else {
            echo "❌ Hata: " . $update_stmt->error . "\n";
        }
        $update_stmt->close();
        echo "\n";
    }
}

echo "=== SONUÇ ===\n";
echo "✅ Toplam dönüştürülen kayıt: $total_updated\n";

// Final durum
echo "\n=== FİNAL DURUM ===\n";
$final_query = "SELECT bathrooms, COUNT(*) as count FROM properties GROUP BY bathrooms ORDER BY bathrooms";
$final_result = $conn->query($final_query);

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $bathroom_val = $row['bathrooms'] ?? 'NULL';
        echo "- \"$bathroom_val\" ({$row['count']} kayıt)\n";
    }
}

// Kontrolle teyit edelim
$standard_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE bathrooms IN ('1', '2', '3')")->fetch_assoc()['count'];
$total_final = $conn->query("SELECT COUNT(*) as total FROM properties")->fetch_assoc()['total'];

echo "\nKONTROL:\n";
echo "========\n";
echo "Toplam property: $total_final\n";
echo "Standart değere sahip (1,2,3): $standard_count\n";

if ($total_final == $standard_count) {
    echo "\n🎉 BAŞARILI! Tüm banyo sayıları 1, 2, 3 değerlerine dönüştürüldü!\n";
} else {
    echo "\n⚠️ Hala standart olmayan değerler var.\n";
}

$conn->close();
?>
