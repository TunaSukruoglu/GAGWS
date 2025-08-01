<?php
// Update existing properties with default values
include '../db.php';

echo "<h3>Updating Existing Properties</h3>";

try {
    // Set default values for new columns
    $updates = [
        "UPDATE properties SET building_age = '5' WHERE building_age IS NULL OR building_age = ''",
        "UPDATE properties SET usage_status = 'Boş' WHERE usage_status IS NULL OR usage_status = ''",
        "UPDATE properties SET dues = 0 WHERE dues IS NULL",
        "UPDATE properties SET credit_eligible = 0 WHERE credit_eligible IS NULL",
        "UPDATE properties SET deed_status = 'Kat Mülkiyetli' WHERE deed_status IS NULL OR deed_status = ''",
        "UPDATE properties SET exchange = 'Hayır' WHERE exchange IS NULL OR exchange = ''",
        "UPDATE properties SET location_type = 'standalone' WHERE location_type IS NULL OR location_type = ''"
    ];
    
    foreach ($updates as $query) {
        echo "Executing: " . htmlspecialchars($query) . "<br>";
        if ($conn->query($query)) {
            echo "✓ Affected rows: " . $conn->affected_rows . "<br><br>";
        } else {
            echo "✗ Error: " . $conn->error . "<br><br>";
        }
    }
    
    // Show updated data
    echo "<h4>Sample Updated Property:</h4>";
    $result = $conn->query("SELECT id, room_count, building_age, parking, dues, city, district, location_type, usage_status FROM properties LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
