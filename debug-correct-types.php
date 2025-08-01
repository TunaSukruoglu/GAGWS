<?php
$type_string = "issdssssdddssssiiiisssisssdisississssis";
echo "Type string length: " . strlen($type_string) . "\n";
echo "Type string: " . $type_string . "\n\n";

// 40 parametreyi listeleyelim
$expected_types = [
    'user_id' => 'i',           // 1
    'title' => 's',             // 2  
    'description' => 's',       // 3
    'price' => 'd',             // 4
    'type' => 's',              // 5
    'category' => 's',          // 6
    'subcategory' => 's',       // 7
    'listing_type' => 's',      // 8
    'area_gross' => 'd',        // 9
    'area_net' => 'd',          // 10
    'area' => 'd',              // 11
    'address' => 's',           // 12
    'city' => 's',              // 13
    'district' => 's',          // 14
    'neighborhood' => 's',      // 15
    'room_count' => 'i',        // 16
    'bedrooms' => 'i',          // 17
    'living_room_count' => 'i', // 18
    'bathrooms' => 'i',         // 19
    'floor' => 's',             // 20
    'building_floors' => 'i',   // 21
    'year_built' => 's',        // 22
    'building_age' => 's',      // 23
    'heating' => 's',           // 24 (enum)
    'elevator' => 's',          // 25 (enum)
    'parking' => 's',           // 26 (enum)
    'furnished' => 'i',         // 27 (tinyint)
    'usage_status' => 's',      // 28 (enum)
    'dues' => 'd',              // 29 (decimal)
    'credit_eligible' => 'i',   // 30 (tinyint)
    'deed_status' => 's',       // 31 (enum)
    'exchange' => 's',          // 32 (enum)
    'location_type' => 's',     // 33 (enum)
    'featured' => 'i',          // 34 (tinyint)
    'images_string' => 's',     // 35
    'main_image' => 's',        // 36
    'cloudflare_images_json' => 's', // 37
    'cloudflare_main_image' => 's',  // 38
    'use_cloudflare' => 'i',    // 39 (tinyint)
    'features_json' => 's'      // 40
];

$correct_type_string = implode('', array_values($expected_types));
echo "Correct type string: " . $correct_type_string . "\n";
echo "Correct length: " . strlen($correct_type_string) . "\n";
?>
