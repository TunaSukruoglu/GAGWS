<?php
require_once 'db.php';

echo "=== KULLANIM DURUMU VERİLERİ ANALİZİ ===\n\n";

// Mevcut usage_status değerlerini kontrol et
$query = "SELECT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status ORDER BY count DESC";
$result = $conn->query($query);

echo "Mevcut kullanım durumu değerleri:\n";
echo "==================================\n";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usage_val = $row['usage_status'] ?? 'NULL';
        if ($usage_val === '') $usage_val = 'BOŞ STRING';
        echo "- \"$usage_val\" ({$row['count']} kayıt)\n";
    }
} else {
    echo "Kullanım durumu verileri bulunamadı.\n";
}

// Properties tablosundaki usage_status kolununun yapısını kontrol et
echo "\nKullanım durumu kolonu yapısı:\n";
echo "==============================\n";
$structure_query = "SHOW COLUMNS FROM properties LIKE 'usage_status'";
$structure_result = $conn->query($structure_query);
if ($structure_result && $structure_result->num_rows > 0) {
    $structure = $structure_result->fetch_assoc();
    echo "Field: " . $structure['Field'] . "\n";
    echo "Type: " . $structure['Type'] . "\n";
    echo "Null: " . $structure['Null'] . "\n";
    echo "Default: " . ($structure['Default'] ?? 'NULL') . "\n";
} else {
    echo "usage_status kolonu bulunamadı.\n";
}

$conn->close();
?>
