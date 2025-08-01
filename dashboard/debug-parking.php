<?php
include '../db.php';

echo "<h3>Otopark Debug - Mevcut Değerler</h3>";

$query = "SELECT id, title, parking FROM properties WHERE parking IS NOT NULL AND parking != '' ORDER BY id DESC LIMIT 10";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Otopark Değeri</th><th>Karakter Sayısı</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
        echo "<td>[" . htmlspecialchars($row['parking']) . "]</td>";
        echo "<td>" . strlen($row['parking']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Otopark verisi bulunan ilan yok.</p>";
}

// Boş veya null otopark değeri olanları da göster
echo "<h3>Otopark Değeri Boş Olanlar (Son 5)</h3>";
$query2 = "SELECT id, title, parking FROM properties WHERE parking IS NULL OR parking = '' ORDER BY id DESC LIMIT 5";
$result2 = $conn->query($query2);

if ($result2 && $result2->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Otopark Değeri</th></tr>";
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
        echo "<td>[" . ($row['parking'] === null ? 'NULL' : 'EMPTY') . "]</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
