<?php
session_start();
include 'db.php';
include 'includes/common-functions.php';

echo "<h1>Portfoy Debug</h1>";

// Test the basic query
$where = "1=1";
$params = [];
$types = "";

echo "<h2>Debug Info:</h2>";
echo "Where clause: " . $where . "<br>";
echo "Params: " . print_r($params, true) . "<br>";
echo "Types: " . $types . "<br>";

// Count query
$count_sql = "SELECT COUNT(*) as total FROM properties WHERE $where";
echo "<h3>Count Query:</h3>";
echo $count_sql . "<br>";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_count = $count_result->fetch_assoc()['total'];

echo "Total count: " . $total_count . "<br>";

// Main query
$sql = "SELECT id, title, status, listing_type, price, created_at FROM properties WHERE $where ORDER BY created_at DESC";
echo "<h3>Main Query:</h3>";
echo $sql . "<br>";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

echo "<h3>Results:</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Listing Type</th><th>Price</th><th>Created</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['title'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['listing_type'] . "</td>";
    echo "<td>" . $row['price'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test the function that formats price
function formatPrice($price) {
    if ($price >= 1000000) {
        return '₺' . number_format($price / 1000000, 1) . 'M';
    } elseif ($price >= 1000) {
        return '₺' . number_format($price / 1000) . 'K';
    } else {
        return '₺' . number_format($price);
    }
}

echo "<h3>Price Formatting Test:</h3>";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "Property ID " . $row['id'] . ": " . formatPrice($row['price']) . "<br>";
}
?>
