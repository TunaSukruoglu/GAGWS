<?php
session_start();
require_once 'auth_check.php';
include __DIR__ . '/../db.php';

// URL'den edit parametresini al
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    
    // Veritabanından property bilgilerini al
    $property_query = "SELECT * FROM properties WHERE id = ? AND (user_id = ? OR 'admin' = 'admin')";
    $stmt = $conn->prepare($property_query);
    $stmt->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $stmt->execute();
    $existing_property = $stmt->get_result()->fetch_assoc();
    
    if ($existing_property) {
        echo "<h2>Property Data Debug (ID: $edit_id)</h2>";
        echo "<pre>";
        print_r($existing_property);
        echo "</pre>";
        
        echo "<h3>Key Fields Check:</h3>";
        echo "<strong>rooms:</strong> " . ($existing_property['rooms'] ?? 'NULL') . "<br>";
        echo "<strong>age:</strong> " . ($existing_property['age'] ?? 'NULL') . "<br>";
        echo "<strong>heating:</strong> " . ($existing_property['heating'] ?? 'NULL') . "<br>";
        echo "<strong>bathrooms:</strong> " . ($existing_property['bathrooms'] ?? 'NULL') . "<br>";
        echo "<strong>parking:</strong> " . ($existing_property['parking'] ?? 'NULL') . "<br>";
        echo "<strong>usage_status:</strong> " . ($existing_property['usage_status'] ?? 'NULL') . "<br>";
        echo "<strong>floor:</strong> " . ($existing_property['floor'] ?? 'NULL') . "<br>";
        
        echo "<h3>Edit URL Test:</h3>";
        echo '<a href="add-property.php?edit=' . $edit_id . '">İlanı Düzenle</a>';
    } else {
        echo "Property not found or no access!";
        echo "<br>Edit ID: " . $edit_id;
        echo "<br>User ID: " . $_SESSION['user_id'];
    }
} else {
    echo "No edit parameter provided. Usage: debug-edit.php?edit=ID";
    
    // Kullanıcının ilanlarını listele
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id, title, type, rooms FROM properties WHERE user_id = ? LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if ($properties) {
        echo "<h3>Your Properties:</h3>";
        foreach ($properties as $prop) {
            echo '<a href="debug-edit.php?edit=' . $prop['id'] . '">';
            echo "ID: {$prop['id']} - {$prop['title']} ({$prop['type']}) - {$prop['rooms']}";
            echo '</a><br>';
        }
    }
}
?>
