<?php
// Debug script to count bind parameters exactly

$type_string = "issdssssdddssssiiiisisssidisssissssis";
$type_count = strlen($type_string);

echo "Type string: " . $type_string . "\n";
echo "Type string length: " . $type_count . "\n\n";

// Count each type
$i_count = substr_count($type_string, 'i');
$s_count = substr_count($type_string, 's');
$d_count = substr_count($type_string, 'd');

echo "Integer (i) count: " . $i_count . "\n";
echo "String (s) count: " . $s_count . "\n";
echo "Double (d) count: " . $d_count . "\n";
echo "Total: " . ($i_count + $s_count + $d_count) . "\n\n";

// Manual parameter list
$parameters = [
    1 => 'user_id',
    2 => 'title', 
    3 => 'description',
    4 => 'price',
    5 => 'type',
    6 => 'category',
    7 => 'subcategory',
    8 => 'listing_type',
    9 => 'area_gross',
    10 => 'area_net',
    11 => 'area',
    12 => 'address',
    13 => 'city',
    14 => 'district',
    15 => 'neighborhood',
    16 => 'room_count',
    17 => 'bedrooms',
    18 => 'living_room_count',
    19 => 'bathrooms',
    20 => 'floor',
    21 => 'building_floors',
    22 => 'year_built',
    23 => 'building_age',
    24 => 'heating',
    25 => 'elevator',
    26 => 'parking',
    27 => 'furnished',
    28 => 'usage_status',
    29 => 'dues',
    30 => 'credit_eligible',
    31 => 'deed_status',
    32 => 'exchange',
    33 => 'location_type',
    34 => 'featured',
    35 => 'images_string',
    36 => 'main_image',
    37 => 'cloudflare_images_json',
    38 => 'cloudflare_main_image',
    39 => 'use_cloudflare',
    40 => 'features_json'
];

echo "Parameter count: " . count($parameters) . "\n\n";

// Print each parameter with its type
for ($i = 1; $i <= count($parameters); $i++) {
    $type_char = $type_string[$i-1];
    echo "$i. $type_char - {$parameters[$i]}\n";
}
?>
