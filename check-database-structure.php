<?php
include 'db.php';

// Properties tablosunun yapısını göster
echo "=== PROPERTIES TABLOSU YAPISI ===\n";
echo "Content-Type: text/plain; charset=utf-8\n\n";

$result = $conn->query("DESCRIBE properties");
$columns = [];

while ($row = $result->fetch_assoc()) {
    $columns[] = $row;
    printf("%-25s | %-15s | %-5s | %-10s | %s\n", 
           $row['Field'], 
           $row['Type'], 
           $row['Null'], 
           $row['Key'], 
           $row['Default'] ?? 'NULL');
}

echo "\n=== KONUM ALANLARI DETAYI ===\n";
$location_fields = ['location', 'city', 'district', 'neighborhood', 'il', 'ilce', 'mahalle', 'address'];

foreach ($location_fields as $field) {
    $found = false;
    foreach ($columns as $col) {
        if ($col['Field'] === $field) {
            printf("%-15s: MEVCUT (%s)\n", $field, $col['Type']);
            $found = true;
            break;
        }
    }
    if (!$found) {
        printf("%-15s: MEVCUT DEĞİL\n", $field);
    }
}

echo "\n=== MEVCUT VERİLER KONTROLÜ ===\n";
$sample_query = "SELECT id, location, city, district, neighborhood, il, ilce, mahalle, address FROM properties WHERE id IN (55, 56) ORDER BY id";
$result = $conn->query($sample_query);

while ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    foreach ($location_fields as $field) {
        if (isset($row[$field])) {
            $value = $row[$field];
            if (is_null($value)) {
                $display = 'NULL';
            } elseif ($value === '') {
                $display = 'BOŞ';
            } elseif ($value === '0') {
                $display = 'SIFIR';
            } else {
                $display = $value;
            }
            printf("  %-15s: %s\n", $field, $display);
        }
    }
    echo "\n";
}

echo "=== ÖNERİLEN ÇÖZÜM ===\n";
echo "1. Eski alanları (city, district, neighborhood) yeni alanlara (il, ilce, mahalle) kopyala\n";
echo "2. Form gönderiminde yeni alanları kullan\n";
echo "3. Property-details.php'de her iki alan setini de kontrol et\n";

echo "\n=== SQL GÜNCELLEMESİ ===\n";
echo "-- Eski verileri yeni alanlara kopyala:\n";
echo "UPDATE properties SET il = city WHERE il IS NULL OR il = '' OR il = '0';\n";
echo "UPDATE properties SET ilce = district WHERE ilce IS NULL OR ilce = '' OR ilce = '0';\n";
echo "UPDATE properties SET mahalle = neighborhood WHERE mahalle IS NULL OR mahalle = '' OR mahalle = '0';\n";

echo "\n=== 56 NUMARALI İLAN İÇİN ÖZEL KONTROL ===\n";
$check56 = $conn->query("SELECT city, district, neighborhood, il, ilce, mahalle FROM properties WHERE id = 56");
$data56 = $check56->fetch_assoc();

echo "ID 56 için mevcut veriler:\n";
foreach ($data56 as $field => $value) {
    printf("  %-15s: %s\n", $field, $value ?? 'NULL');
}
?>
