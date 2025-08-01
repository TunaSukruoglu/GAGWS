<?php
// Veritabanı yapısını kontrol etme scripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Veritabanı yapısı kontrol ediliyor...\n";

// Include database connection
include 'db.php';

echo "Connected to database successfully.\n";

// Check if properties table exists
$check_table = $conn->query("SHOW TABLES LIKE 'properties'");
if ($check_table->num_rows == 0) {
    echo "Properties table does not exist!\n";
    exit;
}

echo "Properties table exists. Checking structure:\n\n";

// Get table structure
$result = $conn->query("DESCRIBE properties");
if ($result) {
    echo "Current columns in properties table:\n";
    echo "Field\t\t\tType\t\t\tNull\tKey\tDefault\n";
    echo "------------------------------------------------------------\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "\t\t" . $row['Type'] . "\t\t" . $row['Null'] . "\t" . $row['Key'] . "\t" . $row['Default'] . "\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

// Check specifically for category column
$check_category = $conn->query("SHOW COLUMNS FROM properties LIKE 'category'");
echo "\nCategory column check: ";
if ($check_category->num_rows > 0) {
    echo "EXISTS\n";
    $cat_info = $check_category->fetch_assoc();
    echo "Category column details: " . print_r($cat_info, true) . "\n";
} else {
    echo "MISSING\n";
}

$conn->close();
?>
