<?php
// Minimal debug - sadece ENUM kontrolü
include 'db.php';

// ENUM değerlerini al
$result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'usage_status'");
$row = $result->fetch_assoc();

echo "ENUM Type: " . $row['Type'] . "\n";

// ENUM değerlerini parse et
preg_match_all("/'([^']+)'/", $row['Type'], $matches);
$valid_values = $matches[1];

echo "Geçerli değerler:\n";
foreach ($valid_values as $i => $val) {
    echo ($i+1) . ". '$val'\n";
}

echo "\nTest değerleri:\n";
$test_values = ['Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli'];
foreach ($test_values as $test) {
    $valid = in_array($test, $valid_values) ? 'GEÇERLİ' : 'GEÇERSİZ';
    echo "'$test' -> $valid\n";
}
?>
