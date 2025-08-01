<?php
// Parameter type analysis
$type_string = "ssdssssdddssssiiiisisssssisdissssisssiis";
echo "Type string: " . $type_string . "\n";
echo "Type string length: " . strlen($type_string) . "\n";

$expected_params = [
    'title' => 's',           // 1
    'description' => 's',     // 2
    'price' => 'd',          // 3
    'type' => 's',           // 4
    'category' => 's',       // 5
    'subcategory' => 's',    // 6
    'listing_type' => 's',   // 7
    'area_gross' => 'd',     // 8
    'area_net' => 'd',       // 9
    'area' => 'd',           // 10
    'address' => 's',        // 11
    'city' => 's',           // 12
    'district' => 's',       // 13
    'neighborhood' => 's',   // 14
    'room_count' => 'i',     // 15
    'bedrooms' => 'i',       // 16
    'living_room_count' => 'i', // 17
    'bathrooms' => 'i',      // 18
    'floor' => 's',          // 19
    'building_floors' => 'i', // 20
    'year_built' => 's',     // 21 - bu integer olmalı!
    'building_age' => 's',   // 22
    'heating' => 's',        // 23
    'elevator' => 's',       // 24
    'parking' => 's',        // 25
    'furnished' => 's',      // 26 - bu integer olmalı!
    'usage_status' => 'i',   // 27 - bu string olmalı!
    'dues' => 's',           // 28 - bu decimal olmalı!
    'credit_eligible' => 'd', // 29 - bu integer olmalı!
    'deed_status' => 'i',    // 30 - bu string olmalı!
    'exchange' => 's',       // 31
    'location_type' => 's',  // 32
    'featured' => 's',       // 33 - bu integer olmalı!
    'images_string' => 's',  // 34
    'main_image' => 'i',     // 35 - bu string olmalı!
    'cloudflare_images_json' => 's', // 36
    'cloudflare_main_image' => 's',  // 37
    'use_cloudflare' => 's', // 38 - bu integer olmalı!
    'features_json' => 'i',  // 39 - bu string olmalı!
    'edit_id' => 's',        // 40 - bu integer olmalı!
    'user_id' => 's',        // 41 - bu integer olmalı!
    'user_role' => 'i',      // 42 - bu string olmalı!
    'EXTRA' => 's'           // 43 - Bu nedir?
];

echo "Expected parameter count: " . (count($expected_params) - 1) . "\n";

$correct_types = "";
foreach (array_slice($expected_params, 0, -1) as $param => $type) {
    $correct_types .= $type;
}

echo "Correct type string should be: " . $correct_types . "\n";
echo "Correct type string length: " . strlen($correct_types) . "\n";
?>
