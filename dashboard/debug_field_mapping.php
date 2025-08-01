<?php
// Debug form field mapping
include '../db.php';

// Simulate edit mode for debugging
$edit_mode = true;

// Get a property for testing
$result = $conn->query("SELECT * FROM properties LIMIT 1");
$existing_property = $result->fetch_assoc();

echo "<h3>Form Field Mapping Debug</h3>";

if ($existing_property) {
    echo "<h4>Property ID: " . $existing_property['id'] . "</h4>";
    echo "<table border='1'><tr><th>Form Field</th><th>Database Column</th><th>Current Value</th><th>Selected Option Check</th></tr>";
    
    // Room Count
    $room_selected = ($edit_mode && isset($existing_property['room_count']) && $existing_property['room_count'] == '3+1') ? 'SELECTED' : 'NOT SELECTED';
    echo "<tr><td>Oda Sayısı</td><td>room_count</td><td>" . ($existing_property['room_count'] ?? 'NULL') . "</td><td>$room_selected</td></tr>";
    
    // Building Age
    $building_selected = ($edit_mode && isset($existing_property['building_age']) && $existing_property['building_age'] == '5') ? 'SELECTED' : 'NOT SELECTED';
    echo "<tr><td>Bina Yaşı</td><td>building_age</td><td>" . ($existing_property['building_age'] ?? 'NULL') . "</td><td>$building_selected</td></tr>";
    
    // Parking
    $parking_selected = ($edit_mode && isset($existing_property['parking']) && $existing_property['parking'] == 'Var') ? 'SELECTED' : 'NOT SELECTED';
    echo "<tr><td>Otopark</td><td>parking</td><td>" . ($existing_property['parking'] ?? 'NULL') . "</td><td>$parking_selected</td></tr>";
    
    // Dues
    echo "<tr><td>Aidat</td><td>dues</td><td>" . ($existing_property['dues'] ?? 'NULL') . "</td><td>N/A (Input field)</td></tr>";
    
    // City
    $city_selected = ($edit_mode && isset($existing_property['city']) && $existing_property['city'] == 'İstanbul') ? 'SELECTED' : 'NOT SELECTED';
    echo "<tr><td>İl</td><td>city</td><td>" . ($existing_property['city'] ?? 'NULL') . "</td><td>$city_selected</td></tr>";
    
    // District
    echo "<tr><td>İlçe</td><td>district</td><td>" . ($existing_property['district'] ?? 'NULL') . "</td><td>Dynamically loaded</td></tr>";
    
    // Location Type
    $location_selected = ($edit_mode && isset($existing_property['location_type']) && $existing_property['location_type'] == 'site') ? 'CHECKED' : 'NOT CHECKED';
    echo "<tr><td>Konum Tipi</td><td>location_type</td><td>" . ($existing_property['location_type'] ?? 'NULL') . "</td><td>$location_selected</td></tr>";
    
    echo "</table>";
} else {
    echo "<p>No properties found.</p>";
}

$conn->close();
?>
