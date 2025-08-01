<?php
// DB bağlantısı test et
include __DIR__ . '/../db.php';

echo "<h2>Database Connection Test</h2>";

if (isset($conn)) {
    echo "✅ MySQLi connection exists<br>";
    
    // Test query
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "✅ Database query successful<br>";
        echo "<strong>Tables:</strong><br>";
        while ($row = $result->fetch_row()) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "❌ Database query failed: " . $conn->error . "<br>";
    }
} else {
    echo "❌ No connection found<br>";
}

if (isset($pdo)) {
    echo "✅ PDO connection also exists<br>";
}

// Session test
session_start();
echo "<h3>Session Test</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";

// Properties test
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT COUNT(*) as count FROM properties WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo "Your properties count: " . $result['count'] . "<br>";
}
?>
