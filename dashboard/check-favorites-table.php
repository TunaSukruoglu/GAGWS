<?php
require_once '../db.php';

echo "<h2>Favorites Table Check</h2>";

try {
    // Check if favorites table exists
    $result = $conn->query("SHOW TABLES LIKE 'favorites'");
    
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✅ <strong>Favorites table exists</strong></p>";
        
        // Check table structure
        $structure = $conn->query("DESCRIBE favorites");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check data count
        $count_result = $conn->query("SELECT COUNT(*) as total FROM favorites");
        $count = $count_result->fetch_assoc()['total'];
        echo "<p><strong>Total records:</strong> $count</p>";
        
        // Test the actual query from dashboard
        echo "<h3>Testing Dashboard Query:</h3>";
        $favorite_stats_query = "SELECT 
            COUNT(*) as total_favorites,
            COUNT(DISTINCT property_id) as unique_properties,
            COUNT(DISTINCT user_id) as unique_users,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_favorites,
            SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_favorites
            FROM favorites";
        
        $favorite_stats = $conn->query($favorite_stats_query);
        
        if ($favorite_stats) {
            $stats = $favorite_stats->fetch_assoc();
            echo "<p style='color: green;'>✅ Query executed successfully</p>";
            echo "<ul>";
            foreach ($stats as $key => $value) {
                echo "<li><strong>$key:</strong> " . ($value !== null ? $value : 'NULL') . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>❌ Query failed: " . $conn->error . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ <strong>Favorites table does NOT exist</strong></p>";
        echo "<h3>Creating Favorites Table:</h3>";
        
        $create_favorites_table = "
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            property_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, property_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";
        
        if ($conn->query($create_favorites_table)) {
            echo "<p style='color: green;'>✅ Favorites table created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create favorites table: " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
