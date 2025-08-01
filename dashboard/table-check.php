<?php
session_start();
include __DIR__ . '/../db.php';

echo "Table structure check:<br><br>";

// Favorites tablosu kontrolü
$result = $conn->query("DESCRIBE favorites");
if ($result) {
    echo "<h3>Favorites table structure:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Favorites table not found or error: " . $conn->error . "<br>";
}

echo "<br>";

// Properties tablosu kontrolü
$result = $conn->query("DESCRIBE properties");
if ($result) {
    echo "<h3>Properties table structure:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Properties table not found or error: " . $conn->error . "<br>";
}

echo "<br>";

// Test join query
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    echo "<h3>Test join query for user $user_id:</h3>";
    
    $test_query = "SELECT COUNT(*) as count FROM properties p INNER JOIN favorites f ON p.id = f.property_id WHERE f.user_id = ?";
    $stmt = $conn->prepare($test_query);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        echo "Favorites count: " . $result['count'] . "<br>";
    } else {
        echo "Query failed: " . $conn->error . "<br>";
    }
}
?>
