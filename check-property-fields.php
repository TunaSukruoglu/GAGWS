<?php
// CORRECT DATABASE CONNECTION
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Property Details Field Analysis</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to: {$dbname}</p>";
    
    // Check specific fields distribution
    $fields = ['usage_status', 'credit_eligible', 'parking', 'furnished'];
    
    foreach($fields as $field) {
        echo "<h3>{$field} Distribution</h3>";
        
        $result = $conn->query("SELECT {$field}, COUNT(*) as count FROM properties GROUP BY {$field} ORDER BY count DESC LIMIT 10");
        
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
            echo "<tr><th>Value</th><th>Count</th><th>Display Status</th></tr>";
            
            while ($row = $result->fetch_assoc()) {
                $value = $row[$field];
                $empty_check = empty($value) || $value === '0' || $value === '-' || $value === 'NULL' || strtolower($value) === 'null';
                $status = $empty_check ? '<span style="color: red;">❌ EMPTY/NULL</span>' : '<span style="color: green;">✅ HAS VALUE</span>';
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($value ?: 'NULL/Empty') . "</td>";
                echo "<td>" . $row['count'] . "</td>";
                echo "<td>" . $status . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Get a sample property to see actual data
    echo "<h3>Sample Property Data</h3>";
    $result = $conn->query("SELECT id, title, usage_status, credit_eligible, parking, furnished FROM properties WHERE usage_status IS NOT NULL AND usage_status != '' AND usage_status != '0' LIMIT 5");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Title</th><th>Usage Status</th><th>Credit Eligible</th><th>Parking</th><th>Furnished</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars(substr($row['title'], 0, 30)) . "...</td>";
            echo "<td>" . htmlspecialchars($row['usage_status'] ?: 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['credit_eligible'] ?: 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['parking'] ?: 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['furnished'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if these fields have proper values
    echo "<h3>Field Status Summary</h3>";
    foreach($fields as $field) {
        $result = $conn->query("SELECT COUNT(*) as total, 
                               COUNT(CASE WHEN {$field} IS NOT NULL AND {$field} != '' AND {$field} != '0' AND {$field} != '-' THEN 1 END) as valid
                               FROM properties");
        $stats = $result->fetch_assoc();
        $percentage = $stats['total'] > 0 ? round(($stats['valid'] / $stats['total']) * 100, 2) : 0;
        
        echo "<p><strong>{$field}:</strong> {$stats['valid']} valid out of {$stats['total']} total ({$percentage}%)</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
