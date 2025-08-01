<?php
// FIELD VERIFICATION TEST
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>🎯 Final Field Verification Test</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if any fields still have NULL or empty values
    echo "<h3>NULL/Empty Value Check</h3>";
    
    $problem_fields = [];
    $fields_to_check = [
        'parking' => ['NULL', '', 'NULL', '-'],
        'usage_status' => ['NULL', '', 'NULL', '-'],  
        'credit_eligible' => ['NULL', '', 'NULL', '-'],
        'furnished' => ['NULL', '', 'NULL', '-', '0']
    ];
    
    foreach($fields_to_check as $field => $bad_values) {
        $conditions = [];
        foreach($bad_values as $bad_value) {
            if ($bad_value === 'NULL') {
                $conditions[] = "{$field} IS NULL";
            } else {
                $conditions[] = "{$field} = '" . $conn->real_escape_string($bad_value) . "'";
            }
        }
        
        $where_clause = implode(' OR ', $conditions);
        $result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE {$where_clause}");
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            $problem_fields[] = $field;
            echo "<p style='color: red;'>❌ <strong>{$field}:</strong> {$count} properties with empty values</p>";
        } else {
            echo "<p style='color: green;'>✅ <strong>{$field}:</strong> All properties have valid values</p>";
        }
    }
    
    // Test random sample properties  
    echo "<h3>Random Sample Test (10 Properties)</h3>";
    $result = $conn->query("SELECT 
        id, title, parking, usage_status, credit_eligible, furnished 
        FROM properties 
        ORDER BY RAND() 
        LIMIT 10");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Title</th><th>Parking</th><th>Usage Status</th><th>Credit Eligible</th><th>Furnished</th><th>Test Link</th>";
    echo "</tr>";
    
    $all_good = true;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['title'], 0, 25)) . "...</td>";
        
        // Check each field
        $row_status = [];
        foreach(['parking', 'usage_status', 'credit_eligible', 'furnished'] as $field) {
            $value = $row[$field];
            $is_empty = empty($value) || in_array($value, ['NULL', '-', '0']);
            
            if ($is_empty) {
                echo "<td style='color: red; font-weight: bold;'>❌ " . htmlspecialchars($value ?: 'EMPTY') . "</td>";
                $all_good = false;
            } else {
                echo "<td style='color: green;'>✅ " . htmlspecialchars($value) . "</td>";
            }
        }
        
        echo "<td><a href='property-details.php?id={$row['id']}' target='_blank' style='color: blue;'>View Details</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Summary
    echo "<h3>📊 Final Summary</h3>";
    if (empty($problem_fields) && $all_good) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #155724; margin: 0;'>🎉 SUCCESS!</h4>";
        echo "<p style='color: #155724; margin: 5px 0 0 0;'>All fields are working perfectly:</p>";
        echo "<ul style='color: #155724;'>";
        echo "<li>✅ <strong>Parking System:</strong> 3 options (Otopark Yok, Açık Otopark, Kapalı Otopark)</li>";
        echo "<li>✅ <strong>Usage Status:</strong> 4 options (Boş, Kiracılı, Malik Kullanımında, Yatırım Amaçlı)</li>";
        echo "<li>✅ <strong>Credit Eligible:</strong> 2 options (Evet/Hayır)</li>";
        echo "<li>✅ <strong>Furnished:</strong> 2 options (Evet/Hayır)</li>";
        echo "</ul>";
        echo "<p style='color: #155724; font-weight: bold;'>✨ Artık tüm alanlar düzgün çalışıyor ve seçilen değerler ilan detaylarında görünüyor!</p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px;'>";
        echo "<h4 style='color: #721c24;'>❌ Issues Found</h4>";
        echo "<p style='color: #721c24;'>The following fields still have problems: " . implode(', ', $problem_fields) . "</p>";
        echo "</div>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { font-family: Arial, sans-serif; font-size: 12px; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
