<?php
include '../db.php';

echo "<h3>Oda Sayısı Migration - Eski Formatı Güncelle</h3>";

// Önce mevcut room_count değerlerini listele
$query = "SELECT id, title, room_count, bedrooms FROM properties WHERE room_count IS NOT NULL AND room_count != '' ORDER BY id DESC LIMIT 20";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<h4>Mevcut Oda Sayısı Değerleri:</h4>";
    echo "<table border='1' cellpadding='4'>";
    echo "<tr><th>ID</th><th>Başlık</th><th>Mevcut room_count</th><th>bedrooms</th><th>Önerilen Format</th><th>Aksiyon</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $current_room_count = $row['room_count'];
        $bedrooms = $row['bedrooms'] ?? 0;
        
        // Önerilen format hesapla
        $suggested_format = '';
        if (strpos($current_room_count, '+') !== false) {
            // Zaten yeni formatta
            $suggested_format = $current_room_count;
            $needs_update = false;
        } else {
            // Eski format, güncelle
            if (is_numeric($current_room_count)) {
                $suggested_format = $current_room_count . '+1';
            } else if ($current_room_count == 'var' || $current_room_count == 'Var') {
                $suggested_format = ($bedrooms > 0) ? $bedrooms . '+1' : '2+1';
            } else {
                $suggested_format = '2+1'; // Varsayılan
            }
            $needs_update = true;
        }
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . substr($row['title'], 0, 30) . "...</td>";
        echo "<td>[" . htmlspecialchars($current_room_count) . "]</td>";
        echo "<td>" . $bedrooms . "</td>";
        echo "<td><strong>" . $suggested_format . "</strong></td>";
        echo "<td>";
        if ($needs_update) {
            echo "<a href='?update=1&id=" . $row['id'] . "&new_value=" . urlencode($suggested_format) . "' onclick=\"return confirm('room_count değerini \\\"$suggested_format\\\" olarak güncelleyelim mi?')\">Güncelle</a>";
        } else {
            echo "<span style='color: green;'>✓ Güncel</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>room_count verisi bulunamadı.</p>";
}

// Güncelleme işlemi
if (isset($_GET['update']) && isset($_GET['id']) && isset($_GET['new_value'])) {
    $id = intval($_GET['id']);
    $new_value = $_GET['new_value'];
    
    $update_query = "UPDATE properties SET room_count = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_value, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ ID $id numaralı ilanın room_count değeri '$new_value' olarak güncellendi!</div>";
        } else {
            echo "<div style='color: orange; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid orange; background: #fff8f0;'>⚠ ID $id numaralı ilan bulunamadı veya zaten güncel.</div>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Güncelleme hatası: " . $conn->error . "</div>";
    }
    
    // Sayfayı yenile
    echo "<meta http-equiv='refresh' content='2'>";
}

echo "<br><br>";
echo "<h4>Toplu Güncelleme:</h4>";
echo "<a href='?update_all_numeric=1' onclick=\"return confirm('Tüm sayısal room_count değerlerini (örn: 2 → 2+1) formatına dönüştürmek istediğinize emin misiniz?')\"><strong>Tüm Sayısal Değerleri Güncelle</strong></a><br><br>";
echo "<a href='?update_all_var=1' onclick=\"return confirm('Tüm \\\"var\\\" room_count değerlerini \\\"2+1\\\" formatına dönüştürmek istediğinize emin misiniz?')\"><strong>Tüm 'var' Değerleri Güncelle</strong></a>";

// Toplu güncellemeler
if (isset($_GET['update_all_numeric'])) {
    $update_query = "UPDATE properties SET room_count = CONCAT(room_count, '+1') WHERE room_count REGEXP '^[0-9]+$'";
    $result = $conn->query($update_query);
    
    if ($result) {
        $affected = $conn->affected_rows;
        echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ $affected adet sayısal room_count değeri güncellendi!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Toplu güncelleme hatası: " . $conn->error . "</div>";
    }
    echo "<meta http-equiv='refresh' content='2'>";
}

if (isset($_GET['update_all_var'])) {
    $update_query = "UPDATE properties SET room_count = '2+1' WHERE room_count IN ('var', 'Var', 'VAR')";
    $result = $conn->query($update_query);
    
    if ($result) {
        $affected = $conn->affected_rows;
        echo "<div style='color: green; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid green; background: #f0fff0;'>✓ $affected adet 'var' room_count değeri güncellendi!</div>";
    } else {
        echo "<div style='color: red; font-weight: bold; margin: 10px 0; padding: 10px; border: 1px solid red; background: #fff0f0;'>✗ Toplu güncelleme hatası: " . $conn->error . "</div>";
    }
    echo "<meta http-equiv='refresh' content='2'>";
}

echo "<br><br><a href='add-property.php'>← Add Property Sayfasına Dön</a>";
?>
