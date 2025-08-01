<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "ENUM INSERT Test\n";
echo "================\n\n";

// Test değerleri - database'deki ENUM'lara uygun
$test_data = [
    'user_id' => 1,
    'title' => 'Test İlan ENUM',
    'description' => 'Test açıklama',
    'price' => 500000,
    'type' => 'sale',
    'category' => 'apartment',
    'subcategory' => 'daire',
    'listing_type' => 'Satılık',  // Database'deki ENUM değeri
    'area_gross' => 100,
    'area_net' => 85,
    'area' => 100,
    'address' => 'Test Adres',
    'city' => 'İstanbul',
    'district' => 'Test İlçe',
    'room_count' => 3,
    'bedrooms' => 2,
    'living_room_count' => 1,
    'bathrooms' => 2,
    'floor' => '3',
    'building_floors' => '5',
    'year_built' => 2020,
    'building_age' => '5',
    'heating' => 'Kombi (Dogalgaz)',  // Türkçe karakter YOK
    'elevator' => 'Var',
    'parking' => 'Acik Otopark',  // Türkçe karakter YOK
    'furnished' => 0,
    'usage_status' => 'Bos',  // Türkçe karakter YOK
    'dues' => 500,
    'credit_eligible' => 1,
    'deed_status' => 'Kat Mulkiyeti',
    'exchange' => 'Hayir',  // Türkçe karakter YOK
    'location_type' => 'standalone',
    'featured' => 0,
    'images' => '[]',
    'main_image' => '',
    'features' => '{"ic_ozellikler": [], "dis_ozellikler": []}',
    'status' => 'active'
];

try {
    // INSERT sorgusu hazırla
    $sql = "INSERT INTO properties (
        user_id, title, description, price, type, category, subcategory, listing_type,
        area_gross, area_net, area, address, city, district, room_count, bedrooms,
        living_room_count, bathrooms, floor, building_floors, year_built, building_age,
        heating, elevator, parking, furnished, usage_status, dues, credit_eligible,
        deed_status, exchange, location_type, featured, images, main_image, features, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind parameters - 37 parametre var, usage_status 27. pozisyonda 's' olmalı
    $stmt->bind_param("issdssssdddsssiiiiiiissssisssssisssss",
        $test_data['user_id'], $test_data['title'], $test_data['description'], $test_data['price'],
        $test_data['type'], $test_data['category'], $test_data['subcategory'], $test_data['listing_type'],
        $test_data['area_gross'], $test_data['area_net'], $test_data['area'], $test_data['address'],
        $test_data['city'], $test_data['district'], $test_data['room_count'], $test_data['bedrooms'],
        $test_data['living_room_count'], $test_data['bathrooms'], $test_data['floor'], $test_data['building_floors'],
        $test_data['year_built'], $test_data['building_age'], $test_data['heating'], $test_data['elevator'],
        $test_data['parking'], $test_data['furnished'], $test_data['usage_status'], $test_data['dues'],
        $test_data['credit_eligible'], $test_data['deed_status'], $test_data['exchange'], $test_data['location_type'],
        $test_data['featured'], $test_data['images'], $test_data['main_image'], $test_data['features'], $test_data['status']
    );
    
    // Test ENUM değerlerini logla
    echo "Test ENUM değerleri:\n";
    echo "usage_status: '" . $test_data['usage_status'] . "'\n";
    echo "parking: '" . $test_data['parking'] . "'\n";
    echo "heating: '" . $test_data['heating'] . "'\n";
    echo "elevator: '" . $test_data['elevator'] . "'\n";
    echo "deed_status: '" . $test_data['deed_status'] . "'\n";
    echo "exchange: '" . $test_data['exchange'] . "'\n";
    echo "listing_type: '" . $test_data['listing_type'] . "'\n\n";
    
    // Execute
    if ($stmt->execute()) {
        $property_id = $conn->insert_id;
        echo "✓ SUCCESS: Test ilan başarıyla eklendi! (ID: $property_id)\n";
        
        // Eklenen veriyi kontrol et
        $check_sql = "SELECT usage_status, parking, heating, elevator, deed_status, exchange, listing_type FROM properties WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "\nDatabase'de kaydedilen değerler:\n";
        foreach ($row as $field => $value) {
            echo "$field: '$value'\n";
        }
        
    } else {
        echo "✗ ERROR: " . $stmt->error . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}
?>
