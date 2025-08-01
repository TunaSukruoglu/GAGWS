<?php
// Debug script for testing main image update
session_start();

// Simple test script to verify database connection and property data
$servername = "localhost";
$username = "gokhanay_user"; 
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
    
    echo "<h3>✅ Database Connection Success</h3>";
    
    // Get a sample property for testing
    $query = "SELECT id, title, images, main_image, cloudflare_images, cloudflare_main_image FROM properties WHERE cloudflare_images IS NOT NULL AND cloudflare_images != '' AND cloudflare_images != '[]' LIMIT 5";
    $result = $conn->query($query);
    
    echo "<h3>📋 Properties with Cloudflare Images:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<strong>ID:</strong> " . $row['id'] . "<br>";
        echo "<strong>Title:</strong> " . htmlspecialchars($row['title']) . "<br>";
        echo "<strong>Main Image:</strong> " . htmlspecialchars($row['main_image']) . "<br>";
        echo "<strong>Cloudflare Main:</strong> " . htmlspecialchars($row['cloudflare_main_image']) . "<br>";
        
        $cf_images = json_decode($row['cloudflare_images'], true);
        if (is_array($cf_images)) {
            echo "<strong>Cloudflare Images Count:</strong> " . count($cf_images) . "<br>";
            echo "<strong>First 3 Images:</strong><br>";
            for ($i = 0; $i < min(3, count($cf_images)); $i++) {
                echo "• " . htmlspecialchars($cf_images[$i]) . "<br>";
            }
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo htmlspecialchars($e->getMessage());
}
?>
