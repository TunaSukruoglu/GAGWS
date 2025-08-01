<?php
// Edit Mode Image Debug Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Edit Mode Image Address Debug</h2>";

try {
    $conn = new mysqli('localhost', 'gokhanay_user', '113041122839sS?!_', 'gokhanay_db');
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    // Get property ID from URL or use default
    $property_id = $_GET['id'] ?? null;
    
    if (!$property_id) {
        echo "<h3>Available Properties:</h3>";
        $result = $conn->query('SELECT id, title, parking, LENGTH(images) as img_length FROM properties ORDER BY id DESC LIMIT 10');
        if ($result) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Parking</th><th>Images Size</th><th>Action</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . substr($row['title'], 0, 50) . "</td>";
                echo "<td><strong>[" . $row['parking'] . "]</strong></td>";
                echo "<td>" . $row['img_length'] . " bytes</td>";
                echo "<td><a href='?id=" . $row['id'] . "'>Debug Images</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        // Debug specific property
        $stmt = $conn->prepare('SELECT id, title, parking, images FROM properties WHERE id = ?');
        $stmt->bind_param('i', $property_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($property = $result->fetch_assoc()) {
            echo "<h3>Property ID: {$property['id']}</h3>";
            echo "<p><strong>Title:</strong> {$property['title']}</p>";
            echo "<p><strong>Parking Value:</strong> <code style='background: #f0f0f0; padding: 2px 5px;'>[{$property['parking']}]</code></p>";
            
            echo "<h4>Raw Images JSON:</h4>";
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
            echo htmlspecialchars($property['images']);
            echo "</pre>";
            
            echo "<h4>Parsed Images Array:</h4>";
            $images = json_decode($property['images'], true) ?: [];
            echo "<p><strong>Total Images:</strong> " . count($images) . "</p>";
            
            if (!empty($images)) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>#</th><th>Image Address</th><th>Type</th><th>Preview</th></tr>";
                
                foreach ($images as $index => $imageUrl) {
                    $imageType = 'Unknown';
                    $previewUrl = '';
                    
                    // Determine image type and create preview URL
                    if (strpos($imageUrl, 'https://imagedelivery.net/') === 0) {
                        $imageType = 'Cloudflare Images';
                        // Extract image ID and use account hash for display
                        if (preg_match('/https:\/\/imagedelivery\.net\/[^\/]+\/([a-f0-9-]+)\//', $imageUrl, $matches)) {
                            $imageId = $matches[1];
                            $previewUrl = "https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/{$imageId}/public";
                        } else {
                            $previewUrl = $imageUrl;
                        }
                    } elseif (strpos($imageUrl, '/uploads/properties/') === 0) {
                        $imageType = 'Local Upload';
                        $previewUrl = 'https://gokhanaydinli.com' . $imageUrl;
                    } elseif (preg_match('/^[a-f0-9-]{36}$/', $imageUrl)) {
                        $imageType = 'Cloudflare Image ID';
                        $previewUrl = "https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/{$imageUrl}/public";
                    } else {
                        $imageType = 'Other Format';
                        $previewUrl = $imageUrl;
                    }
                    
                    echo "<tr>";
                    echo "<td>" . ($index + 1) . "</td>";
                    echo "<td style='word-break: break-all; max-width: 300px;'><code>{$imageUrl}</code></td>";
                    echo "<td><span style='padding: 2px 8px; background: " . ($imageType === 'Cloudflare Images' ? '#4CAF50' : ($imageType === 'Local Upload' ? '#2196F3' : '#FF9800')) . "; color: white; border-radius: 3px; font-size: 12px;'>{$imageType}</span></td>";
                    echo "<td><img src='{$previewUrl}' style='max-width: 100px; max-height: 100px;' onerror=\"this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwIiB5PSI1NSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE0IiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5FcnJvcjwvdGV4dD48L3N2Zz4='\"></td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<h4>Edit Mode Test URLs:</h4>";
                echo "<p><a href='dashboard/add-property.php?edit_property={$property['id']}' target='_blank'>Edit This Property</a></p>";
                echo "<p><a href='property-details.php?id={$property['id']}' target='_blank'>View Property Details</a></p>";
            } else {
                echo "<p style='color: #ff6b6b;'><strong>No images found for this property.</strong></p>";
            }
        } else {
            echo "<p style='color: #ff6b6b;'>Property not found with ID: {$property_id}</p>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='background: #ff4444; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>
