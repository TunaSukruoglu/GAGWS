<?php
// Database update script to add building_age column
include '../db.php';

echo "Adding building_age column...\n";

$queries = [
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS building_age VARCHAR(20) NULL"
];

$update_queries = [
    "UPDATE properties SET building_age = '0' WHERE building_age IS NULL"
];

try {
    // Add columns
    foreach ($queries as $query) {
        echo "Executing: $query\n";
        if ($conn->query($query)) {
            echo "✓ Success\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
    
    // Update existing records
    foreach ($update_queries as $query) {
        echo "Executing: $query\n";
        if ($conn->query($query)) {
            echo "✓ Success\n";
        } else {
            echo "✗ Error: " . $conn->error . "\n";
        }
    }
    
    echo "\nbuilding_age column added successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
