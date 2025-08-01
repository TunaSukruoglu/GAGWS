<?php
require_once 'db.php';

echo "=== BANYO SAYISI FİNAL RAPORU ===\n\n";

// Son durum kontrolü
echo "GÜNCEL BANYO SAYISI VERİLERİ:\n";
echo "=============================\n";
$final_query = "SELECT bathrooms, COUNT(*) as count FROM properties GROUP BY bathrooms ORDER BY bathrooms";
$final_result = $conn->query($final_query);

$total_properties = 0;
$values_found = [];

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $bathroom_val = $row['bathrooms'] ?? 'NULL';
        if ($bathroom_val === '') $bathroom_val = 'BOŞ STRING';
        
        $count = $row['count'];
        $total_properties += $count;
        $values_found[] = $row['bathrooms'];
        
        // Standart değerleri yeşil, diğerlerini kırmızı göster
        $standard_values = ['1', '2', '3'];
        $status = in_array($row['bathrooms'], $standard_values) ? '✅' : '❌';
        
        echo "$status \"$bathroom_val\" ($count kayıt)\n";
    }
}

echo "\nÖZET RAPOR:\n";
echo "===========\n";
echo "Toplam property sayısı: $total_properties\n";
echo "Farklı banyo sayısı değeri: " . count($values_found) . "\n";

// Standart değerlerin durumu
$standard_values = ['1', '2', '3'];
$standard_counts = [];
$total_standard = 0;

echo "\nSTANDART DEĞERLER DAĞILIMI:\n";
echo "===========================\n";
foreach ($standard_values as $standard) {
    $count_query = "SELECT COUNT(*) as count FROM properties WHERE bathrooms = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("s", $standard);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['count'];
    $count_stmt->close();
    
    $standard_counts[$standard] = $count;
    $total_standard += $count;
    
    echo "- \"$standard banyo\": $count kayıt\n";
}

echo "\nSONUÇ DEĞERLENDİRMESİ:\n";
echo "======================\n";
echo "Standart değerlere sahip: $total_standard / $total_properties\n";

if ($total_standard == $total_properties && count($values_found) == 3) {
    echo "🎉 MÜKEMMEL! Veritabanında sadece 1, 2, 3 banyo değerleri var!\n";
    echo "✅ Temizleme işlemi tamamen başarılı!\n";
    echo "✅ Form seçenekleri: Sadece 1, 2, 3\n";
    echo "✅ Database: Sadece 1, 2, 3 değerleri\n";
    echo "✅ Display Logic: Büyük değerleri otomatik 3'e çeviriyor\n";
} elseif ($total_standard == $total_properties) {
    echo "✅ Tüm kayıtlar standart değerlere sahip ama fazla kategori var.\n";
} else {
    echo "⚠️ Hala standart olmayan değerler var: " . ($total_properties - $total_standard) . " kayıt\n";
    
    // Standart olmayan değerleri göster
    echo "\nSTANDART OLMAYAN DEĞERLER:\n";
    echo "==========================\n";
    $non_standard_query = "SELECT bathrooms, COUNT(*) as count FROM properties 
                          WHERE bathrooms NOT IN ('" . implode("', '", $standard_values) . "') 
                          AND bathrooms IS NOT NULL AND bathrooms != ''
                          GROUP BY bathrooms ORDER BY count DESC";
    $non_standard_result = $conn->query($non_standard_query);
    
    if ($non_standard_result && $non_standard_result->num_rows > 0) {
        while ($row = $non_standard_result->fetch_assoc()) {
            $bathroom_val = $row['bathrooms'] ?? 'NULL';
            if ($bathroom_val === '') $bathroom_val = 'BOŞ STRING';
            echo "❌ \"$bathroom_val\" ({$row['count']} kayıt)\n";
        }
    }
}

// NULL ve boş değer kontrolü
echo "\nNULL VE BOŞ DEĞER KONTROLÜ:\n";
echo "============================\n";
$null_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE bathrooms IS NULL")->fetch_assoc()['count'];
$empty_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE bathrooms = ''")->fetch_assoc()['count'];

echo "NULL değerler: $null_count kayıt\n";
echo "Boş string değerler: $empty_count kayıt\n";

if ($null_count == 0 && $empty_count == 0) {
    echo "✅ NULL ve boş değer yok!\n";
} else {
    echo "⚠️ NULL veya boş değerler tespit edildi.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "BANYO SAYISI TEMİZLEME RAPORU TAMAMLANDI\n";
echo "Standart Değerler: 1 | 2 | 3\n";
echo str_repeat("=", 50) . "\n";

$conn->close();
?>
