<?php
require_once 'db.php';

echo "=== KULLANIM DURUMU VERİLERİNİ TEMİZLEYELİM ===\n\n";

// Önce mevcut durumu görelim
echo "MEVCUT DURUM:\n";
echo "=============\n";
$current_query = "SELECT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status ORDER BY count DESC";
$current_result = $conn->query($current_query);

if ($current_result && $current_result->num_rows > 0) {
    while ($row = $current_result->fetch_assoc()) {
        $usage_val = $row['usage_status'] ?? 'NULL';
        if ($usage_val === '') $usage_val = 'BOŞ STRING';
        echo "- \"$usage_val\" ({$row['count']} kayıt)\n";
    }
}

// Standart değerler
$standard_values = ['Boş', 'Kiracılı', 'Malik Kullanımında', 'Yatırım Amaçlı'];

echo "\nSTANDART DEĞERLER:\n";
echo "==================\n";
foreach ($standard_values as $std) {
    echo "- \"$std\"\n";
}

echo "\n=== DÖNÜŞTÜRMELERİ GERÇEKLEŞTİRİYOR ===\n\n";

// Dönüşüm kuralları
$conversions = [
    // Eski kayıtları standart değerlere dönüştür
    'boş' => 'Boş',
    'dolu' => 'Kiracılı', // Dolu genellikle kiracılı anlamına gelir
    'empty' => 'Boş',
    'occupied' => 'Kiracılı',
    'owner' => 'Malik Kullanımında',
    'investment' => 'Yatırım Amaçlı',
    '' => 'Boş', // Boş stringler için varsayılan
    null => 'Boş' // NULL değerler için varsayılan
];

$total_updated = 0;
$success = true;

foreach ($conversions as $old_value => $new_value) {
    // Mevcut kayıt sayısını kontrol et
    if ($old_value === null) {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE usage_status IS NULL";
        $count_stmt = $conn->prepare($count_query);
    } elseif ($old_value === '') {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE usage_status = ''";
        $count_stmt = $conn->prepare($count_query);
    } else {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE usage_status = ?";
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
            $update_query = "UPDATE properties SET usage_status = ? WHERE usage_status IS NULL";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } elseif ($old_value === '') {
            $update_query = "UPDATE properties SET usage_status = ? WHERE usage_status = ''";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $new_value);
        } else {
            $update_query = "UPDATE properties SET usage_status = ? WHERE usage_status = ?";
            $update_stmt = $conn->prepare($update_query);
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
        echo "\n";
    }
}

// Standart olmayan kalan değerleri kontrol edelim
echo "=== STANDART OLMAYAN DEĞERLER KONTROL EDİLİYOR ===\n";
$remaining_query = "SELECT DISTINCT usage_status FROM properties WHERE usage_status NOT IN ('" . 
                  implode("', '", $standard_values) . "') AND usage_status IS NOT NULL AND usage_status != ''";
$remaining_result = $conn->query($remaining_query);

if ($remaining_result && $remaining_result->num_rows > 0) {
    echo "⚠️ Hala standart olmayan değerler var:\n";
    while ($row = $remaining_result->fetch_assoc()) {
        $non_standard = $row['usage_status'];
        echo "- \"$non_standard\"\n";
        
        // Bu değerleri de dönüştürelim - varsayılan olarak "Boş" yapalım
        $convert_query = "UPDATE properties SET usage_status = 'Boş' WHERE usage_status = ?";
        $convert_stmt = $conn->prepare($convert_query);
        $convert_stmt->bind_param("s", $non_standard);
        
        if ($convert_stmt->execute()) {
            $affected = $convert_stmt->affected_rows;
            echo "  → \"Boş\" olarak dönüştürüldü ($affected kayıt)\n";
            $total_updated += $affected;
        }
        $convert_stmt->close();
    }
} else {
    echo "✅ Standart olmayan değer kalmadı!\n";
}

echo "\n=== SONUÇ ===\n";
echo "✅ Toplam dönüştürülen kayıt: $total_updated\n";

// Final durum
echo "\n=== FİNAL DURUM ===\n";
$final_query = "SELECT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status ORDER BY usage_status";
$final_result = $conn->query($final_query);

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $usage_val = $row['usage_status'] ?? 'NULL';
        echo "- \"$usage_val\" ({$row['count']} kayıt)\n";
    }
}

// Kontrolle teyit edelim
$total_properties = $conn->query("SELECT COUNT(*) as total FROM properties")->fetch_assoc()['total'];
$standard_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE usage_status IN ('" . 
                             implode("', '", $standard_values) . "')")->fetch_assoc()['count'];

echo "\nTOTAL KONTROL:\n";
echo "==============\n";
echo "Toplam property: $total_properties\n";
echo "Standart değere sahip: $standard_count\n";
echo "Standart dışı: " . ($total_properties - $standard_count) . "\n";

if ($total_properties == $standard_count) {
    echo "\n🎉 BAŞARILI! Tüm kullanım durumu verileri sadece 4 standart değere dönüştürüldü!\n";
} else {
    echo "\n⚠️ Hala bazı kayıtlarda standart olmayan değerler var.\n";
}

$conn->close();
?>
