<?php
require_once 'db.php';

echo "=== PARKING VERİLERİNİ STANDARTLAŞTIRMA ===\n\n";

// Düzeltme planı
$corrections = [
    'var' => 'Otopark Var (Eski Kayıt)',
    'Var' => 'Otopark Var (Eski Kayıt 2)',
    'Yok' => 'Otopark Yok',
    '3+ Araç' => '5+ Araç',
    '' => NULL,  // Boş stringleri NULL yap
];

$total_updated = 0;
$success = true;

foreach ($corrections as $old_value => $new_value) {
    // Mevcut kayıt sayısını kontrol et
    if ($old_value === '') {
        $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = ''";
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
        echo "\"$old_value\" değerini \"$new_value\" olarak güncelleniyor... ($count kayıt)\n";
        
        // Güncelleme yap
        if ($old_value === '') {
            $update_query = "UPDATE properties SET parking = ? WHERE parking = ''";
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
    } else {
        echo "\"$old_value\" değeri bulunamadı, atlanıyor...\n";
    }
    $count_stmt->close();
    echo "\n";
}

echo "=== SONUÇ ===\n";
if ($success) {
    echo "✅ Tüm güncellemeler başarıyla tamamlandı!\n";
    echo "📊 Toplam güncellenen kayıt: $total_updated\n";
} else {
    echo "⚠️ Bazı güncellemelerde hata oluştu.\n";
}

// Güncellenmiş durumu göster
echo "\n=== GÜNCELLENMİŞ PARKING VERİLERİ ===\n";
$final_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$final_result = $conn->query($final_query);

if ($final_result && $final_result->num_rows > 0) {
    while ($row = $final_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
    }
}

$conn->close();
?>
