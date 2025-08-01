<?php
include 'db.php';

echo "Database Connection Test\n";
echo "========================\n\n";

// Test basic connection
if ($conn) {
    echo "✓ Database connection successful\n";
} else {
    echo "✗ Database connection failed\n";
    exit;
}

// Test simple query
$result = $conn->query("SELECT COUNT(*) as total FROM properties");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Total properties in database: " . $row['total'] . "\n";
} else {
    echo "✗ Query failed: " . $conn->error . "\n";
}

// Test with status filter
$result = $conn->query("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "✓ Active properties: " . $row['total'] . "\n";
} else {
    echo "✗ Status query failed: " . $conn->error . "\n";
}

// Test full query as used in portfoy.php
$where = "status = 'active'";
$sql = "SELECT * FROM properties WHERE $where ORDER BY created_at DESC";
echo "\n📋 Query being executed: $sql\n";

$result = $conn->query($sql);
if ($result) {
    echo "✓ Query executed successfully\n";
    echo "✓ Number of rows returned: " . $result->num_rows . "\n";
    
    if ($result->num_rows > 0) {
        echo "\n📋 Properties found:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- ID: {$row['id']}, Title: {$row['title']}, Status: {$row['status']}\n";
        }
    }
} else {
    echo "✗ Full query failed: " . $conn->error . "\n";
}
?>
