<?php
// PROPERTY 57 EDIT TEST
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Property 57 Edit Test</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get current property 57 data
    $result = $conn->query("SELECT * FROM properties WHERE id = 57");
    
    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();
        
        echo "<h3>Current Property 57 Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        
        $important_fields = ['id', 'title', 'parking', 'usage_status', 'credit_eligible', 'furnished'];
        
        foreach($important_fields as $field) {
            echo "<tr>";
            echo "<th style='background: #f0f0f0; padding: 8px;'>" . ucfirst(str_replace('_', ' ', $field)) . "</th>";
            echo "<td style='padding: 8px;'>";
            
            $value = $property[$field] ?? 'NULL';
            if ($field === 'parking') {
                $color = $value === 'Otopark Yok' ? 'red' : ($value === 'Açık Otopark' ? 'green' : ($value === 'Kapalı Otopark' ? 'blue' : 'gray'));
                echo "<span style='color: {$color}; font-weight: bold;'>" . htmlspecialchars($value) . "</span>";
            } else {
                echo htmlspecialchars($value);
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test parking update
        echo "<h3>Test Parking Update:</h3>";
        echo "<form method='POST' style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<label><strong>Select New Parking:</strong></label><br>";
        echo "<select name='new_parking' style='padding: 10px; margin: 10px 0; width: 200px;'>";
        echo "<option value='Otopark Yok'" . ($property['parking'] === 'Otopark Yok' ? ' selected' : '') . ">Otopark Yok</option>";
        echo "<option value='Açık Otopark'" . ($property['parking'] === 'Açık Otopark' ? ' selected' : '') . ">Açık Otopark</option>";
        echo "<option value='Kapalı Otopark'" . ($property['parking'] === 'Kapalı Otopark' ? ' selected' : '') . ">Kapalı Otopark</option>";
        echo "</select><br>";
        echo "<input type='submit' name='update_parking' value='Update Parking' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>";
        echo "</form>";
        
        // Handle parking update
        if ($_POST['update_parking'] ?? false) {
            $new_parking = $conn->real_escape_string($_POST['new_parking']);
            
            $update_result = $conn->query("UPDATE properties SET parking = '{$new_parking}' WHERE id = 57");
            
            if ($update_result) {
                echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
                echo "<h4>✅ SUCCESS!</h4>";
                echo "<p>Property 57 parking updated to: <strong>{$new_parking}</strong></p>";
                echo "<p><a href='property-details.php?id=57' target='_blank' style='color: #007bff;'>View Property Details</a></p>";
                echo "<p><a href='add-property.php?edit=57' target='_blank' style='color: #007bff;'>Edit Property Form</a></p>";
                echo "</div>";
                
                // Refresh property data
                $result = $conn->query("SELECT parking FROM properties WHERE id = 57");
                $updated_property = $result->fetch_assoc();
                echo "<p><strong>Verified new parking value:</strong> <span style='color: green; font-weight: bold;'>" . $updated_property['parking'] . "</span></p>";
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>";
                echo "<h4>❌ Update Failed!</h4>";
                echo "<p>Error: " . $conn->error . "</p>";
                echo "</div>";
            }
        }
        
        // Quick links
        echo "<h3>Quick Links:</h3>";
        echo "<p><a href='add-property.php?edit=57' target='_blank' style='color: #007bff;'>📝 Edit Property 57 Form</a></p>";
        echo "<p><a href='property-details.php?id=57' target='_blank' style='color: #007bff;'>👁️ View Property 57 Details</a></p>";
        
    } else {
        echo "<p style='color: red;'>Property 57 not found!</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { font-family: Arial, sans-serif; margin: 20px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
