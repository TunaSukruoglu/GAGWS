<?php
// CORRECT DATABASE CONNECTION
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Parking Field Test & Update</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get first property with detailed parking info
    echo "<h3>Current Parking Data</h3>";
    $result = $conn->query("SELECT id, title, parking FROM properties LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Parking (Raw)</th><th>Action</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "...</td>";
        echo "<td>" . htmlspecialchars($row['parking'] ?: 'NULL') . "</td>";
        echo "<td><a href='test-parking-update.php?id={$row['id']}' target='_blank'>Test Update</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test updating parking for property ID 1
    if (isset($_GET['update']) && $_GET['update'] === 'test') {
        $property_id = intval($_GET['id'] ?? 1);
        $new_parking = $_GET['parking'] ?? 'Açık Otopark';
        
        echo "<h3>Testing Parking Update</h3>";
        $stmt = $conn->prepare("UPDATE properties SET parking = ? WHERE id = ?");
        $stmt->bind_param("si", $new_parking, $property_id);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Parking updated to '{$new_parking}' for property ID {$property_id}</p>";
            
            // Verify
            $verify = $conn->query("SELECT parking FROM properties WHERE id = {$property_id}");
            $check = $verify->fetch_assoc();
            echo "<p>Verification: " . htmlspecialchars($check['parking']) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Update failed: " . $stmt->error . "</p>";
        }
    }
    
    // Manual update form
    echo "<hr><h3>Manual Parking Update Test</h3>";
    echo "<form method='GET'>";
    echo "<label>Property ID: <input type='number' name='id' value='1' min='1'></label><br><br>";
    echo "<label>New Parking Value:</label><br>";
    echo "<select name='parking'>";
    echo "<option value='Otopark Yok'>Otopark Yok</option>";
    echo "<option value='Açık Otopark' selected>Açık Otopark</option>";
    echo "<option value='Kapalı Otopark'>Kapalı Otopark</option>";
    echo "</select><br><br>";
    echo "<input type='hidden' name='update' value='test'>";
    echo "<button type='submit'>Update Parking</button>";
    echo "</form>";
    
    // Check property details display for a specific property
    if (isset($_GET['test_display'])) {
        $property_id = intval($_GET['test_display']);
        echo "<h3>Testing Display for Property {$property_id}</h3>";
        
        $result = $conn->query("SELECT * FROM properties WHERE id = {$property_id}");
        $property = $result->fetch_assoc();
        
        if ($property) {
            echo "<p><strong>Raw parking value:</strong> " . htmlspecialchars($property['parking']) . "</p>";
            
            // Test display logic
            $parking = $property['parking'] ?? '';
            echo "<p><strong>Display logic result:</strong> ";
            if (!empty($parking) && $parking !== '0' && $parking !== '-' && $parking !== 'NULL' && strtolower($parking) !== 'null') {
                if ($parking === 'Otopark Yok') {
                    echo 'Otopark Yok';
                } elseif ($parking === 'Açık Otopark') {
                    echo 'Açık Otopark';
                } elseif ($parking === 'Kapalı Otopark') {
                    echo 'Kapalı Otopark';
                } else {
                    echo htmlspecialchars($parking);
                }
            } else {
                echo 'Belirtilmemiş';
            }
            echo "</p>";
            
            echo "<p><a href='property-details.php?id={$property_id}' target='_blank'>View Property Details Page</a></p>";
        }
    }
    
    echo "<hr><p><a href='?test_display=1'>Test Display for Property 1</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
