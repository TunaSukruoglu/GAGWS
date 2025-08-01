<?php
// PARKING DEBUG - Hangi property'ye bakıyorsun?
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>🔍 Parking Debug - Hangi Property?</h2>";

// URL'den property ID'sini al
$property_id = $_GET['id'] ?? null;

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if ($property_id) {
        echo "<h3>Property {$property_id} Detayları:</h3>";
        $result = $conn->query("SELECT * FROM properties WHERE id = " . intval($property_id));
        
        if ($result && $result->num_rows > 0) {
            $property = $result->fetch_assoc();
            
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<p><strong>ID:</strong> " . $property['id'] . "</p>";
            echo "<p><strong>Title:</strong> " . htmlspecialchars($property['title']) . "</p>";
            echo "<p><strong>Database Parking Value:</strong> <span style='color: red; font-weight: bold;'>'" . ($property['parking'] ?? 'NULL') . "'</span></p>";
            echo "<p><strong>What will display:</strong> ";
            
            $parking = $property['parking'] ?? '';
            if (!empty($parking) && $parking !== '0' && $parking !== '-' && $parking !== 'NULL' && strtolower($parking) !== 'null') {
                if ($parking === 'Otopark Yok') {
                    echo "<span style='color: blue; font-weight: bold;'>Otopark Yok</span>";
                }
                elseif ($parking === 'Açık Otopark') {
                    echo "<span style='color: green; font-weight: bold;'>Açık Otopark</span>";
                }
                elseif ($parking === 'Kapalı Otopark') {
                    echo "<span style='color: purple; font-weight: bold;'>Kapalı Otopark</span>";
                } else {
                    echo "<span style='color: orange; font-weight: bold;'>Unknown: " . htmlspecialchars($parking) . "</span>";
                }
            } else {
                echo "<span style='color: red; font-weight: bold;'>Nothing (empty/null)</span>";
            }
            echo "</p>";
            echo "</div>";
            
            // Test form to change it
            echo "<h3>Parking Değiştirme Testi:</h3>";
            echo "<form method='POST' style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
            echo "<input type='hidden' name='property_id' value='{$property_id}'>";
            echo "<label><strong>Yeni Parking Durumu:</strong></label><br>";
            echo "<select name='new_parking' style='padding: 8px; margin: 10px 0;'>";
            echo "<option value='Otopark Yok'" . ($parking === 'Otopark Yok' ? ' selected' : '') . ">Otopark Yok</option>";
            echo "<option value='Açık Otopark'" . ($parking === 'Açık Otopark' ? ' selected' : '') . ">Açık Otopark</option>";
            echo "<option value='Kapalı Otopark'" . ($parking === 'Kapalı Otopark' ? ' selected' : '') . ">Kapalı Otopark</option>";
            echo "</select><br>";
            echo "<input type='submit' name='update_parking' value='Parking Durumunu Değiştir' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>";
            echo "</form>";
            
        } else {
            echo "<p style='color: red;'>Property bulunamadı!</p>";
        }
    }
    
    // Handle form submission
    if ($_POST['update_parking'] ?? false) {
        $update_id = intval($_POST['property_id']);
        $new_parking = $conn->real_escape_string($_POST['new_parking']);
        
        $update_result = $conn->query("UPDATE properties SET parking = '{$new_parking}' WHERE id = {$update_id}");
        
        if ($update_result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>✅ Güncelleme Başarılı!</h4>";
            echo "<p>Property {$update_id} parking durumu '{$new_parking}' olarak güncellendi.</p>";
            echo "<p><a href='property-details.php?id={$update_id}' target='_blank'>Yeni Durumu Kontrol Et</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>❌ Güncelleme Hatası!</h4>";
            echo "<p>" . $conn->error . "</p>";
            echo "</div>";
        }
    }
    
    // List recent properties for testing
    echo "<h3>Son Eklenen Property'ler:</h3>";
    $result = $conn->query("SELECT id, title, parking FROM properties ORDER BY id DESC LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Current Parking</th><th>Test</th><th>Debug</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "...</td>";
        echo "<td style='color: " . ($row['parking'] === 'Otopark Yok' ? 'red' : ($row['parking'] === 'Açık Otopark' ? 'green' : 'purple')) . ";'>" . htmlspecialchars($row['parking']) . "</td>";
        echo "<td><a href='property-details.php?id=" . $row['id'] . "' target='_blank'>View</a></td>";
        echo "<td><a href='?id=" . $row['id'] . "'>Debug</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

if (!$property_id) {
    echo "<p style='background: #fff3cd; padding: 15px; border-radius: 5px;'>Hangi property'nin parking durumunda sorun var? Yukarıdaki listeden birine tıklayarak debug edebilirsin.</p>";
}
?>

<style>
table { font-family: Arial, sans-serif; font-size: 12px; margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
