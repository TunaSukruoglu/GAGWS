<?php
// Database bağlantısı
include '../db.php';

echo "<h2>Properties Tablosu - usage_status Kolonu Analizi</h2>";

// Tablo yapısını kontrol et
echo "<h3>1. Kolon Yapısı (DESCRIBE):</h3>";
$result = $conn->query("DESCRIBE properties");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] == 'usage_status') {
            echo "<tr style='background-color: yellow;'>";
        } else {
            echo "<tr>";
        }
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Mevcut usage_status değerlerini kontrol et
echo "<h3>2. Mevcut usage_status Değerleri:</h3>";
$result = $conn->query("SELECT DISTINCT usage_status, COUNT(*) as count FROM properties GROUP BY usage_status ORDER BY count DESC");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>usage_status Değeri</th><th>Uzunluk</th><th>Adet</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $value = $row['usage_status'];
        $length = strlen($value);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "<td>" . $length . "</td>";
        echo "<td>" . $row['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// SHOW CREATE TABLE ile detaylı yapıyı al
echo "<h3>3. Detaylı Tablo Yapısı:</h3>";
$result = $conn->query("SHOW CREATE TABLE properties");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
}

echo "<h3>4. Çözüm Önerileri:</h3>";
echo "<p>Eğer usage_status kolonu ENUM ise, yeni değer eklemek için ALTER TABLE gerekir.</p>";
echo "<p>Eğer VARCHAR ise, boyutunu artırmak gerekebilir.</p>";

$conn->close();
?>
