<?php
include '../db.php';

echo "<h3>Otopark ve Kullanım Durumu Migration</h3>";

// Önce mevcut parking ve usage_status değerlerini listele
$query = "SELECT id, title, parking, usage_status FROM properties ORDER BY id DESC LIMIT 20";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h4>Mevcut Değerler:</h4>";
    echo "<table border='1' cellpadding='4'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Mevcut Parking</th><th>Mevcut Usage Status</th><th>Aksiyon</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $parking = $row['parking'];
        $usage_status = $row['usage_status'];
        
        // Hangi alanların güncellenmesi gerektiğini kontrol et
        $parking_needs_update = false;
        $usage_needs_update = false;
        $suggested_parking = $parking;
        $suggested_usage = $usage_status;
        
        // Parking kontrol
        if ($parking == 'var' || $parking == 'Var') {
            $suggested_parking = 'Açık Otopark';
            $parking_needs_update = true;
        }
        
        // Usage status kontrol
        if ($usage_status == 'boş' || $usage_status == 'bos') {
            $suggested_usage = 'Boş';
            $usage_needs_update = true;
        } else if ($usage_status == 'dolu') {
            $suggested_usage = 'Kiracılı';
            $usage_needs_update = true;
        }
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
        echo "<td>[" . htmlspecialchars($parking ?? 'NULL') . "]" . ($parking_needs_update ? " → <strong>$suggested_parking</strong>" : '') . "</td>";
        echo "<td>[" . htmlspecialchars($usage_status ?? 'NULL') . "]" . ($usage_needs_update ? " → <strong>$suggested_usage</strong>" : '') . "</td>";
        echo "<td>";
        if ($parking_needs_update || $usage_needs_update) {
            echo "<a href='?update=1&id=" . $row['id'] . "&parking=" . urlencode($suggested_parking) . "&usage=" . urlencode($suggested_usage) . "' onclick=\"return confirm('ID " . $row['id'] . " güncellenmeli?')\">Güncelle</a>";
        } else {
            echo "<span style='color: green;'>✓ Güncel</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Veri bulunamadı.</p>";
}

// Güncelleme işlemi
if (isset($_GET['update']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $new_parking = $_GET['parking'];
    $new_usage = $_GET['usage'];
    
    $update_query = "UPDATE properties SET parking = ?, usage_status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $new_parking, $new_usage, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ ID $id güncellendi! Parking: '$new_parking', Usage: '$new_usage'</div>";
        } else {
            echo "<div style='color: orange; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid orange; background: #fff8f0;'>⚠ ID $id bulunamadı veya zaten güncel.</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Güncelleme hatası: " . $conn->error . "</div>";
    }
    
    echo "<meta http-equiv='refresh' content='2'>";
}

// Toplu güncellemeler
echo "<br><h4>Toplu Güncellemeler:</h4>";
echo "<a href='?fix_parking=1' onclick=\"return confirm('Tüm parking değerlerini güncellemek istediğinize emin misiniz?')\">Parking Değerlerini Düzelt</a><br><br>";
echo "<a href='?fix_usage=1' onclick=\"return confirm('Tüm kullanım durumu değerlerini güncellemek istediğinize emin misiniz?')\">Kullanım Durumu Değerlerini Düzelt</a>";

if (isset($_GET['fix_parking'])) {
    $queries = [
        "UPDATE properties SET parking = 'Açık Otopark' WHERE parking IN ('var', 'Var', 'VAR')",
        "UPDATE properties SET parking = 'Otopark Yok' WHERE parking IN ('yok', 'Yok', 'YOK', 'hayir', 'hayır')"
    ];
    
    $total_affected = 0;
    foreach ($queries as $query) {
        $result = $conn->query($query);
        if ($result) {
            $total_affected += $conn->affected_rows;
        }
    }
    
    echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ $total_affected adet parking değeri güncellendi!</div>";
    echo "<meta http-equiv='refresh' content='2'>";
}

if (isset($_GET['fix_usage'])) {
    $queries = [
        "UPDATE properties SET usage_status = 'Boş' WHERE usage_status IN ('boş', 'bos', 'BOS', 'BOŞ')",
        "UPDATE properties SET usage_status = 'Kiracılı' WHERE usage_status IN ('dolu', 'DOLU', 'kiracili', 'Kiracili')"
    ];
    
    $total_affected = 0;
    foreach ($queries as $query) {
        $result = $conn->query($query);
        if ($result) {
            $total_affected += $conn->affected_rows;
        }
    }
    
    echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ $total_affected adet kullanım durumu değeri güncellendi!</div>";
    echo "<meta http-equiv='refresh' content='2'>";
}

echo "<br><br><a href='add-property.php'>← Add Property'ye Dön</a>";
?>
