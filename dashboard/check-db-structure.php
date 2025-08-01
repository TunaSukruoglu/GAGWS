<?php
include '../db.php';

echo "<h2>Properties Tablosu Yapısı</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Sütun Adı</th><th>Veri Tipi</th><th>Null</th><th>Anahtar</th><th>Varsayılan</th><th>Ekstra</th></tr>";

$result = $conn->query("DESCRIBE properties");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $row['Key'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>Hata: " . $conn->error . "</td></tr>";
}
echo "</table>";

echo "<br><br><h2>Toplam Sütun Sayısı</h2>";
$count_result = $conn->query("SELECT COUNT(*) as total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gokhanay_db' AND TABLE_NAME = 'properties'");
if ($count_result) {
    $count = $count_result->fetch_assoc();
    echo "<p><strong>Toplam " . $count['total'] . " sütun bulunuyor.</strong></p>";
}

echo "<br><h2>Örnek Kayıt (Son Eklenen)</h2>";
$sample_result = $conn->query("SELECT * FROM properties ORDER BY id DESC LIMIT 1");
if ($sample_result && $sample_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    $sample = $sample_result->fetch_assoc();
    foreach ($sample as $key => $value) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ccc; font-weight: bold;'>" . $key . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . (strlen($value) > 100 ? substr($value, 0, 100) . "..." : $value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Henüz kayıt yok.</p>";
}
?>
