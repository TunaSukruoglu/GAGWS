<?php
session_start();
include __DIR__ . '/../db.php';

echo "<h2>Quick Edit Test</h2>";

// Session kontrol
if (!isset($_SESSION['user_id'])) {
    echo "❌ User not logged in<br>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "✅ User ID: $user_id<br>";

// Kullanıcının ilk ilanını bul
$query = "SELECT * FROM properties WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if ($property) {
    $test_id = $property['id'];
    echo "✅ Found property ID: $test_id<br>";
    echo "<strong>Property Title:</strong> " . ($property['title'] ?? 'N/A') . "<br>";
    echo "<strong>Rooms:</strong> " . ($property['rooms'] ?? 'N/A') . "<br>";
    echo "<strong>Age:</strong> " . ($property['age'] ?? 'N/A') . "<br>";
    echo "<strong>Heating:</strong> " . ($property['heating'] ?? 'N/A') . "<br>";
    
    echo "<h3>Test Edit Link:</h3>";
    echo '<a href="add-property.php?edit=' . $test_id . '" target="_blank">Edit Property</a><br><br>';
    
    // Debug alanı
    echo "<h3>Raw Property Data:</h3>";
    echo "<pre>";
    print_r($property);
    echo "</pre>";
} else {
    echo "❌ No properties found for this user<br>";
    
    // Tüm properties'i kontrol et (admin için)
    $query = "SELECT * FROM properties LIMIT 5";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "<h3>Available Properties (for admin):</h3>";
        while ($prop = $result->fetch_assoc()) {
            echo "ID: " . $prop['id'] . " - " . ($prop['title'] ?? 'N/A') . 
                 " (User: " . $prop['user_id'] . ")<br>";
        }
    }
}
?>
