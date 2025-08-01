<?php
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "❌ Giriş yapmanız gerekiyor.";
    exit;
}

$user_id = $_SESSION['user_id'];

// User bilgilerini al
$user_query = $conn->prepare("SELECT name, role, can_add_property FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

echo "<h2>Edit Test Dashboard</h2>";
echo "<strong>User:</strong> " . $user_data['name'] . "<br>";
echo "<strong>Role:</strong> " . $user_data['role'] . "<br>";
echo "<strong>Can Add Property:</strong> " . ($user_data['can_add_property'] ? 'Yes' : 'No') . "<br><br>";

// Kullanıcının ilanlarını listele
$query = "SELECT id, title, type, user_id FROM properties WHERE user_id = ? ORDER BY id DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h3>Your Properties:</h3>";
if ($user_properties) {
    foreach ($user_properties as $prop) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>ID:</strong> {$prop['id']}<br>";
        echo "<strong>Title:</strong> {$prop['title']}<br>";
        echo "<strong>Type:</strong> {$prop['type']}<br>";
        echo '<a href="add-property.php?edit=' . $prop['id'] . '" target="_blank" style="color: blue;">Edit This Property</a>';
        echo "</div>";
    }
} else {
    echo "No properties found.";
}

// Admin ise tüm ilanları göster
if ($user_data['role'] === 'admin') {
    echo "<h3>All Properties (Admin View):</h3>";
    $query = "SELECT id, title, type, user_id FROM properties ORDER BY id DESC LIMIT 10";
    $all_properties = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    
    foreach ($all_properties as $prop) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px; background: #f9f9f9;'>";
        echo "<strong>ID:</strong> {$prop['id']} (User: {$prop['user_id']})<br>";
        echo "<strong>Title:</strong> {$prop['title']}<br>";
        echo "<strong>Type:</strong> {$prop['type']}<br>";
        echo '<a href="add-property.php?edit=' . $prop['id'] . '" target="_blank" style="color: red;">Edit as Admin</a>';
        echo "</div>";
    }
}
?>

<h3>Direct Test:</h3>
<p>Bu linkler add-property.php?edit=ID formatında test yapabilirsiniz:</p>
<?php
if ($user_properties) {
    $first_property = $user_properties[0];
    echo '<a href="add-property.php?edit=' . $first_property['id'] . '" target="_blank">Test Edit (ID: ' . $first_property['id'] . ')</a>';
}
?>
