<?php
include '../db.php';

$property_id = 51;

echo "<h3>İlan Detay Kontrol - ID: $property_id - Oda Sayısı Odaklı</h3>";

$query = "SELECT id, title, room_count, bedrooms, bathrooms FROM properties WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $property = $result->fetch_assoc()) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Alan</th><th>Değer</th><th>Detay</th></tr>";
    
    echo "<tr>";
    echo "<td><strong>ID</strong></td>";
    echo "<td>" . htmlspecialchars($property['id']) . "</td>";
    echo "<td>-</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td><strong>Başlık</strong></td>";
    echo "<td>" . htmlspecialchars($property['title']) . "</td>";
    echo "<td>-</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td><strong>room_count</strong></td>";
    echo "<td>[" . htmlspecialchars($property['room_count'] ?? 'NULL') . "]</td>";
    echo "<td>Karakter sayısı: " . strlen($property['room_count'] ?? '') . ", Tip: " . gettype($property['room_count']) . "</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td><strong>bedrooms</strong></td>";
    echo "<td>[" . htmlspecialchars($property['bedrooms'] ?? 'NULL') . "]</td>";
    echo "<td>Sayısal değer</td>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<td><strong>bathrooms</strong></td>";
    echo "<td>[" . htmlspecialchars($property['bathrooms'] ?? 'NULL') . "]</td>";
    echo "<td>Sayısal değer</td>";
    echo "</tr>";
    
    echo "</table>";
    
    // Karşılaştırma test et
    echo "<h4>Karşılaştırma Testleri:</h4>";
    $room_count_value = $property['room_count'];
    $test_values = ['1+0', '1+1', '2+1', '2.5+1', '3+1', '3.5+1', '4+1', 'var', '', null];
    
    echo "<table border='1' cellpadding='4'>";
    echo "<tr><th>Test Değeri</th><th>Eşleşme (==)</th><th>Eşleşme (===)</th></tr>";
    foreach ($test_values as $test_val) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($test_val ?? 'NULL') . "</td>";
        echo "<td>" . ($room_count_value == $test_val ? '✓ EVET' : '✗ HAYIR') . "</td>";
        echo "<td>" . ($room_count_value === $test_val ? '✓ EVET' : '✗ HAYIR') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>İlan bulunamadı!</p>";
}
?>
