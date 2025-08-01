<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "FULL INSERT Test - add-property.php Simulation\n";
echo "===============================================\n\n";

// add-property.php ile aynı INSERT sorgusunu test et
$test_data = [
    'user_id' => 1,
    'title' => 'Test Full INSERT',
    'description' => 'Test açıklama full insert',
    'price' => 750000,
    'type' => 'sale',
    'category' => 'apartment',
    'subcategory' => 'daire',
    'listing_type' => 'Satılık',
    'area_gross' => 120,
    'area_net' => 100,
    'area' => 120,
    'address' => 'Test Mahallesi Test Sokak',
    'city' => 'İstanbul',
    'district' => 'Kadıköy',
    'room_count' => 3,
    'bedrooms' => 2,
    'living_room_count' => 1,
    'bathrooms' => 2,
    'floor' => '5',
    'building_floors' => '8',
    'year_built' => 2020,
    'building_age' => '5',
    'heating' => 'Kombi (Dogalgaz)',
    'elevator' => 'Var',
    'parking' => 'Acik Otopark',
    'furnished' => 1,
    'usage_status' => 'Bos',  // Problem olan alan
    'dues' => 800,
    'credit_eligible' => 1,
    'deed_status' => 'Kat Mulkiyeti',
    'exchange' => 'Hayir',
    'location_type' => 'standalone',
    'featured' => 0,
    'images' => '[]',
    'main_image' => '',
    'features' => '{"ic_ozellikler": [], "dis_ozellikler": []}',
    'status' => 'active'
];

echo "Test edilecek usage_status: '" . $test_data['usage_status'] . "'\n\n";

try {
    // add-property.php ile aynı INSERT sorgusu
    $query = "INSERT INTO properties SET 
        user_id = ?,
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
        status = 'active',
        created_at = NOW()";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Düzeltilmiş bind_param - 36 parametre için 36 karakter
    $bind_result = $stmt->bind_param("issdssssdddsssiiiiiiissssisssssissss", 
        $test_data['user_id'], $test_data['title'], $test_data['description'], $test_data['price'], 
        $test_data['type'], $test_data['category'], $test_data['subcategory'],
        $test_data['listing_type'], $test_data['area_gross'], $test_data['area_net'], $test_data['area'], 
        $test_data['address'], $test_data['city'], $test_data['district'], 
        $test_data['room_count'], $test_data['bedrooms'], $test_data['living_room_count'], $test_data['bathrooms'], 
        $test_data['floor'], $test_data['building_floors'], $test_data['year_built'], $test_data['building_age'],
        $test_data['heating'], $test_data['elevator'], $test_data['parking'], $test_data['furnished'], 
        $test_data['usage_status'], $test_data['dues'], $test_data['credit_eligible'],
        $test_data['deed_status'], $test_data['exchange'], $test_data['location_type'], $test_data['featured'],
        $test_data['images'], $test_data['main_image'], $test_data['features']
    );
    
    if (!$bind_result) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }

    if ($stmt->execute()) {
        $property_id = $conn->insert_id;
        echo "✓ SUCCESS: İlan başarıyla eklendi! (ID: $property_id)\n\n";
        
        // Eklenen veriyi kontrol et
        $check_sql = "SELECT usage_status, parking, heating, elevator, deed_status, exchange, listing_type FROM properties WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "Database'de kaydedilen ENUM değerleri:\n";
        foreach ($row as $field => $value) {
            $display = empty($value) ? '[EMPTY]' : "'$value'";
            echo "  $field: $display\n";
        }
        
    } else {
        echo "✗ INSERT ERROR: " . $stmt->error . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}
?>
