<?php
// UPDATE mode parameter counter
$update_params = [
    'title', 'description', 'price', 'type', 'category', 'subcategory',
    'listing_type', 'area_gross', 'area_net', 'area', 'address', 'city', 'district', 
    'room_count', 'bedrooms', 'living_room_count', 'bathrooms', 'floor', 'building_floors', 'year_built', 'building_age',
    'heating', 'elevator', 'parking', 'furnished', 'usage_status', 'dues', 'credit_eligible',
    'deed_status', 'exchange', 'location_type', 'featured',
    'images', 'main_image', 'features',
    'edit_id', 'user_id', 'role'  // WHERE koşulları
];

$type_string = "ssdssssdddsssiiiiisissssisssssisssiss";

echo "UPDATE Mode Parameter Analysis\n";
echo "==============================\n\n";

echo "Parameter count: " . count($update_params) . "\n";
echo "Type string length: " . strlen($type_string) . "\n";

if (count($update_params) === strlen($type_string)) {
    echo "✓ Counts match!\n\n";
} else {
    echo "✗ Counts don't match!\n\n";
}

foreach ($update_params as $i => $param) {
    $pos = $i + 1;
    $type_char = isset($type_string[$i]) ? $type_string[$i] : 'X';
    echo "$pos. $param ($type_char)\n";
    
    if ($param === 'usage_status') {
        echo "    ^^ usage_status at position $pos with type '$type_char'\n";
    }
}
?>
