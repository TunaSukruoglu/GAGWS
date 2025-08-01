<?php
// CORRECT DATABASE CONNECTION
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Property Field Debug</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get a sample property with actual data
    $result = $conn->query("SELECT id, title, usage_status, credit_eligible, parking, furnished 
                           FROM properties 
                           WHERE id IN (SELECT id FROM properties LIMIT 5)
                           ORDER BY id ASC LIMIT 5");
    
    if ($result) {
        while ($property = $result->fetch_assoc()) {
            echo "<div style='border: 2px solid #ccc; margin: 10px; padding: 15px;'>";
            echo "<h3>Property ID: {$property['id']} - " . htmlspecialchars(substr($property['title'], 0, 50)) . "...</h3>";
            
            echo "<h4>Raw Database Values:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Field</th><th>Raw Value</th><th>Is Empty Check</th><th>Display Logic</th></tr>";
            
            $fields = [
                'usage_status' => $property['usage_status'],
                'credit_eligible' => $property['credit_eligible'], 
                'parking' => $property['parking'],
                'furnished' => $property['furnished']
            ];
            
            foreach($fields as $field_name => $field_value) {
                $is_empty = empty($field_value) || $field_value === '0' || $field_value === '-' || $field_value === 'NULL' || strtolower($field_value) === 'null';
                $empty_status = $is_empty ? '<span style="color: red;">❌ EMPTY</span>' : '<span style="color: green;">✅ HAS VALUE</span>';
                
                // Apply display logic
                $display_value = 'Belirtilmemiş';
                if (!$is_empty) {
                    switch($field_name) {
                        case 'usage_status':
                            if (in_array($field_value, ['Boş', 'Kiracılı', 'Malik Kullanımında', 'Yatırım Amaçlı'])) {
                                $display_value = $field_value;
                            } else {
                                $display_value = 'Boş (converted)';
                            }
                            break;
                        case 'credit_eligible':
                            if (in_array($field_value, ['Evet, krediye uygun', 'Hayır, krediye uygun değil'])) {
                                $display_value = $field_value;
                            } elseif ($field_value === 'yes') {
                                $display_value = 'Evet, krediye uygun';
                            } elseif ($field_value === 'no') {
                                $display_value = 'Hayır, krediye uygun değil';
                            } else {
                                $display_value = $field_value;
                            }
                            break;
                        case 'parking':
                            if (in_array($field_value, ['Otopark Yok', 'Açık Otopark', 'Kapalı Otopark'])) {
                                $display_value = $field_value;
                            } else {
                                $display_value = $field_value;
                            }
                            break;
                        case 'furnished':
                            if (in_array($field_value, ['Evet', 'Hayır'])) {
                                $display_value = $field_value;
                            } elseif ($field_value === 'yes') {
                                $display_value = 'Evet';
                            } elseif ($field_value === 'no') {
                                $display_value = 'Hayır';
                            } else {
                                $display_value = $field_value;
                            }
                            break;
                    }
                }
                
                echo "<tr>";
                echo "<td><strong>{$field_name}</strong></td>";
                echo "<td>" . htmlspecialchars($field_value ?: 'NULL') . "</td>";
                echo "<td>" . $empty_status . "</td>";
                echo "<td style='color: blue;'>" . htmlspecialchars($display_value) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
    }
    
    echo "<hr><h3>Recommended Fix</h3>";
    echo "<p>The issue seems to be that the condition check is too strict. We need to:</p>";
    echo "<ol>";
    echo "<li>Remove the check for '-' in the empty condition</li>";
    echo "<li>Make sure the display logic shows actual values even if they're not in standard format</li>";
    echo "<li>Add fallback display for non-standard values</li>";
    echo "</ol>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
