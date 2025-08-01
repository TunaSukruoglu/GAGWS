<?php
require_once 'db.php';

echo "Properties tablosundaki parking verileri:\n";
echo "===========================================\n";

// Parking kolonundaki benzersiz değerleri al
$query = "SELECT DISTINCT parking, COUNT(*) as count FROM properties WHERE parking IS NOT NULL AND parking != '' GROUP BY parking ORDER BY count DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "Parking değerleri ve sayıları:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- \"" . $row['parking'] . "\" (" . $row['count'] . " kayıt)\n";
    }
} else {
    echo "Parking verileri bulunamadı.\n";
}

echo "\nToplam parking verisi olan kayıt sayısı:\n";
$total_query = "SELECT COUNT(*) as total FROM properties WHERE parking IS NOT NULL AND parking != ''";
$total_result = $conn->query($total_query);
if ($total_result) {
    $total = $total_result->fetch_assoc();
    echo "Toplam: " . $total['total'] . " kayıt\n";
}

echo "\nParking verisi olmayan kayıtlar:\n";
$empty_query = "SELECT COUNT(*) as empty FROM properties WHERE parking IS NULL OR parking = ''";
$empty_result = $conn->query($empty_query);
if ($empty_result) {
    $empty = $empty_result->fetch_assoc();
    echo "Boş/NULL: " . $empty['empty'] . " kayıt\n";
}

echo "\nÖrnek kayıtlar (ID, title, parking):\n";
echo "=====================================\n";
$sample_query = "SELECT id, title, parking FROM properties WHERE parking IS NOT NULL AND parking != '' LIMIT 10";
$sample_result = $conn->query($sample_query);
if ($sample_result && $sample_result->num_rows > 0) {
    while ($row = $sample_result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - " . substr($row['title'], 0, 30) . "... - Parking: \"" . $row['parking'] . "\"\n";
    }
}

echo "\nTüm parking değerlerinin detaylı listesi:\n";
echo "========================================\n";
$detailed_query = "SELECT parking, COUNT(*) as count FROM properties GROUP BY parking ORDER BY count DESC";
$detailed_result = $conn->query($detailed_query);
if ($detailed_result && $detailed_result->num_rows > 0) {
    while ($row = $detailed_result->fetch_assoc()) {
        $parking_val = $row['parking'] ?? 'NULL';
        if ($parking_val === '') $parking_val = 'EMPTY STRING';
        echo "- \"" . $parking_val . "\" (" . $row['count'] . " kayıt)\n";
    }
}

// Properties tablosundaki parking kolununun yapısını kontrol et
echo "\nParking kolonu yapısı:\n";
echo "=====================\n";
$structure_query = "SHOW COLUMNS FROM properties LIKE 'parking'";
$structure_result = $conn->query($structure_query);
if ($structure_result && $structure_result->num_rows > 0) {
    $structure = $structure_result->fetch_assoc();
    echo "Field: " . $structure['Field'] . "\n";
    echo "Type: " . $structure['Type'] . "\n";
    echo "Null: " . $structure['Null'] . "\n";
    echo "Default: " . ($structure['Default'] ?? 'NULL') . "\n";
}

$conn->close();
?>
