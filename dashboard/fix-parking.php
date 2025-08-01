<?php
include '../db.php';

echo "<h3>Otopark Değeri Migration</h3>";

// Önce mevcut "var" değerlerini listele
$query = "SELECT id, title, parking FROM properties WHERE parking = 'var'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h4>Güncellenmesi gereken ilanlar:</h4>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Mevcut Değer</th><th>Aksiyon</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'], 0, 50) . "...</td>";
        echo "<td>[" . htmlspecialchars($row['parking']) . "]</td>";
        echo "<td>";
        echo "<a href='?update=1&id=" . $row['id'] . "' onclick=\"return confirm('Bu ilanın otopark değerini \\\"Açık Otopark\\\" olarak güncelleyelim mi?')\">Güncelle</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br><a href='?update_all=1' onclick=\"return confirm('Tüm \\\"var\\\" değerlerini \\\"Açık Otopark\\\" olarak güncellemek istediğinize emin misiniz?')\"><strong>Hepsini Toplu Güncelle</strong></a>";
} else {
    echo "<p>Güncellenmesi gereken 'var' değeri bulunamadı.</p>";
}

// Güncelleme işlemleri
if (isset($_GET['update']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $update_query = "UPDATE properties SET parking = 'Açık Otopark' WHERE id = ? AND parking = 'var'";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ ID $id numaralı ilan başarıyla güncellendi!</div>";
        } else {
            echo "<div style='color: orange; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid orange; background: #fff8f0;'>⚠ ID $id numaralı ilan bulunamadı veya zaten güncel.</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Güncelleme hatası: " . $conn->error . "</div>";
    }
    
    // Sayfayı yenile
    echo "<meta http-equiv='refresh' content='2'>";
}

if (isset($_GET['update_all'])) {
    $update_all_query = "UPDATE properties SET parking = 'Açık Otopark' WHERE parking = 'var'";
    $result = $conn->query($update_all_query);
    
    if ($result) {
        $affected = $conn->affected_rows;
        echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ $affected adet ilan başarıyla güncellendi!</div>";
        // Sayfayı yenile
        echo "<meta http-equiv='refresh' content='2'>";
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Toplu güncelleme hatası: " . $conn->error . "</div>";
    }
}

echo "<br><br><a href='debug-parking.php'>← Debug Sayfasına Dön</a>";
echo "<br><a href='add-property.php'>← Add Property Sayfasına Dön</a>";
?>
