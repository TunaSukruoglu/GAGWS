<?php
// Minimal test for add-property form
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        echo "<h2>POST Data Received:</h2>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        
        $usage_status = $_POST['usage_status'] ?? '';
        echo "<p><strong>Usage Status Input:</strong> '" . htmlspecialchars($usage_status) . "'</p>";
        
        // Test simple insertion
        $query = "INSERT INTO properties (user_id, title, description, price, type, category, usage_status, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $user_id = 1; // Test user ID
            $title = "Test Property " . date('Y-m-d H:i:s');
            $description = "Test description";
            $price = 100000;
            $type = "sale";
            $category = "apartment";
            
            echo "<p><strong>Attempting insert with:</strong></p>";
            echo "<ul>";
            echo "<li>user_id: $user_id</li>";
            echo "<li>title: $title</li>";
            echo "<li>usage_status: '$usage_status'</li>";
            echo "</ul>";
            
            $stmt->bind_param("issdsss", $user_id, $title, $description, $price, $type, $category, $usage_status);
            
            if ($stmt->execute()) {
                $property_id = $conn->insert_id;
                echo "<p style='color: green;'>✅ SUCCESS! Property added with ID: $property_id</p>";
            } else {
                echo "<p style='color: red;'>❌ FAILED: " . htmlspecialchars($stmt->error) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Prepare failed: " . htmlspecialchars($conn->error) . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Usage Status Test</title>
</head>
<body>
    <h1>Usage Status Test Form</h1>
    
    <form method="POST">
        <label for="usage_status">Usage Status:</label>
        <select name="usage_status" id="usage_status" required>
            <option value="">Select...</option>
            <option value="Bos">Boş</option>
            <option value="Kiracili">Kiracılı</option>
            <option value="Malik Kullaniminda">Malik Kullanımında</option>
            <option value="Yatirim Amacli">Yatırım Amaçlı</option>
        </select>
        <br><br>
        <button type="submit">Test Submit</button>
    </form>
</body>
</html>
