<?php
include '../db.php';

echo "<h2>Properties Tablosu Yapısı</h2>";

// Tablo yapısını göster
$result = $conn->query("DESCRIBE properties");

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 10px;'>Sütun Adı</th>";
    echo "<th style='padding: 10px;'>Tip</th>";
    echo "<th style='padding: 10px;'>Null</th>";
    echo "<th style='padding: 10px;'>Key</th>";
    echo "<th style='padding: 10px;'>Default</th>";
    echo "<th style='padding: 10px;'>Extra</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold; color: #0d6efd;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Key'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "<td style='padding: 8px;'>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>Properties tablosu bulunamadı!</p>";
}

echo "<br><h3>Örnek Veri (İlk 3 kayıt):</h3>";

// Örnek veri göster
$sample = $conn->query("SELECT * FROM properties LIMIT 3");

if ($sample && $sample->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
    
    // Header
    $first_row = $sample->fetch_assoc();
    echo "<tr style='background: #f0f0f0;'>";
    foreach (array_keys($first_row) as $column) {
        echo "<th style='padding: 5px;'>" . $column . "</th>";
    }
    echo "</tr>";
    
    // İlk satır
    echo "<tr>";
    foreach ($first_row as $value) {
        echo "<td style='padding: 5px;'>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
    }
    echo "</tr>";
    
    // Diğer satırlar
    while ($row = $sample->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td style='padding: 5px;'>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Properties tablosunda veri yok.</p>";
}

echo "<br><a href='dashboard-user.php' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Dashboard'a Dön</a>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f8f9fa;
}

table {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

th {
    background: #0d6efd !important;
    color: white !important;
}

tr:nth-child(even) {
    background: #f8f9fa;
}
</style>