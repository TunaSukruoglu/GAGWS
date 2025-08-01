<?php
// Check category column definition and fix the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Category Column Debug</h2>";

try {
    require_once 'db.php';
    
    // Check current column definition
    $result = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'category'");
    
    if ($result && $result->num_rows > 0) {
        $column = $result->fetch_assoc();
        echo "<h3>Current category column definition:</h3>";
        echo "<pre>";
        print_r($column);
        echo "</pre>";
        
        // Extract ENUM values
        $type = $column['Type'];
        echo "<p><strong>Column Type:</strong> $type</p>";
        
        if (strpos($type, 'enum') !== false) {
            // Parse ENUM values
            preg_match_all("/'([^']+)'/", $type, $matches);
            $enum_values = $matches[1];
            
            echo "<h3>Allowed ENUM values:</h3>";
            echo "<ul>";
            foreach ($enum_values as $value) {
                echo "<li><code>$value</code></li>";
            }
            echo "</ul>";
        }
    }
    
    // Check what value we're trying to insert
    echo "<h3>🧪 Test Values:</h3>";
    
    $test_values = ['residence', 'apartment', 'house', 'villa', 'office', 'shop', 'warehouse', 'land'];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Test Value</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "</tr>";
    
    foreach ($test_values as $test_value) {
        echo "<tr>";
        echo "<td style='padding: 6px;'><code>$test_value</code></td>";
        
        // Test if this value would work
        try {
            $stmt = $conn->prepare("SELECT ? as test_category");
            $stmt->bind_param("s", $test_value);
            $stmt->execute();
            
            // Try to insert into a test scenario
            $test_query = "SELECT 1 FROM properties WHERE category = ? LIMIT 1";
            $test_stmt = $conn->prepare($test_query);
            $test_stmt->bind_param("s", $test_value);
            $test_stmt->execute();
            
            echo "<td style='padding: 6px; color: green;'>✅ Valid</td>";
            
        } catch (Exception $e) {
            echo "<td style='padding: 6px; color: red;'>❌ Invalid</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Suggest fix
    echo "<h3>🔧 Suggested Fix:</h3>";
    
    $suggested_values = ['apartment', 'house', 'villa', 'office', 'shop', 'warehouse', 'land'];
    $enum_string = "'" . implode("','", $suggested_values) . "'";
    
    echo "<p>If the current ENUM doesn't include 'residence', we should either:</p>";
    echo "<ol>";
    echo "<li><strong>Change the test value</strong> from 'residence' to 'apartment'</li>";
    echo "<li><strong>Or update the ENUM</strong> to include 'residence'</li>";
    echo "</ol>";
    
    echo "<h4>Option 1: Quick Fix (Change test value)</h4>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    echo "<code>// Change this line in test:<br>";
    echo "\$category = 'residence'; // ❌ Not in ENUM<br>";
    echo "\$category = 'apartment'; // ✅ Valid ENUM value</code>";
    echo "</div>";
    
    echo "<h4>Option 2: Update ENUM (Add 'residence')</h4>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
    echo "<code>ALTER TABLE properties MODIFY COLUMN category ENUM($enum_string,'residence');</code>";
    echo "</div>";
    
    if (isset($_GET['fix']) && $_GET['fix'] == 'enum') {
        echo "<div style='background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h4>🚀 Updating ENUM...</h4>";
        
        try {
            $update_query = "ALTER TABLE properties MODIFY COLUMN category ENUM('apartment','house','villa','office','shop','warehouse','land','residence')";
            
            if ($conn->query($update_query)) {
                echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "✅ ENUM updated successfully! 'residence' value is now allowed.";
                echo "</div>";
                echo "<script>setTimeout(() => window.location.href = window.location.href.split('?')[0], 2000);</script>";
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "❌ Update failed: " . $conn->error;
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "❌ Error: " . $e->getMessage();
            echo "</div>";
        }
        echo "</div>";
    }
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='?fix=enum' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Fix ENUM (Add 'residence')</a>";
    echo "<a href='db-records-check.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔄 Retry Test</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2, h3 { color: #333; }
table { width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; text-align: left; }
code { background: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
</style>
