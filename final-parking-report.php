<?php
require_once 'db.php';

echo "=== VERİTABANI PARKING VERİLERİ - FİNAL KONTROL ===\n\n";

// Tüm parking değerlerini listele
echo "TÜM PARKING DEĞERLERİ:\n";
echo "======================\n";
$all_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$all_result = $conn->query($all_query);

$total_properties = 0;
$values_found = [];

if ($all_result && $all_result->num_rows > 0) {
    while ($row = $all_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        if ($parking_val === '') $parking_val = 'BOŞ STRING';
        
        $count = $row['count'];
        $total_properties += $count;
        $values_found[] = $row['parking'];
        
        // Standart değerleri yeşil, diğerlerini kırmızı göster
        $standard_values = ['Otopark Yok', 'Açık Otopark', 'Kapalı Otopark'];
        $status = in_array($row['parking'], $standard_values) ? '✅' : '❌';
        
        echo "$status \"$parking_val\" ($count kayıt)\n";
    }
} else {
    echo "Hiç parking verisi bulunamadı!\n";
}

echo "\nÖZET RAPOR:\n";
echo "===========\n";
echo "Toplam property sayısı: $total_properties\n";
echo "Farklı parking değeri sayısı: " . count($values_found) . "\n";

// Standart değerlerin durumu
$standard_values = ['Otopark Yok', 'Açık Otopark', 'Kapalı Otopark'];
$standard_counts = [];
$total_standard = 0;

foreach ($standard_values as $standard) {
    $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("s", $standard);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['count'];
    $count_stmt->close();
    
    $standard_counts[$standard] = $count;
    $total_standard += $count;
    
    echo "- \"$standard\": $count kayıt\n";
}

echo "\nSTANDART UYUMLULUK:\n";
echo "===================\n";
echo "Standart değerlere sahip: $total_standard / $total_properties\n";

if ($total_standard == $total_properties && count($values_found) == 3) {
    echo "🎉 MÜKEMMEL! Veritabanında sadece 3 standart parking değeri var!\n";
    echo "✅ Temizleme işlemi tamamen başarılı!\n";
} elseif ($total_standard == $total_properties) {
    echo "✅ Tüm kayıtlar standart değerlere sahip ama fazla kategorik var.\n";
} else {
    echo "⚠️ Hala standart olmayan değerler var: " . ($total_properties - $total_standard) . " kayıt\n";
    
    // Standart olmayan değerleri göster
    echo "\nSTANDART OLMAYAN DEĞERLER:\n";
    echo "==========================\n";
    $non_standard_query = "SELECT parking, COUNT(*) as count FROM properties 
                          WHERE parking NOT IN ('" . implode("', '", $standard_values) . "') 
                          GROUP BY parking ORDER BY count DESC";
    $non_standard_result = $conn->query($non_standard_query);
    
    if ($non_standard_result && $non_standard_result->num_rows > 0) {
        while ($row = $non_standard_result->fetch_assoc()) {
            $parking_val = $row['parking'] ?? 'NULL';
            if ($parking_val === '') $parking_val = 'BOŞ STRING';
            echo "❌ \"$parking_val\" ({$row['count']} kayıt)\n";
        }
    }
}

// NULL ve boş değer kontrolü
echo "\nNULL VE BOŞ DEĞER KONTROLÜ:\n";
echo "============================\n";
$null_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE parking IS NULL")->fetch_assoc()['count'];
$empty_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE parking = ''")->fetch_assoc()['count'];

echo "NULL değerler: $null_count kayıt\n";
echo "Boş string değerler: $empty_count kayıt\n";

if ($null_count == 0 && $empty_count == 0) {
    echo "✅ NULL ve boş değer yok!\n";
} else {
    echo "⚠️ NULL veya boş değerler tespit edildi.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "VERİTABANI TEMİZLEME RAPORU TAMAMLANDI\n";
echo str_repeat("=", 50) . "\n";

$conn->close();
?>
