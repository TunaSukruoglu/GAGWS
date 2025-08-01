<?php
echo "<h3>Database Connection Test</h3>";

$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8');
    
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connection successful<br><br>";
        
        $query = 'SELECT id, title, parking, images FROM properties WHERE id = 1';
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo '<strong>Property ID 1 Current Status:</strong><br>';
            echo 'ID: ' . $row['id'] . '<br>';
            echo 'Title: ' . htmlspecialchars($row['title']) . '<br>';
            echo 'Parking: [' . htmlspecialchars($row['parking']) . ']<br>';
            
            $images = json_decode($row['images'], true);
            echo 'Images count: ' . ($images ? count($images) : 0) . '<br>';
            
            if ($images && is_array($images)) {
                echo '<strong>Images:</strong><br>';
                foreach ($images as $i => $img) {
                    echo ($i + 1) . '. ' . htmlspecialchars($img) . '<br>';
                }
            } else {
                echo 'No images found or invalid JSON<br>';
            }
        } else {
            echo 'No property found with ID 1<br>';
        }
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . '<br>';
}
?>
