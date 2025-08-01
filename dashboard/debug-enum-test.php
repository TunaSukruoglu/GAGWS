<?php
try {
    // Database connection
    include '../db.php';
    
    echo "<h2>Database ENUM Check</h2>";
    
    // Check usage_status column
    $query = "SHOW COLUMNS FROM properties LIKE 'usage_status'";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo "<h3>usage_status Column:</h3>";
        echo "<p><strong>Type:</strong> " . htmlspecialchars($row['Type']) . "</p>";
        
        // Extract ENUM values
        if (preg_match_all("/'([^']+)'/", $row['Type'], $matches)) {
            echo "<h4>Valid ENUM Values:</h4>";
            echo "<ul>";
            foreach ($matches[1] as $i => $value) {
                echo "<li><code>'" . htmlspecialchars($value) . "'</code></li>";
            }
            echo "</ul>";
            
            // Show as PHP array
            echo "<h4>PHP Array for Code:</h4>";
            echo "<pre>\$valid_usage_statuses = ['" . implode("', '", $matches[1]) . "'];</pre>";
        }
    }
    
    // Test a simple insert to see exact error
    echo "<h3>Testing Simple Insert:</h3>";
    
    // Get a test user ID
    $user_query = $conn->query("SELECT id FROM users LIMIT 1");
    if ($user_data = $user_query->fetch_assoc()) {
        $test_user_id = $user_data['id'];
        
        echo "<p>Testing with user ID: $test_user_id</p>";
        
        // Simple test insert
        $test_query = "INSERT INTO properties (user_id, title, description, price, type, category, usage_status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $test_stmt = $conn->prepare($test_query);
        
        if ($test_stmt) {
            $test_title = "Test Property";
            $test_desc = "Test Description";
            $test_price = 100000;
            $test_type = "sale";
            $test_category = "apartment";
            $test_usage = "Bos";
            
            $test_stmt->bind_param("issdsss", $test_user_id, $test_title, $test_desc, $test_price, $test_type, $test_category, $test_usage);
            
            if ($test_stmt->execute()) {
                $test_id = $conn->insert_id;
                echo "<p style='color: green;'>✅ Test insert successful! ID: $test_id</p>";
                
                // Clean up test data
                $conn->query("DELETE FROM properties WHERE id = $test_id");
                echo "<p>Test data cleaned up.</p>";
            } else {
                echo "<p style='color: red;'>❌ Test insert failed: " . htmlspecialchars($test_stmt->error) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Test prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
