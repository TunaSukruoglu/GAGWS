<?php
echo "<h2>Server Environment Comparison</h2>";

echo "<h3>PHP Information:</h3>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";
echo "<strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "<br>";

echo "<h3>MySQL Information:</h3>";
try {
    require_once 'db.php';
    
    if (isset($conn) && $conn) {
        echo "<div style='color: green;'>✅ Database Connected</div>";
        
        // Get MySQL version
        $version_result = $conn->query("SELECT VERSION() as version");
        if ($version_result && $row = $version_result->fetch_assoc()) {
            echo "<strong>MySQL Version:</strong> " . $row['version'] . "<br>";
        }
        
        // Get character set info
        $charset_result = $conn->query("SHOW VARIABLES LIKE 'character_set_%'");
        echo "<h4>Character Set Variables:</h4>";
        if ($charset_result) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            while ($row = $charset_result->fetch_assoc()) {
                echo "<tr><td>" . $row['Variable_name'] . "</td><td>" . $row['Value'] . "</td></tr>";
            }
            echo "</table>";
        }
        
        // Check properties table ENUM columns
        echo "<h4>Properties Table ENUM Columns:</h4>";
        $enum_result = $conn->query("SHOW COLUMNS FROM properties WHERE Type LIKE 'enum%'");
        if ($enum_result) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
            while ($row = $enum_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Test specific ENUM value
        echo "<h4>Test ENUM Insert:</h4>";
        try {
            $test_query = "INSERT INTO properties (user_id, title, price, usage_status) VALUES (1, 'SERVER_TEST', 100000, 'Bos')";
            if ($conn->query($test_query)) {
                $insert_id = $conn->insert_id;
                echo "<div style='color: green;'>✅ ENUM Insert SUCCESS - ID: $insert_id</div>";
                // Clean up
                $conn->query("DELETE FROM properties WHERE id = $insert_id");
            } else {
                echo "<div style='color: red;'>❌ ENUM Insert FAILED: " . $conn->error . "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ ENUM Insert EXCEPTION: " . $e->getMessage() . "</div>";
        }
        
    } else {
        echo "<div style='color: red;'>❌ Database Connection Failed</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database Error: " . $e->getMessage() . "</div>";
}

echo "<h3>PHP Extensions:</h3>";
$extensions = ['mysqli', 'pdo', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<strong>$ext:</strong> " . ($loaded ? '<span style="color: green;">✅ Loaded</span>' : '<span style="color: red;">❌ Not Loaded</span>') . "<br>";
}

echo "<h3>Error Reporting:</h3>";
echo "<strong>Error Reporting:</strong> " . error_reporting() . "<br>";
echo "<strong>Display Errors:</strong> " . ini_get('display_errors') . "<br>";
echo "<strong>Log Errors:</strong> " . ini_get('log_errors') . "<br>";

echo "<h3>Memory & Limits:</h3>";
echo "<strong>Memory Limit:</strong> " . ini_get('memory_limit') . "<br>";
echo "<strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "<br>";
echo "<strong>Upload Max Size:</strong> " . ini_get('upload_max_filesize') . "<br>";
?>
