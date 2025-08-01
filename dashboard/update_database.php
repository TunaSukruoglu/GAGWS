<?php
// Database update script to add missing columns
include '../db.php';

echo "Starting database update...\n";

$queries = [
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS usage_status VARCHAR(50) NULL",
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS dues DECIMAL(10,2) DEFAULT 0", 
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS credit_eligible TINYINT(1) DEFAULT 0",
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS deed_status VARCHAR(100) NULL",
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS exchange VARCHAR(20) NULL",
    "ALTER TABLE properties ADD COLUMN IF NOT EXISTS location_type VARCHAR(50) NULL"
];

$update_queries = [
    "UPDATE properties SET usage_status = 'Boş' WHERE usage_status IS NULL",
    "UPDATE properties SET dues = 0 WHERE dues IS NULL", 
    "UPDATE properties SET credit_eligible = 0 WHERE credit_eligible IS NULL",
    "UPDATE properties SET deed_status = 'Kat Mülkiyetli' WHERE deed_status IS NULL",
    "UPDATE properties SET exchange = 'Hayır' WHERE exchange IS NULL",
    "UPDATE properties SET location_type = 'standalone' WHERE location_type IS NULL"
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
    
    echo "\nDatabase update completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
