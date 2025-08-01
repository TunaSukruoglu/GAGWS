<?php
// Test UPDATE mode with debug
include '../db.php';

// Mock session data
$user_data = ['role' => 'admin'];
$user_id = 1;
$edit_id = 2; // Existing property ID

echo "UPDATE Mode Test\n";
echo "================\n\n";

try {
    // Test data
    $title = 'Updated Test Title';
    $description = 'Updated description';
    $price = 900000.0;
    $type = 'sale';
    $category = 'apartment';
    $subcategory = 'daire';
    $listing_type = 'Satılık';
    $area_gross = 140.0;
    $area_net = 120.0;
    $area = 140.0;
    $address = 'Updated address';
    $city = 'İstanbul';
    $district = 'Updated district';
    $room_count = 4;
    $bedrooms = 3;
    $living_room_count = 1;
    $bathrooms = 2;
    $floor = '8';
    $building_floors = '15';
    $year_built = 2021;
    $building_age = '4';
    $heating = 'Kombi (Dogalgaz)';
    $elevator = 'Var';
    $parking = 'Kapali Otopark';
    $furnished = 1;
    $usage_status = 'Kiracili';  // Test farklı değer
    $dues = 1500.0;
    $credit_eligible = 1;
    $deed_status = 'Kat Mulkiyeti';
    $exchange = 'Hayir';
    $location_type = 'standalone';
    $featured = 0;
    $images_string = '["test.jpg"]';
    $main_image = 'test.jpg';
    $features_json = '{"ic_ozellikler": ["test"]}';

    echo "Test values:\n";
    echo "usage_status: '$usage_status'\n";
    echo "user_data['role']: '" . $user_data['role'] . "'\n\n";

    // UPDATE query
    $query = "UPDATE properties SET 
        title = ?,
        description = ?,
        price = ?,
        type = ?,
        category = ?,
        subcategory = ?,
        listing_type = ?,
        area_gross = ?,
        area_net = ?,
        area = ?,
        address = ?,
        city = ?,
        district = ?,
        room_count = ?,
        bedrooms = ?,
        living_room_count = ?,
        bathrooms = ?,
        floor = ?,
        building_floors = ?,
        year_built = ?,
        building_age = ?,
        heating = ?,
        elevator = ?,
        parking = ?,
        furnished = ?,
        usage_status = ?,
        dues = ?,
        credit_eligible = ?,
        deed_status = ?,
        exchange = ?,
        location_type = ?,
        featured = ?,
        images = ?,
        main_image = ?,
        features = ?,
        updated_at = NOW()
        WHERE id = ? AND (user_id = ? OR ? = 'admin')";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Correct 38-character type string for 38 parameters
    $bind_result = $stmt->bind_param("ssdssssdddsssiiiississssisdisssisssiis", 
        $title, $description, $price, $type, $category, $subcategory,
        $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
        $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
        $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
        $deed_status, $exchange, $location_type, $featured,
        $images_string, $main_image, $features_json,
        $edit_id, $user_id, $user_data['role']);
        
    if (!$bind_result) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }

    if ($stmt->execute()) {
        echo "✓ UPDATE SUCCESS!\n\n";
        
        // Verify updated data
        $check_sql = "SELECT usage_status, parking, heating FROM properties WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $edit_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "Updated values in database:\n";
        foreach ($row as $field => $value) {
            echo "  $field: '$value'\n";
        }
        
    } else {
        echo "✗ UPDATE ERROR: " . $stmt->error . "\n";
    }

} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}
?>
