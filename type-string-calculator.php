<?php
// Exact parameter types for UPDATE
$update_params = [
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
    'room_count' => 'i',     // 14
    'bedrooms' => 'i',       // 15
    'living_room_count' => 'i', // 16
    'bathrooms' => 'i',      // 17
    'floor' => 's',          // 18 (string, not int!)
    'building_floors' => 's', // 19 (string, not int!)
    'year_built' => 'i',     // 20
    'building_age' => 's',   // 21
    'heating' => 's',        // 22
    'elevator' => 's',       // 23
    'parking' => 's',        // 24
    'furnished' => 'i',      // 25
    'usage_status' => 's',   // 26
    'dues' => 'd',           // 27 (double, not string!)
    'credit_eligible' => 'i', // 28
    'deed_status' => 's',    // 29
    'exchange' => 's',       // 30
    'location_type' => 's',  // 31
    'featured' => 'i',       // 32
    'images' => 's',         // 33
    'main_image' => 's',     // 34
    'features' => 's',       // 35
    'edit_id' => 'i',        // 36
    'user_id' => 'i',        // 37
    'role' => 's'            // 38
];

$correct_type_string = implode('', array_values($update_params));

echo "Correct UPDATE type string calculation:\n";
echo "======================================\n\n";

$i = 1;
foreach ($update_params as $param => $type) {
    echo "$i. $param ($type)\n";
    $i++;
}

echo "\nCorrect type string: $correct_type_string\n";
echo "Length: " . strlen($correct_type_string) . "\n";

$current_type = "ssdssssdddsssiiiiisissssisssssisssiss";
echo "\nCurrent type string: $current_type\n";
echo "Current length: " . strlen($current_type) . "\n";

if ($correct_type_string === $current_type) {
    echo "\n✓ Type strings match!\n";
} else {
    echo "\n✗ Type strings DON'T match!\n";
    echo "\nDifferences:\n";
    for ($j = 0; $j < max(strlen($correct_type_string), strlen($current_type)); $j++) {
        $correct_char = isset($correct_type_string[$j]) ? $correct_type_string[$j] : 'X';
        $current_char = isset($current_type[$j]) ? $current_type[$j] : 'X';
        if ($correct_char !== $current_char) {
            $pos = $j + 1;
            echo "Position $pos: Expected '$correct_char', Got '$current_char'\n";
        }
    }
}
?>
