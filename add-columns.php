<?php
include 'db.php';

echo "=== VERİTABANI SÜTUNLARI EKLEME ===\n";

// Önce mevcut sütunları kontrol et
echo "Mevcut sütunlar:\n";
$result = $conn->query('SHOW COLUMNS FROM properties LIKE "%features%"');
while($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . "\n";
}

echo "\n=== YENİ SÜTUNLAR EKLENİYOR ===\n";

// Yeni sütunları ekle
$columns = [
    'interior_features',
    'exterior_features', 
    'neighborhood_features',
    'transportation_features',
    'view_features',
    'housing_type_features'
];

foreach($columns as $column) {
    $sql = "ALTER TABLE properties ADD COLUMN IF NOT EXISTS $column TEXT";
    if($conn->query($sql) === TRUE) {
        echo "✅ $column sütunu eklendi\n";
    } else {
        echo "❌ $column sütunu eklenirken hata: " . $conn->error . "\n";
    }
}

echo "\n=== GÜNCEL SÜTUNLAR ===\n";
$result = $conn->query('SHOW COLUMNS FROM properties LIKE "%features%"');
while($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . "\n";
}
?>
