<?php
// Test database connection with updated password
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";
echo "<p>Testing connection with updated credentials...</p>";

$servername = "localhost";
$username = "gokhanay_user";
$password = "GkHn2025!Secure123";
$dbname = "gokhanay_db";
$port = 3306;

echo "<p><strong>Connection Details:</strong></p>";
echo "<ul>";
echo "<li>Server: $servername</li>";
echo "<li>Username: $username</li>";
echo "<li>Database: $dbname</li>";
echo "<li>Port: $port</li>";
echo "</ul>";

try {
    echo "<p>Attempting connection...</p>";
    
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";
    echo "<p>MySQL version: " . $conn->server_info . "</p>";
    
    // Test charset
    $conn->set_charset("utf8mb4");
    echo "<p style='color: green;'>✅ Character set configured successfully!</p>";
    
    // Test database selection
    $result = $conn->query("SELECT DATABASE() as current_db");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Current database: " . $row['current_db'] . "</p>";
    }
    
    // Test basic query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "<p style='color: green;'>✅ Database tables accessible (" . $result->num_rows . " tables found)</p>";
        
        echo "<h3>Available Tables:</h3>";
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    // Test users table
    $users_result = $conn->query("SELECT COUNT(*) as user_count FROM users");
    if ($users_result) {
        $user_data = $users_result->fetch_assoc();
        echo "<p style='color: green;'>✅ Users table: " . $user_data['user_count'] . " users found</p>";
    }
    
    // Test properties table
    $properties_result = $conn->query("SELECT COUNT(*) as property_count FROM properties");
    if ($properties_result) {
        $property_data = $properties_result->fetch_assoc();
        echo "<p style='color: green;'>✅ Properties table: " . $property_data['property_count'] . " properties found</p>";
    }
    
    $conn->close();
    echo "<p style='color: green;'><strong>✅ All tests completed successfully!</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Database connection failed:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Possible solutions:</h3>";
    echo "<ul>";
    echo "<li>Check if MySQL service is running</li>";
    echo "<li>Verify username and password are correct</li>";
    echo "<li>Check if database 'gokhanay_db' exists</li>";
    echo "<li>Verify port 3306 is accessible</li>";
    echo "<li>Check MySQL user permissions</li>";
    echo "</ul>";
}
?>
