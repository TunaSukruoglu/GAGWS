<?php
session_start();
include '../db.php';

// Test için en son eklenen bir ilanın ID'sini alalım
$test_query = "SELECT id, title FROM properties ORDER BY id DESC LIMIT 1";
$result = $conn->query($test_query);

if ($result && $result->num_rows > 0) {
    $property = $result->fetch_assoc();
    $property_id = $property['id'];
    $property_title = $property['title'];
    
    echo "<h2>Edit Mode Test</h2>";
    echo "<p><strong>Test edilecek ilan:</strong> ID: $property_id - $property_title</p>";
    echo "<p><a href='add-property.php?edit=$property_id' target='_blank'>Edit Mode Testi → add-property.php?edit=$property_id</a></p>";
    
    // İlanın tüm verilerini gösterelim
    $detail_query = "SELECT * FROM properties WHERE id = ?";
    $stmt = $conn->prepare($detail_query);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property_data = $stmt->get_result()->fetch_assoc();
    
    echo "<h3>İlan Verileri:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    foreach ($property_data as $key => $value) {
        $display_value = is_null($value) ? '<em>NULL</em>' : 
                        (strlen($value) > 100 ? substr($value, 0, 100) . "..." : $value);
        echo "<tr>";
        echo "<td style='padding: 8px; font-weight: bold;'>$key</td>";
        echo "<td style='padding: 8px;'>$display_value</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<p>Test edilecek ilan bulunamadı. Önce bir ilan ekleyin.</p>";
}
?>
