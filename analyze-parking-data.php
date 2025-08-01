<?php
require_once 'db.php';

echo "=== MEVCUT PARKING VERİLERİ ANALİZİ ===\n\n";

// Mevcut parking değerlerini analiz et
$query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$result = $conn->query($query);

echo "Şu anda veritabanında bulunan parking değerleri:\n";
echo "================================================\n";

$current_values = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $parking_val = $row['parking'];
        if ($parking_val === null) $parking_val = 'NULL';
        if ($parking_val === '') $parking_val = 'BOŞ STRING';
        
        echo "- \"$parking_val\" ({$row['count']} kayıt)\n";
        $current_values[] = $parking_val;
    }
}

echo "\n=== YENİ STANDART PARKING SEÇENEKLERİ ===\n";
$standard_options = [
    'Otopark Yok',
    'Otopark Var (Eski Kayıt)',
    'Otopark Var (Eski Kayıt 2)',
    '1 Araç',
    '2 Araç', 
    '3 Araç',
    '4 Araç',
    '5+ Araç',
    'Açık Otopark',
    'Kapalı Otopark',
    'Yarı Açık Otopark',
    'Yer Altı Otoparkı',
    'Vale Park',
    'Bahçe İçi Park',
    'Sokak Parkı',
    'Ücretli Park',
    'Ücretsiz Park',
    'Açık + Kapalı Park',
    'Misafir Parkı Var',
    'Engelli Parkı Var'
];

echo "\nStandart seçenekler:\n";
foreach ($standard_options as $option) {
    echo "- \"$option\"\n";
}

echo "\n=== VERİ TEMİZLİK ÖNERİLERİ ===\n";

// Problematik değerleri tespit et
$problematic = [];
foreach ($current_values as $value) {
    if ($value != 'NULL' && $value != 'BOŞ STRING' && !in_array($value, $standard_options)) {
        // Form seçeneklerindeki karşılıklarını kontrol et
        $form_values = ['Yok', 'var', 'Var', 'Açık Otopark', 'Kapalı Otopark', 'Yarı Açık Otopark', 'Yer Altı Otoparkı'];
        if (!in_array($value, $form_values)) {
            $problematic[] = $value;
        }
    }
}

if (!empty($problematic)) {
    echo "\nProblematik değerler (düzeltilmesi gereken):\n";
    foreach ($problematic as $prob) {
        echo "- \"$prob\"\n";
    }
} else {
    echo "\n✅ Tüm değerler standart seçeneklerle uyumlu!\n";
}

echo "\n=== ÖNGÖRÜLEN DÜZELTİLMELER ===\n";

// Düzeltme önerileri
$corrections = [
    'var' => 'Otopark Var (Eski Kayıt)',
    'Var' => 'Otopark Var (Eski Kayıt 2)', 
    'Yok' => 'Otopark Yok',
    '3+ Araç' => '5+ Araç'
];

echo "\nÖnerilen düzeltmeler:\n";
foreach ($corrections as $old => $new) {
    $count_query = "SELECT COUNT(*) as count FROM properties WHERE parking = '$old'";
    $count_result = $conn->query($count_query);
    if ($count_result) {
        $count = $count_result->fetch_assoc()['count'];
        if ($count > 0) {
            echo "- \"$old\" → \"$new\" ($count kayıt)\n";
        }
    }
}

$conn->close();
?>
