<?php
// PROPERTY 58 PERFORMANCE ANALYSIS
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>🐌 Property 58 Performance Analysis</h2>";

$start_time = microtime(true);

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $db_connect_time = microtime(true) - $start_time;
    echo "<p>✅ Database connection: <strong>" . number_format($db_connect_time * 1000, 2) . "ms</strong></p>";
    
    // 1. Property 58 Basic Query
    $query_start = microtime(true);
    $result = $conn->query("SELECT * FROM properties WHERE id = 58");
    $basic_query_time = microtime(true) - $query_start;
    
    echo "<p>📊 Basic property query: <strong>" . number_format($basic_query_time * 1000, 2) . "ms</strong></p>";
    
    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();
        
        echo "<h3>Property 58 Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Field</th><th>Value</th><th>Length</th><th>Issues</th></tr>";
        
        $potential_issues = [];
        
        foreach($property as $field => $value) {
            $value_length = strlen($value ?? '');
            $issues = [];
            
            // Check for potential issues
            if ($value_length > 1000) {
                $issues[] = "Very long content";
                $potential_issues[] = "$field: Very long content ({$value_length} chars)";
            }
            
            if ($field === 'images' && $value_length > 500) {
                $issues[] = "Many images";
                $potential_issues[] = "$field: Many images ({$value_length} chars)";
            }
            
            if (empty($value) && in_array($field, ['title', 'description', 'price'])) {
                $issues[] = "Empty required field";
                $potential_issues[] = "$field: Empty required field";
            }
            
            echo "<tr>";
            echo "<td><strong>$field</strong></td>";
            echo "<td>" . htmlspecialchars(substr($value ?? '', 0, 50)) . (strlen($value ?? '') > 50 ? '...' : '') . "</td>";
            echo "<td>$value_length</td>";
            echo "<td style='color: " . (empty($issues) ? 'green' : 'red') . ";'>" . (empty($issues) ? 'OK' : implode(', ', $issues)) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // 2. Images Query Test
        echo "<h3>Images Analysis:</h3>";
        $images_start = microtime(true);
        $images_result = $conn->query("SELECT * FROM property_images WHERE property_id = 58");
        $images_query_time = microtime(true) - $images_start;
        
        echo "<p>📸 Images query: <strong>" . number_format($images_query_time * 1000, 2) . "ms</strong></p>";
        
        if ($images_result) {
            $image_count = $images_result->num_rows;
            echo "<p>Image count: <strong>$image_count images</strong></p>";
            
            if ($image_count > 20) {
                $potential_issues[] = "Too many images: $image_count (may slow down page)";
            }
            
            // Check image sizes
            echo "<h4>Image Details:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Filename</th><th>File Size Check</th></tr>";
            
            while ($img = $images_result->fetch_assoc()) {
                $file_path = "../uploads/properties/" . $img['filename'];
                $file_exists = file_exists($file_path);
                $file_size = $file_exists ? filesize($file_path) : 0;
                
                echo "<tr>";
                echo "<td>" . $img['id'] . "</td>";
                echo "<td>" . htmlspecialchars($img['filename']) . "</td>";
                echo "<td>";
                if ($file_exists) {
                    $size_mb = $file_size / (1024 * 1024);
                    echo number_format($size_mb, 2) . " MB";
                    if ($size_mb > 2) {
                        echo " <span style='color: red;'>⚠️ Large file</span>";
                        $potential_issues[] = "Large image: " . $img['filename'] . " (" . number_format($size_mb, 2) . " MB)";
                    }
                } else {
                    echo "<span style='color: red;'>❌ Missing file</span>";
                    $potential_issues[] = "Missing image file: " . $img['filename'];
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // 3. Features Query Test
        echo "<h3>Features Analysis:</h3>";
        $features_start = microtime(true);
        $features_result = $conn->query("SELECT * FROM property_features WHERE property_id = 58");
        $features_query_time = microtime(true) - $features_start;
        
        echo "<p>🔧 Features query: <strong>" . number_format($features_query_time * 1000, 2) . "ms</strong></p>";
        
        if ($features_result) {
            $features_count = $features_result->num_rows;
            echo "<p>Features count: <strong>$features_count features</strong></p>";
            
            if ($features_count > 50) {
                $potential_issues[] = "Too many features: $features_count";
            }
        }
        
        // 4. Total Performance Summary
        $total_time = microtime(true) - $start_time;
        echo "<h3>🎯 Performance Summary:</h3>";
        echo "<div style='background: " . ($total_time > 1 ? "#f8d7da" : "#d4edda") . "; padding: 15px; border-radius: 5px;'>";
        echo "<p><strong>Total Analysis Time:</strong> " . number_format($total_time * 1000, 2) . "ms</p>";
        
        if (!empty($potential_issues)) {
            echo "<h4 style='color: red;'>⚠️ Potential Issues Found:</h4>";
            echo "<ul>";
            foreach($potential_issues as $issue) {
                echo "<li style='color: red;'>$issue</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: green;'>✅ No obvious performance issues found in data</p>";
        }
        echo "</div>";
        
        // 5. Quick Links
        echo "<h3>🔗 Test Links:</h3>";
        echo "<p><a href='property-details.php?id=58' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test Property 58 Details (Time This!)</a></p>";
        echo "<p><a href='../portfoy.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 Back to Portfolio</a></p>";
        
        // 6. Recommendations
        echo "<h3>🛠️ Performance Recommendations:</h3>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
        echo "<ul>";
        echo "<li>Check property-details.php for unnecessary database queries</li>";
        echo "<li>Optimize image loading (lazy loading, compression)</li>";
        echo "<li>Add database indexes if missing</li>";
        echo "<li>Check for slow PHP code blocks</li>";
        echo "<li>Consider caching frequently accessed data</li>";
        echo "</ul>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Property 58 not found!</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
table { font-family: Arial, sans-serif; font-size: 12px; margin: 10px 0; }
th, td { padding: 6px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; font-weight: bold; }
tr:nth-child(even) { background-color: #f9f9f9; }
</style>
