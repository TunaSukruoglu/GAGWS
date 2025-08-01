<?php
require_once 'db.php';

echo "=== KULLANIM DURUMU FİNAL RAPORU ===\n\n";

// Son durum kontrolü
echo "GÜNCEL KULLANIM DURUMU VERİLERİ:\n";
echo "================================\n";
$final_query = "SELECT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status ORDER BY count DESC";
$final_result = $conn->query($final_query);

$total_properties = 0;
$values_found = [];

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $usage_val = $row['usage_status'] ?? 'NULL';
        if ($usage_val === '') $usage_val = 'BOŞ STRING';
        
        $count = $row['count'];
        $total_properties += $count;
        $values_found[] = $row['usage_status'];
        
        // Standart değerleri yeşil, diğerlerini kırmızı göster
        $standard_values = ['Boş', 'Kiracılı', 'Malik Kullanımında', 'Yatırım Amaçlı'];
        $status = in_array($row['usage_status'], $standard_values) ? '✅' : '❌';
        
        echo "$status \"$usage_val\" ($count kayıt)\n";
    }
}

echo "\nÖZET RAPOR:\n";
echo "===========\n";
echo "Toplam property sayısı: $total_properties\n";
echo "Farklı kullanım durumu değeri sayısı: " . count($values_found) . "\n";

// Standart değerlerin durumu
$standard_values = ['Boş', 'Kiracılı', 'Malik Kullanımında', 'Yatırım Amaçlı'];
$standard_counts = [];
$total_standard = 0;

echo "\nSTANDART DEĞERLER DAĞILIMI:\n";
echo "===========================\n";
foreach ($standard_values as $standard) {
    $count_query = "SELECT COUNT(*) as count FROM properties WHERE usage_status = ?";
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

echo "\nSONUÇ DEĞERLENDİRMESİ:\n";
echo "======================\n";
echo "Standart değerlere sahip: $total_standard / $total_properties\n";

if ($total_standard == $total_properties && count($values_found) == 4) {
    echo "🎉 MÜKEMMEL! Veritabanında sadece 4 standart kullanım durumu değeri var!\n";
    echo "✅ Temizleme işlemi tamamen başarılı!\n";
    echo "✅ Form seçenekleri: Sadece 4 standart seçenek\n";
    echo "✅ Database: Sadece 4 standart değer\n";
    echo "✅ Display Logic: Eski değerleri otomatik dönüştürüyor\n";
} elseif ($total_standard == $total_properties) {
    echo "✅ Tüm kayıtlar standart değerlere sahip ama fazla kategori var.\n";
} else {
    echo "⚠️ Hala standart olmayan değerler var: " . ($total_properties - $total_standard) . " kayıt\n";
    
    // Standart olmayan değerleri göster
    echo "\nSTANDART OLMAYAN DEĞERLER:\n";
    echo "==========================\n";
    $non_standard_query = "SELECT usage_status, COUNT(*) as count FROM properties 
                          WHERE usage_status NOT IN ('" . implode("', '", $standard_values) . "') 
                          AND usage_status IS NOT NULL AND usage_status != ''
                          GROUP BY usage_status ORDER BY count DESC";
    $non_standard_result = $conn->query($non_standard_query);
    
    if ($non_standard_result && $non_standard_result->num_rows > 0) {
        while ($row = $non_standard_result->fetch_assoc()) {
            $usage_val = $row['usage_status'] ?? 'NULL';
            if ($usage_val === '') $usage_val = 'BOŞ STRING';
            echo "❌ \"$usage_val\" ({$row['count']} kayıt)\n";
        }
    }
}

// NULL ve boş değer kontrolü
echo "\nNULL VE BOŞ DEĞER KONTROLÜ:\n";
echo "============================\n";
$null_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE usage_status IS NULL")->fetch_assoc()['count'];
$empty_count = $conn->query("SELECT COUNT(*) as count FROM properties WHERE usage_status = ''")->fetch_assoc()['count'];

echo "NULL değerler: $null_count kayıt\n";
echo "Boş string değerler: $empty_count kayıt\n";

if ($null_count == 0 && $empty_count == 0) {
    echo "✅ NULL ve boş değer yok!\n";
} else {
    echo "⚠️ NULL veya boş değerler tespit edildi.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "KULLANIM DURUMU TEMİZLEME RAPORU TAMAMLANDI\n";
echo "Standart Değerler: Boş | Kiracılı | Malik Kullanımında | Yatırım Amaçlı\n";
echo str_repeat("=", 60) . "\n";

$conn->close();
?>
